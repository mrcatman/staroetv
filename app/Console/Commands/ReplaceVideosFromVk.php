<?php

namespace App\Console\Commands;

use App\Helpers\ExternalServicesHelper;
use App\Models\Picture;
use App\Models\Record;
use Illuminate\Console\Command;
use function Laravel\Prompts\select;

class ReplaceVideosFromVk extends Command
{

    protected $signature = 'videos:replace-from-vk {group_id} {user_id} {offset?}';
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

        echo 'Video saved: '.$video->title.' ('.$found_video->player.')'.PHP_EOL;
    }

    public function handle()
    {
        $group_id = -1 * $this->argument('group_id');
        $user_id = $this->argument('user_id');
        $offset = $this->argument('offset') ?? 0;

        $videos = Record::where(['author_id' => $user_id])->where(function($query) {
            $query->where('embed_code', 'like', '%youtu%')
                ->orWhereNotNull('telegram_id');
        })->limit(10000)->offset($offset)->get();

        foreach ($videos as $video) {
            $search = ExternalServicesHelper::vkVideoSearch($video->title, $group_id);
            $found_videos = collect($search->response->items);

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
                options: array_merge(['Skip', 'All'], $labels),
            );
            if ($action === 'Skip') {} elseif ($action === 'All') {
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
                $this->accept($video, $found_video);
            }
        }
    }
}
