<?php

namespace App\Console\Commands;

use App\Helpers\ExternalServicesHelper;
use App\Models\Picture;
use App\Models\Record;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ReplaceVideosFromVk extends Command
{

    protected $signature = 'videos:replace-from-vk {group_id} {user_id} {--offset=0} {--action=replace} {--filter=} {--order=id} {--extended-search=0}';
    protected $description = 'Command description';

    private function accept($video, $found_video)
    {
        $thumbnail = $found_video->image[count($found_video->image) - 1]->url;

        $cover = Picture::firstOrNew([
            'url' => $thumbnail
        ]);
        $cover->save();

        $video->cover_id = $cover->id;

        $video->telegram_id = null;
        $video->external_id = ExternalServicesHelper::resolveVkId($video->embed_code);

        $video->save();

        echo 'Video saved: '.$video->title.' ('.$found_video->player.')'.PHP_EOL;
    }

    private function normalize($title) {
        $title = preg_replace('/ \[г.(.*?)\]/', '', $title);
        $title = preg_replace('/ \(г.(.*?)\)/', '', $title);
        $title = str_replace("'", ' ', $title);
        $title = str_replace('-', ' ', $title);
        $title = str_replace(':', ' ', $title);
        $title = str_replace('.', ' ', $title);
        $title = preg_replace('/[^a-zA-Z0-9 \p{Cyrillic}]/u', '', $title);
        $replacements = ['(не с начала)', '(не до конца)', '(фрагмент)'];
        foreach ($replacements as $replacement) {
            $title = str_replace($replacement, '', $title);
        }
        return trim($title);
    }

    private function search($title, $group_id) {
        if ($this->option('extended-search') == '1') {
            $data = explode('(', $title);
            $name = trim($data[0]);
            $channel_and_date_string = explode(')', $data[1])[0];
            $channel_and_date = explode(',', $channel_and_date_string);
            $date = isset($channel_and_date[1]) ? $channel_and_date[1] : $channel_and_date[0];
            $channel = $channel_and_date[0];
            $search = ExternalServicesHelper::vkVideoSearch($name.' '.$channel, $group_id);
            $found_videos = collect($search->response->items)->filter(function ($item) use ($date) {
                return str_contains($item->title, $date);
            });
            if (count($found_videos) === 0) {
                echo 'Not found videos by name, trying to search by date'.PHP_EOL;
            }
            usleep(500000);

            $search = ExternalServicesHelper::vkVideoSearch('"'.$date.'"', $group_id);
            $found_videos = collect($search->response->items);
        } else {
            $search = ExternalServicesHelper::vkVideoSearch($this->normalize($title), $group_id);
            $found_videos = collect($search->response->items);
        }

        foreach ($found_videos as $item) {
            $item->external_id = ExternalServicesHelper::resolveVkId($item->player);
        }
        $already_added = Record::whereIn('external_id', $found_videos->pluck('external_id'))->pluck('external_id');
        $filtered_videos = $found_videos->filter(function ($item) use ($already_added) {
            if ($already_added->contains($item->external_id)) {
                echo 'Skipping video: '.$item->title.' ('.$item->id.')'.PHP_EOL;
            }
            return !$already_added->contains($item->external_id);
        })->values();

        return $filtered_videos;
    }

    private function getLabels($found_videos) {
        $labels = [];

        foreach ($found_videos as $index => $found_video) {
            if (!isset($found_video->image)) {
                echo 'Image not found, probably video broken' . PHP_EOL;
                echo json_encode($found_video) . PHP_EOL;
            } else {
                $label = '#' . $index . '. ' . $found_video->title . '(' . $found_video->share_url . ')' . PHP_EOL;
                echo $label;
                $labels[] = $label;
            }
        }
        return $labels;
    }

    private function getVideos() {

        $user_id = $this->argument('user_id');
        $offset = $this->option('offset');
        $filter = $this->option('filter');

        $skip = ['Белый попугай', 'Непутёвые заметки', 'Своя игра (НТВ', 'Смехопанорама'];

        $records = Record::where(['author_id' => $user_id])->where(function($query) {
            $query->where('embed_code', 'like', '%youtu%')
                ->orWhereNotNull('telegram_id');
        })->whereNull('source_path')->where(function($q) use ($skip) {
            foreach ($skip as $item) {
                $q->where('title', 'not like', '%'.$item.'%');
            }
        })->limit(100000)->offset($offset);
        if ($filter != '') {
            $records = $records->where('title', 'like', '%'.$filter.'%');
        }
        $order = $this->option('order');
        if ($order == 'title') {
            $records = $records->orderBy('title', 'asc');
        } else {
            $records = $records->orderBy('id', 'desc');
        }

        return $records->get();
    }

    private function replace() {
        $group_id = -1 * $this->argument('group_id');

        $videos = $this->getVideos();

        foreach ($videos as $video) {
            $found_videos = $this->search($video->title, $group_id);
            if ($found_videos->isEmpty()) {
                echo 'Not found in VK: '.$this->normalize($video->title).PHP_EOL;
                if ($this->option('extended-search') == '1') {
                    $url = text('Enter video URL');
                    if (trim($url) === '') {
                        continue;
                    }
                    $video_id = ExternalServicesHelper::resolveVkId($url);
                    $response = ExternalServicesHelper::vkVideo($video_id);
                    $found_video = $response->response->items[0];
                    $this->accept($video, $found_video);
                } else {
                    usleep(500000);
                    $new_title = explode(')', $video->title)[0].')';
                    if ($new_title !== $video->title) {
                        $found_videos = $this->search($new_title, $group_id);
                        if ($found_videos->isEmpty()) {
                            echo 'Not found (2): ' . $this->normalize($new_title) . PHP_EOL;
                            continue;
                        }
                    } else {
                        continue;
                    }
                }

            }

            echo PHP_EOL.PHP_EOL.'Found '.$found_videos->count().' videos for '.$video->title.PHP_EOL;
            $labels = $this->getLabels($found_videos);

            if ($this->normalize($found_videos->first()->title) === $this->normalize($video->title)) {
                echo 'Auto accepting: '.$video->title.PHP_EOL;
                $video->embed_code = '<iframe src="' . $found_videos->first()->player . '" frameborder="0" allowfullscreen></iframe>';
                $this->accept($video, $found_videos->first());
                usleep(500000);
                continue;
            }

            if ($found_videos->count() === 1) {
                $action = select(
                    label: 'Action',
                    options: ['Accept', 'Manual', 'Skip'],
                );
                if ($action === 'Accept') {
                    $found_video = $found_videos->first();
                    $video->embed_code = '<iframe src="' . $found_video->player . '" frameborder="0" allowfullscreen></iframe>';
                    $this->accept($video, $found_video);
                }
                if ($action === 'Manual') {
                    $this->manual($video);
                }
                continue;
            }

            $action = select(
                label: 'Action',
                options: array_merge(['Skip', 'All', 'Manual', 'Multiple'], $labels),
            );
            if ($action === 'Skip') {} elseif ($action === 'Manual') {
                $this->manual($video);;
            } elseif ($action === 'All' || $action === 'Multiple') {
                if ($action === 'Multiple') {
                    $indexes = text('Enter indexes');
                    $found_videos = $found_videos->only(explode(',', $indexes));
                }
                $found_videos = $found_videos->sort(function ($item1, $item2) {
                    return strcmp($item1->title, $item2->title);
                });

                $thumbnail = $found_videos->first()->image[count($found_videos->first()->image) - 1]->url;

                $cover = Picture::firstOrNew([
                    'url' => $thumbnail
                ]);
                $cover->save();

                $video->cover_id = $cover->id;
                $video->embed_code = $found_videos->map(function($found_video) {
                    return '<iframe src="' . $found_video->player . '" frameborder="0" allowfullscreen></iframe>';
                })->join('|');
                $video->telegram_id = null;
                $video->save();

                echo 'Video saved in multiple parts: '.$video->title.' ('.$video->embed_code.')'.PHP_EOL;
            } else {
                $index = array_search($action, $labels);
                $found_video = $found_videos->get($index);
                $video->embed_code = '<iframe src="' . $found_video->player . '" frameborder="0" allowfullscreen></iframe>';
                $this->accept($video, $found_video);
            }
        }
    }

    private function manual($video) {
        $url = text('Enter video URL');
        $video_id = explode("video", $url)[1];
        $video_id = explode("?", $video_id)[0];
        $response = ExternalServicesHelper::vkVideo($video_id);
        $found_video = $response->response->items[0];
        $this->accept($video, $found_video);
    }

    private function fixDuplicates() {
        $group_id = -1 * $this->argument('group_id');
        $user_id = $this->argument('user_id');
        $duplicates = Record::where(['author_id' => $user_id])
            ->select('external_id', 'title', DB::raw('COUNT(*) as count'))
            ->groupBy('external_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        foreach ($duplicates as $duplicate) {
            $found_videos = $this->search($duplicate->title, $group_id);
            if ($found_videos->isEmpty()) {
                echo 'Not found in VK: '.$duplicate->title.PHP_EOL;
            } else {
                echo PHP_EOL.PHP_EOL.'Found '.$found_videos->count().' videos for '.$duplicate->title.' ('.$duplicate->count.' duplicates)'.PHP_EOL;
                $labels = $this->getLabels($found_videos);
                $videos = Record::where(['external_id' => $duplicate->external_id])->get();
                foreach ($videos as $video) {
                    if (count($labels) === 0) {
                        echo 'No more videos to select, continuing'.PHP_EOL;
                        continue;
                    }
                    $action = select(
                        label: 'Action',
                        options: $labels,
                    );
                    $index = array_search($action, $labels);
                    $labels = array_filter($labels, function ($label) use ($action) {
                        return $label !== $action;
                    });

                    $found_video = $found_videos->get($index);
                    $found_videos->forget($index);
                    $found_videos = $found_videos->values();
                    if (!$found_video) {
                        continue;
                    }

                    $video->embed_code = '<iframe src="' . $found_video->player . '" frameborder="0" allowfullscreen></iframe>';
                    $this->accept($video, $found_video);
                }
            }
        }
    }

    private function dumpTitles() {
        $videos = $this->getVideos();
        foreach ($videos as $video) {
            echo $video->title.PHP_EOL;
        }
    }

    public function handle()
    {

        $action = $this->option('action');

        if ($action === 'replace') {
            $this->replace();
        } elseif ($action === 'fix-duplicates') {
            $this->fixDuplicates();
        } elseif ($action === 'dump-titles') {
            $this->dumpTitles();
        }

    }
}
