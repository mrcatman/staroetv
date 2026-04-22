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

    protected $signature = 'videos:replace-from-vk {group_id} {user_id} {offset=0} {action?}';
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
        $video->save();

        $video->external_id = ExternalServicesHelper::resolveVkId($video->embed_code);
        echo 'Video saved: '.$video->title.' ('.$found_video->player.')'.PHP_EOL;
    }

    private function normalize($title) {
        $title = preg_replace('/[^a-zA-Z0-9. \p{Cyrillic}]/u', '', $title);
        $replacements = ['(не с начала)', '(не до конца)', '(фрагмент)'];
        foreach ($replacements as $replacement) {
            $title = str_replace($replacement, '', $title);
        }
        return trim($title);
    }

    private function replace() {
        $group_id = -1 * $this->argument('group_id');
        $user_id = $this->argument('user_id');
        $offset = $this->argument('offset') ?? 0;

        $skip = ['Белый попугай', 'Непутёвые заметки', 'Своя игра (НТВ'];

        $videos = Record::where(['author_id' => $user_id])->where(function($query) {
            $query->where('embed_code', 'like', '%youtu%')
                ->orWhereNotNull('telegram_id');
        })->whereNull('source_path')->where(function($q) use ($skip) {
            foreach ($skip as $item) {
                $q->where('title', 'not like', '%'.$item.'%');
            }
        })->orderBy('id', 'desc')->limit(100000)->offset($offset)->get();

        foreach ($videos as $video) {
            $search = ExternalServicesHelper::vkVideoSearch($this->normalize($video->title), $group_id);
            $found_videos = collect($search->response->items);
            foreach ($found_videos as $item) {
                $item->external_id = ExternalServicesHelper::resolveVkId($item->player);
            }
            $already_added = Record::whereIn('external_id', $found_videos->pluck('external_id'))->pluck('external_id');
            $found_videos = $found_videos->filter(function ($item) use ($already_added) {
                return !$already_added->contains($item->external_id);
            });
            if ($found_videos->isEmpty()) {
                echo 'Not found in VK: '.$video->title.PHP_EOL;
                usleep(500000);
                continue;
            }

            echo PHP_EOL.PHP_EOL.'Found '.$found_videos->count().' videos for '.$video->title.PHP_EOL;
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
                    options: ['Accept', 'Skip'],
                );
                if ($action === 'Accept') {
                    $found_video = $found_videos->first();
                    $video->embed_code = '<iframe src="' . $found_video->player . '" frameborder="0" allowfullscreen></iframe>';
                    $this->accept($video, $found_video);
                }
                continue;
            }

            $action = select(
                label: 'Action',
                options: array_merge(['Skip', 'All', 'Manual'], $labels),
            );
            if ($action === 'Skip') {} elseif ($action === 'Manual') {
                $url = text('Enter video URL');
                $video_id = ExternalServicesHelper::resolveVkId($url);
                $response = ExternalServicesHelper::vkVideo($video_id);
                $found_video = $response->response->items[0];
                $this->accept($video, $found_video);
            } elseif ($action === 'All') {
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

    private function fixDuplicates() {
        $group_id = -1 * $this->argument('group_id');
        $user_id = $this->argument('user_id');
        $duplicates = Record::where(['author_id' => $user_id])
            ->select('external_id', 'title', DB::raw('COUNT(*) as count'))
            ->groupBy('external_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        foreach ($duplicates as $duplicate) {
            $search = ExternalServicesHelper::vkVideoSearch($this->normalize($duplicate->title), $group_id);
            $found_videos = collect($search->response->items);
            if ($found_videos->isEmpty()) {
                echo 'Not found in VK: '.$duplicate->title.PHP_EOL;
            }
            dd($duplicate->title, count($found_videos));
        }
    }

    public function handle()
    {

        $action = $this->argument('action') ?? 'replace';

        if ($action === 'replace') {
            $this->replace();
        } elseif ($action === 'fix-duplicates') {
            $this->fixDuplicates();
        }

    }
}
