<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ChannelName;
use App\Genre;
use App\Helpers\PermissionsHelper;
use App\InterprogramPackage;
use App\Picture;
use App\Program;
use App\Record;
use App\VideoCut;
use Carbon\Carbon;
class VideoCutController extends Controller {

    public function showForm($id) {
        $video = Record::find($id);
        if (!$video || !PermissionsHelper::allows('viadd')) {
            return redirect("https://staroetv.su/");
        }
        $cut = VideoCut::where(['video_id' => $id])->first();
        if ($cut) {
            if (!request()->has('reload')) {
                return redirect("https://staroetv.su/cut/" . $cut->id);
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
            return redirect("https://staroetv.su/");
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
            $need_edit = false;
            if (!isset($cut_result['video_id'])) {
                $need_edit = true;
            } elseif (isset($old_cuts[$index]) && ($old_cuts[$index]['start'] != $cut_result['start']) || (!isset($old_cuts[$index]['end']) && isset($cut_result['end'])) || (isset($old_cuts[$index]['end']) && $old_cuts[$index]['end'] != $cut_result['end'])) {
                $need_edit = true;
            }
            //$need_edit = true;
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
        if (!$video->use_own_player) {
            preg_match('/<iframe(.*?)src="(.*?)" /', $video->embed_code, $output);
            if (count($output) != 3) {
                preg_match('/<iframe(.*?)src=(.*?) (.*?) /', $video->embed_code, $output);
                if (isset($output[3])) {
                    unset($output[3]);
                }
            }
            if (count($output) != 3) {
                return [
                    'status' => 0,
                    'text' => 'Не распознан источник видео'
                ];
            }
            $url = $output[2];
            if ($url[0] == "/") {
                $url = "https:" . $url;
            }
            $path = "temp_videos/" . $video->id . ".mp4";
        } else {
            $path = $video->source_path;
        }
        $output_path = public_path($path);
        $cut = VideoCut::firstOrNew([
            'video_id' => $video->id
        ]);
        $cut->download_path = $path;
        $cut->data = [];
        $cut->save();
        if ($video->use_own_player) {
            $this->onDownloaded($cut->id, 1);
        } else {
            $command = "youtube-dl -i '$url' --output $output_path && curl https://staroetv.su/cut/downloaded/" . $cut->id . "?status=1 || curl https://staroetv.su/cut/downloaded/" . $cut->id . "?status=0";
             $output = shell_exec($command);
            if (strpos($output, ".mkv") !== false) {
                $mkv_path = str_replace(".mp4", ".mkv", $output_path);
                $convert_command = "ffmpeg -y -i $mkv_path -c:v libx264 $output_path && rm $mkv_path";
                shell_exec($convert_command);
            }
        }
        return [
            'status' => 1,
            'text' => $video->use_own_player ? 'Перенаправление...' : 'Видео поставлено в очередь загрузки',
            'command' => $command,
          //  'redirect_to' => '/cut/' . $cut->id
        ];
    }

    public function onDownloaded($id, $status = 0) {
        $cut = VideoCut::find($id);
        if ($cut) {
            $path = public_path($cut->download_path);
            $fps = shell_exec("ffprobe -v error -select_streams v -of default=noprint_wrappers=1:nokey=1 -show_entries stream=r_frame_rate $path");
            $frames = shell_exec("ffprobe -v error -select_streams v:0 -show_entries stream=nb_frames -of default=nokey=1:noprint_wrappers=1 $path");
            $cut->fps = (int)explode("/", $fps)[0];
            if (count($fps_data = explode("/", $cut->fps) ) === 2) {
                $fps = (int)$fps_data[0] / (int)$fps_data[1];
                $cut->fps = $fps;
            }
            $cut->frames = (int)$frames;
            $cut->download_status = request()->input('status', $status);
            $cut->save();
            return [
                'status' => 1,
                'data' => [
                    'cut' => $cut
                ]
            ];
        } else {
            return [
                'status' => 0
            ];
        }
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
        if (isset($cut_results[$index])) {
            $user = auth()->user();

            $input_path = public_path($cut->download_path);

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
                    unlink(public_path($video->source_path));
                }
            }
            if (!$video && $data_only) {
                return [
                    'status' => 0,
                    'text' => 'Видео не найдено'
                ];
            }

            $output = public_path("videos/$filename.mp4");
            $command = null;
            if (!$data_only) {
                if (request()->hasFile('video')) {
                    $file = request()->file('video');
                    $file->move(public_path("videos"), $filename . ".mp4");
                } else {
                    $command = "ffmpeg -y -i $input_path -c:v libx264 -acodec copy -ss $start -to $end $output";
                    shell_exec($command);
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

            $screenshot_path = "/pictures/video_covers/$filename.jpg";
            $screenshot_command = "ffmpeg -y -ss $middle -i $input_path -vframes 1 ".public_path($screenshot_path);
            shell_exec($screenshot_command);

            $cover = new Picture([
                'url' => $screenshot_path
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
                $video->title = $data['advertising_brand'].' ('.$year.')';
                $video->short_description = isset($data['short_description']) ? $data['short_description'] : "";
                $video->description = isset($data['short_description']) ? $data['short_description'] : "";
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
                $video->short_description = isset($data['short_description']) ? $data['short_description'] : "";
                if ($original_video) {
                    $channel_name = $original_video->getChannelName();
                } else {
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
                    'command' => $command,
                    'screenshot_command' => $screenshot_command
                ]
            ];
        } else {
            return [
                'status' => 0,
                'text' => 'Данные обрезки не найдены'
            ];
        }
    }

    public function downloadExternal() {
        if (request()->isMethod('post')) {
            request()->validate([
                'url' => 'required|min:1',
            ]);
            $url = request()->input('url');
            $path = "temp_videos/" .time().".mp4";
            $output_path = public_path($path);
            $cut = new VideoCut();
            $cut->download_path = $path;
            $cut->data = [];
            $cut->save();
            $command = "youtube-dl -f 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4' -i '$url' --output $output_path && curl http://staroetv.su/cut/downloaded/" . $cut->id . "?status=1 || curl http://staroetv.su/cut/downloaded/" . $cut->id . "?status=0";
            exec('bash -c "exec nohup setsid '.$command.' > /dev/null 2>&1 &"');

            return [
                'status' => 1,
                'text' => 'Видео загружено',
                'redirect_to' => '/cut/' . $cut->id
            ];
        }
        return view ('pages.cut.download', [

        ]);
    }

}
