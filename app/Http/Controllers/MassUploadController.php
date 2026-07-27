<?php

namespace App\Http\Controllers;

use App\Constants\Records;
use App\Helpers\ExternalServicesHelper;
use App\Helpers\MediaServerHelper;
use App\Helpers\PermissionsHelper;
use App\Models\Record;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MassUploadController extends Controller {


    public function index($is_radio) {
        if (!PermissionsHelper::allows('viadd')) {
            return redirect(route('index'));
        }

      //  $can_upload = PermissionsHelper::allows('viupload');
        return view('pages.mass-upload.index', [
            'is_radio' => $is_radio
        ]);
    }

    public function fetchList() {
        if (!PermissionsHelper::allows('viadd')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        $items = null;
        $owner_id = request()->input('source');

        $can_upload = PermissionsHelper::allows('viupload');

        try {
            $files = $can_upload ? collect(Storage::disk('media-storage')->files('temp-upload'))->prepend('') : [];
        } catch (\Exception $e) {
            $files = [];
        }

        $count = 25;
        $offset = request()->input('next_page_token', '');

        if ($owner_id == 'local' && $can_upload) {
            $offset = (int)$offset;
            $next_page_token = $offset + $count;

            $files = array_slice($files, $offset, $count + $offset);
            foreach ($files as $file) {
                $items[] = (object)[
                    'file' => $file,
                    'title' => pathinfo($file, PATHINFO_FILENAME),
                    'description' => '',
                    'player' => null,
                    'thumbnails' => []
                ];
            }
        } elseif ($vk_owner_id = ExternalServicesHelper::resolveVkChannelId($owner_id)) {
            $offset = (int)$offset;
            $next_page_token = $offset + $count;

            $data = ExternalServicesHelper::vkVideoList($vk_owner_id, $count, $offset);
            if (isset($data->error)) {
                return [
                    'status' => 0,
                    'text' => $data->error->error_msg
                ];
            }
            if (!isset($data->response)) {
                return [
                    'status' => 0,
                    'text' => 'Пользователь/группа не найдены'
                ];
            }

            $items = collect($data->response->items)->filter(function ($item) {
                return isset($item->player);
            });

            foreach ($items as $item) {
                $item->external_id = ExternalServicesHelper::resolveVkId($item->player);
            }
            $already_added = Record::whereIn('external_id', $items->pluck('external_id'))->pluck('external_id');

            $items = $items->filter(function ($item) use ($already_added) {
                return !$already_added->contains($item->external_id);
            });
            $items = $items->map(function($item) {
                return [
                    'title' => $this->fixTitle($item->title),
                    'description' => $item->description,
                    'player' => $item->player,
                    'duration' => $item->duration,
                    'code' => str_replace('URL', $item->player, Records::IFRAME_CODE),
                    'thumbnails' => array_values(array_map(function($image) {
                        return $image->url;
                    }, $item->image)),
                ];
            })->values();
        } elseif ($youtube_id = ExternalServicesHelper::resolveYoutubeChannelId($owner_id)) {
            $use_ytdlp_method = Cache::get('use-ytdlp-' . $youtube_id, false);
            if (!$use_ytdlp_method) {
                try {
                    $data = ExternalServicesHelper::youtubeVideoList($youtube_id, $count, $offset);
                } catch (\Exception $e) {
                    $use_ytdlp_method = true;
                    Cache::put('use-ytdlp-' . $youtube_id, 3600);
                }
            }

            if ($use_ytdlp_method) {
                $range_start = (int)$offset;
                $range_end = $range_start + $count;
                $next_page_token = $range_end;
                $data = MediaServerHelper::playlist($owner_id, $range_start.'-'.$range_end);
                $items = collect($data);
                foreach ($items as $item) {
                    $item->external_id = $item->id;
                }
                $already_added = Record::whereIn('external_id', $items->pluck('external_id'))->pluck('external_id');
                $items = $items->filter(function ($item) use ($already_added) {
                    return !$already_added->contains($item->external_id);
                });
                $items = $items->map(function ($item) {
                    return [
                        'title' => $this->fixTitle($item->title),
                        'description' => '',
                        'duration' => $item->duration,
                        'player' => 'https://youtube.com/embed/' . $item->id,
                        'code' => str_replace('URL', 'https://youtube.com/embed/' . $item->id, Records::IFRAME_CODE),
                        'thumbnails' => array_map(function($thumbnail) {
                            return $thumbnail->url;
                         }, $item->thumbnails)
                    ];
                })->values();
            } else {
                $items = collect($data->items);
                $next_page_token = $data->nextPageToken;
                foreach ($items as $item) {
                    $item->external_id = $item->snippet->resourceId->videoId;
                }
                $already_added = Record::whereIn('external_id', $items->pluck('external_id'))->pluck('external_id');
                $items = $items->filter(function ($item) use ($already_added) {
                    return !$already_added->contains($item->external_id);
                });
                $items = $items->map(function ($item) {
                    return [
                        'title' => $item->snippet->title,
                        'description' => $item->snippet->description,
                        'player' => 'https://youtube.com/embed/' . $item->snippet->resourceId->videoId,
                        'code' => str_replace('URL', 'https://youtube.com/embed/' . $item->snippet->resourceId->videoId, Records::IFRAME_CODE),
                        'thumbnails' => [
                            $item->snippet->thumbnails->high->url
                        ]
                    ];
                })->values();
            }
        } else {
            return [
                'status' => 0,
                'text' => 'Введена некорректная ссылка'
            ];
        }
        return [
            'status' => 1,
            'data' => [
                'next_page_token' => $next_page_token,
                'items' => $items,
                'files' => $files,
            ]
        ];
    }

    public function importFromTelegram()
    {
        $files = ['mrcatmann_vhs', 'mrcatmann_vhs_ads'];

        $names_ids = [];
        $video_channels = [];
        foreach ($files as $file) {
            $tg_videos = json_decode(file_get_contents(public_path($file . '.json')), 1);
            $tg_videos = $tg_videos['messages'];

            //$tg_videos = array_merge($tg_videos, $data['messages']);

            //$tg_videos = array_reverse($tg_videos);
            $latest_video_ids = [];

            $names_text = null;
            $names_text_index = -1;

            foreach ($tg_videos as $index => &$tg_video) {
                //$orig_text = $tg_video['text'];
                if (is_array($tg_video['text'])) {
                    $text = '';
                    foreach ($tg_video['text'] as $item) {
                        $text .= isset($item['text']) ? $item['text'] : $item;
                    }
                    $tg_video['text'] = $text;
                }
                $tg_video['text'] = explode(PHP_EOL, $tg_video['text']);
                $new_lines = [];
                foreach ($tg_video['text'] as $line) {
                    $line = str_replace(PHP_EOL, '', $line);
                    $line = trim($line);
                    if (mb_strlen($line) > 0) {
                        if (!(ctype_digit(substr($line, 0, 1)) && mb_strpos($line, ':') !== false)) {
                            $new_lines[] = $line;
                        }
                    }
                }
                $tg_video['text'] = implode(PHP_EOL, $new_lines);

                if (Str::contains($tg_video['text'], 'Раннее ABS')) {
                    //dd($tg_video['text'], $orig_text);
                }
                if ($tg_video['text'] != '') {
                    $names_text_index = $index;
                    $names_text = explode(PHP_EOL, $tg_video['text']);
                    if (count($names_text) > 1) {
                        $tg_video['text'] = $names_text[0];
                    }
                }
                if ($tg_video['text'] == '' && $names_text) {
                    if (isset($names_text[$index - $names_text_index])) {
                        $tg_video['text'] = $names_text[$index - $names_text_index];
                    } else {
                        $tg_video['text'] = $names_text[0];
                    }
                }
                if (Str::startsWith($tg_video['text'], '- ')) {
                    $tg_video['text'] = str_replace('- ', '', $tg_video['text']);
                }
                if (isset($tg_video['file_name'])) {
                    $filename = explode('_', $tg_video['file_name']);
                    $filename = implode(' ', $filename);
                    $filename = str_replace('.mp4', '', $filename);
                    if (!Str::startsWith($tg_video['text'], $filename) && $index > 90) {
                        //dd($tg_video['text'], $tg_video['file_name'], $filename);
                    }
                }
                $replacements = [
                    'КХСМ' => ['Кто хочет стать миллионером?'],
                    'Кто хочет стать миллионером' => ['Кто хочет стать миллионером?']
                ];

                $variants = [$tg_video['text']];
                //if (strpos($tg_video['text'], '"') !== false) {
                 //   $variants[] = str_replace('"', "", $tg_video['text']);
                //}
               // if (strpos($tg_video['text'], "'") !== false) {
                 //   $variants[] = str_replace("'", '', $tg_video['text']);
             //   }
                foreach ($replacements as $search => $replacements_list) {
                    if (Str::contains($tg_video['text'], $search)) {
                        foreach ($replacements_list as $replacement) {
                      //      $variants[] = str_replace($search, $replacement, $tg_video['text']);
                        }
                    }
                }
                foreach ($variants as $variant) {
                    $video_channels[$variant] = $file;
                    if (!isset($names_ids[$tg_video['text']])) {
                        $names_ids[$variant] = [$tg_video['id']];
                    } else {
                        if (isset($latest_video_ids[$variant]) && $tg_video['id'] - $latest_video_ids[$variant][count($latest_video_ids[$variant]) - 1] > 10) {
                            $names_ids[$variant] = [];
                        }
                        $names_ids[$variant][] = $tg_video['id'];
                    }
                    if (!isset($latest_video_ids[$variant])) {
                        $latest_video_ids[$variant] = [$tg_video['id']];
                    } else {
                        $latest_video_ids[$variant][] = $tg_video['id'];
                    }

                }
            }
        }
        //$tg_videos = array_slice($tg_videos, -500);
        //return $tg_videos;
        $videos = Record::whereNull('telegram_id')->where(['author_id' => 3358, 'is_from_ucoz' => 0, 'use_own_player' => false])->limit(1)->offset(request()->input('offset', 0))->orderBy('created_at', 'desc')->get();
        foreach ($videos as $index => $video) {
            $title = str_replace('"', '', $video->title);
            $title = str_replace("'", '', $title);
            if (isset($names_ids[$title])) {
                $telegram_id = $video_channels[$title].'/'.implode(',', $names_ids[$title]);
               // dump($title, $telegram_id);
                $video->telegram_id = $telegram_id;
                $video->save();
//                if (count($names_ids[$video->title]) > 1) {
//                    dump($names_ids[$video->title], $video_channels[$video->title], $video->title);
//                }
            } else {
                if (mb_strlen($title) > 120) {
                    $title = mb_substr($title, 0, 120);
                }
                $levenshteins = [];
                foreach ($names_ids as $name => $ids) {
                    $fixed = $name;
                    if ($fixed != '') {
                        if (mb_strpos($fixed, '(') !== false) {
                            $fixed = mb_substr($name, 0, mb_strrpos($fixed, '(') - 1);
                        }
                        if (mb_strlen($fixed) > 120) {
                            $fixed = mb_substr($fixed, 0, 120);
                        }
                        $levenshteins[$name] = levenshtein($fixed, $title);
                    }
                }
                asort($levenshteins);
                $levenshteins = array_slice($levenshteins, 0, 10);

                $rnd = mt_rand(0, 500000);
                if (request()->has('offset') && request()->has('v')) {
                    if (request()->input('v') >= 0) {
                        $selected_title = array_keys($levenshteins)[request()->input('v')];
                        $telegram_id = $video_channels[$selected_title] . '/' . implode(',', $names_ids[$selected_title]);
                        $video->telegram_id = $telegram_id;
                        $video->save();
                        return redirect('/mass-upload/import-from-telegram?rnd='.$rnd.'&offset='.(request()->input('offset')));
                    }
                    return redirect('/mass-upload/import-from-telegram?rnd='.$rnd.'&offset='.(request()->input('offset') + 1));
                } else {
                    $levenshtein_index = 0;
                    echo '<h1>'.$title.'</h1>';

                    echo '<a href="/mass-upload/import-from-telegram?rnd='.$rnd.'&offset='.(request()->input('offset') + 1).'"><h4>Skip</h4></a><br/>';
                    foreach ($levenshteins as $name => $levenshtein) {
                        echo '<a href="/mass-upload/import-from-telegram?rnd='.$rnd.'&offset='.request()->input('offset').'&v='.$levenshtein_index.'">'.$name.'</a><br/>';
                        $levenshtein_index++;
                    }
                }
                //
                //dump($title);
            }
        }
    }

    private function fixTitle($title) {
        return str_replace('.mp4', '', $title);
    }

}
