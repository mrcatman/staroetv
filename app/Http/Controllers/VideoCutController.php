<?php

namespace App\Http\Controllers;

use App\Constants\Actions;
use App\Helpers\ActionsLogHelper;
use App\Helpers\ExternalServicesHelper;
use App\Helpers\MediaHelper;
use App\Helpers\PermissionsHelper;
use App\Jobs\CutVideo;
use App\Jobs\DownloadExternalVideoForCut;
use App\Models\Channel;
use App\Models\DesignPackage;
use App\Models\Genre;
use App\Models\Picture;
use App\Models\Record;
use App\Models\VideoCut;
use Carbon\Carbon;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class VideoCutController extends Controller {

    public function showForm($id) {
        $video = Record::find($id);
        if (!$video || !PermissionsHelper::allows('viadd')) {
            return redirect(route('index'));
        }
        $cut = VideoCut::where(['video_id' => $id])->first();
        if ($cut) {
            if (!request()->has('reload')) {
                return redirect(route('cut.show', $cut->id));
            }
        }
        return view ('pages.cut.index', [
            'cut' => null,
            'video' => $video,
        ]);
    }

    public function show($id) {
        $cut = VideoCut::find($id);
        if (!PermissionsHelper::allows('viadd') || !$cut) {
            return redirect(route('index'));
        }
        $video = $cut->video;
        $channel = null;
        if ($video) {
            $channel = $video->channel;
        }
        return view ('pages.cut.index', [
            'video' => $video,
            'channel' => $channel,
            'cut' => $cut,
       ]);
    }

    public function save($id) {
        if (!PermissionsHelper::allows('viadd')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $cut = VideoCut::find($id);
        if (!$cut) {
            return [
                'status' => 0,
                'text' => 'Данные обрезки не найдены'
            ];
        };
        if ($cut->download_status === 0 ) {
            return $this->start($id);
        }
        if (!$cut->video) {
            if (request()->has('channel_id')) {
                $cut->channel_id = request()->input('channel_id');
            }
            if (request()->has('year')) {
                $cut->year = request()->input('year');
            }
        }
        $cuts = request()->input('cuts', []);
        $old_cuts = $cut->data;
        if (!$old_cuts) {
            $old_cuts = [];
        }

        $cut->data = $cuts;
        $cut->save();

        $indexes = [];
        $errors = [];
        foreach ($cuts as $index => $cut_result) {
            $need_edit = (!isset($cut_result['video_id']) ||
                 isset($old_cuts[$index]) && ($old_cuts[$index]['start'] != $cut_result['start']) ||
                 (!isset($old_cuts[$index]['end']) && isset($cut_result['end'])) ||
                 (isset($old_cuts[$index]['end']) && $old_cuts[$index]['end'] != $cut_result['end'])
            );

            if ($need_edit) {
                $indexes[] = $index;
                $data = $cut_result['data'];
                if ($data['is_advertising'] && empty($data['advertising_brand'])) {
                    $errors[$index] = "Введите рекламируемый товар";
                } elseif (!$data['is_advertising'] && empty($data['interprogram_type'])) {
                    $errors[$index] = "Укажите вид ролика";
                } elseif (!$data['is_advertising']  && !$cut->video && !$cut->channel_id) {
                    $errors[$index] = "Укажите канал";
                }
            }
        }
        if (count($errors) > 0) {
            return [
                'status' => 0,
                'text' => 'В форме есть ошибки',
                'data' => [
                    'errors' => $errors
                ]
            ];
        }
        return [
            'status' => 1,
            'text' => 'Сохранено',
            'data' => [
                'indexes' => $indexes
            ]
        ];
    }

    public function start($id) {
        if (!PermissionsHelper::allows('viadd')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        $video = Record::find($id);
        if (!$video) {
            return [
                'status' => 0,
                'text' => 'Видео не найдено'
            ];
        }

        $cut = VideoCut::firstOrNew([
            'video_id' => $video->id
        ]);
        $cut->data = [];
        $cut->save();

        $output_path = public_path($cut->download_path);

        if ($video->use_own_player) {
            $storage_path = Storage::disk('media-storage')->path($video->source_path);
            Process::forever()->run("cp $storage_path $output_path");
            $this->onDownloaded($cut->id, 1);
        } else {
            $download_url = ExternalServicesHelper::resolveDownloadUrl($video->embed_code);
            if (!$download_url) {
                return [
                    'status' => 0,
                    'text' => 'Не распознан источник видео'
                ];
            }

            DownloadExternalVideoForCut::dispatch($cut, $download_url, $output_path);
        }

        return [
            'status' => 1,
            'text' => $video->use_own_player ? 'Перенаправление...' : 'Видео поставлено в очередь загрузки',
            'redirect_to' => route('cut.show', $cut->id)
        ];
    }


    public function makeVideo($id, $index) {
        if (!PermissionsHelper::allows('viadd')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $cut = VideoCut::find($id);
        if (!$cut) {
            return [
                'status' => 0,
                'text' => 'Данные обрезки не найдены'
            ];
        };
        $cut_results = $cut->data ? $cut->data : [];
        $data_only = request()->input('data_only', false) === true || request()->input('data_only', false) == 1;
        $storage = Storage::disk('media-storage');

        if (isset($cut_results[$index])) {
            $user = auth()->user();

            $path = public_path($cut->download_path);

            $cut_result = $cut_results[$index];
            $start_frame = $cut_result['start'] ? $cut_result['start'] : 0;
            $end_frame = $cut_result['end'] ? $cut_result['end'] : $cut->frames - 1;
            $start = $start_frame / $cut->fps;
            $end = $end_frame / $cut->fps;

            $filename = $cut->id . "_" . $start_frame . "_" . $end_frame;

            $data = $cut_result['data'];
            $video = null;
            if (isset($cut_result['video_id'])) {
                $video = Record::find($cut_result['video_id']);
                if (!$data_only) {
                    $storage->delete($video->source_path);
                }
            }
            if (!$video && $data_only) {
                return [
                    'status' => 0,
                    'text' => 'Видео не найдено'
                ];
            }

            if (!$data_only) {
                if (request()->hasFile('video')) {
                    $file = request()->file('video');
                    $file->move($storage->path("videos"), $filename . ".mp4");
                } else {
                    CutVideo::dispatchSync($path, $filename, $start, $end);
                }
            }
            $original_video = $cut->video;
            if (!$video) {
                $video = new Record([
                    'ucoz_id' => Record::max('ucoz_id') + 1,
                    'is_from_ucoz' => false,
                    'original_added_at' => Carbon::now(),
                    'author_username' => $user->username,
                    'author_id' => $user->id,
                    'description' => '',
                    'short_contents' => '',
                    'views' => 0,
                 ]);
            }
            $set_old_date = request()->input('set_old_date') === '1';
            if ($original_video) {
                $video->author_id = $original_video->author_id;
                $video->author_username = $original_video->author_username;
                $video->channel_id = $original_video->channel_id;
                if ($set_old_date) {
                    $video->created_at = $original_video->getOriginal('created_at');
                    $video->original_added_at = $original_video->getOriginal('original_added_at');
                }
            } else {
                $video->channel_id = $cut->channel_id;
                if ($set_old_date) {
                    $video->created_at = Carbon::createFromDate(2020, 8, 1);
                    $video->original_added_at = Carbon::createFromDate(2020, 8, 1);
                }
            }

            $middle = ($start_frame + (($end_frame - $start_frame) / 2)) / $cut->fps;

            $thumbnail = MediaHelper::makeThumbnail($path, $middle);
            $cover = new Picture([
                'url' => $thumbnail
            ]);
            $cover->save();
            $video->cover_id = $cover->id;

            $video->source_type = "local";
            $video->source_path = "/videos/$filename.mp4";
            if ($original_video) {
                $video->cut_from_id = $original_video->id;
            }
            $video->use_own_player = true;

            if (empty($data['year']) && $original_video) {
                $year = $original_video->year;
            } elseif (empty($data['year']) && !$original_video) {
                $year = request()->input('year');
            } else {
                $year = $data['year'];
            }
            if (!$year || empty($year))  {
                return [
                    'status' => 0,
                    'text' => 'Укажите год'
                ];
            }
            $video->year = $year;
            $video->length = (int)(($end_frame - $start_frame) / $cut->fps);

            if ($data['is_advertising']) {
                $video->is_advertising = true;
                $video->advertising_type = isset($data['advertising_type']) && $data['advertising_type'] > 0 ? $data['advertising_type'] : null;
                $video->advertising_brand = $data['advertising_brand'];
                $video->advertising_category = $data['advertising_category'] ?? "";
                $video->title = $data['advertising_brand'].' ('.$year.')';
                $video->short_description = $data['short_description'] ?? "";
                $video->description = $data['description'] ?? "";
                if (isset($data['region'])) {
                    $video->region = $data['region'];
                }
                if (isset($data['country'])) {
                    $video->region = $data['country'];
                }
            } else {
                $video->is_interprogram = true;
                $video->interprogram_type = $data['interprogram_type'];
                $video->interprogram_package_id = isset($data['interprogram_package_id']) && $data['interprogram_package_id'] > 0 ? $data['interprogram_package_id'] : null;
                $video->short_description = $data['short_description'] ?? "";
                $video->description = $data['description'] ?? "";
                if (!$original_video) {
                    $channel = Channel::find($video->channel_id);
                    if (!$channel) {
                        return [
                            'status' => 0,
                            'text' => 'Укажите канал'
                        ];
                    }
                }

                $type = Genre::find($data['interprogram_type']);
                if (!$type) {
                    return [
                        'status' => 0,
                        'text' => 'Укажите тип материала'
                    ];
                }
                $video->title = $video->generateTitle();
            }
            $video->save();

            $cut_results[$index]['video_id'] = $video->id;
            $cut->data = $cut_results;
            $cut->save();
            return [
                'status' => 1,
                'data' => [
                    'video' => $video,
                    'video_id' => $video->id,
                ]
            ];
        } else {
            return [
                'status' => 0,
                'text' => 'Данные обрезки не найдены'
            ];
        }
    }

    public function delete()
    {
        $cut = VideoCut::find(request()->input('id'));
        if (!PermissionsHelper::allows('viadd') || !$cut) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        ActionsLogHelper::create($cut, Actions::Delete);
        $output_path = public_path($cut->download_path);
        if (file_exists($output_path)) {
            unlink($output_path);
        }

        return [
            'status' => 1,
            'text' => 'Запись удалена',
            'redirect_to' => $cut->video ? route('records.'.$cut->video->route_prefix.'.show', $cut->video->slug) : route('records.videos.index')
        ];
    }

    public function downloadExternal() {
        if (request()->isMethod('post')) {
            request()->validate([
                'url' => 'required|min:1',
            ]);
            $url = request()->input('url');

            $cut = new VideoCut();
            $cut->data = [];
            $cut->save();

            $output_path = public_path($cut->download_path);
            DownloadExternalVideoForCut::dispatch($cut, $url, $output_path);

            return [
                'status' => 1,
                'text' => 'Видео поставлено в очередь загрузки',
                'redirect_to' => route('cut.show', $cut->id)
            ];
        }
        return view ('pages.cut.download', []);
    }

}
