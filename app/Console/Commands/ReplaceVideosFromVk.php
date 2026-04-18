<?php

namespace App\Console\Commands;

use App\Helpers\ExternalServicesHelper;
use App\Models\Picture;
use App\Models\Record;
use Illuminate\Console\Command;

class ReplaceVideosFromVk extends Command
{

    protected $signature = 'videos:replace-from-vk {group_id} {user_id} {page=1}';
    protected $description = 'Command description';

    public function handle()
    {
        $group_id = -1 * $this->argument('group_id');
        $user_id = $this->argument('user_id');
        $page = $this->argument('page');

        $count = 100;
        $offset = ($page - 1) * $count;

        $data = ExternalServicesHelper::vkVideoList($group_id, $count, $offset);
        $found_videos = collect($data->response->items);
        $videos = Record::where(['author_id' => $user_id])->whereIn('title', $found_videos->pluck('title'))->get();
        foreach ($videos as $video) {
            if (strpos($video->embed_code, 'vk.com') !== false) {
                continue;
            }
            // todo части
            $found_video = $found_videos->firstWhere(function ($item) use ($video){
                return trim(mb_strtolower($item->title)) == trim(mb_strtolower($video->title));
            });
            if (!isset($found_video->image)) {
                echo 'Image not found, probably video broken'.PHP_EOL;
                echo json_encode($found_video).PHP_EOL;
            }

            $thumbnail = $found_video->image[count($found_video->image) - 1]->url;

            $cover = Picture::firstOrNew([
                'url' => $thumbnail
            ]);
            $cover->save();

            $video->cover_id = $cover->id;
            $video->embed_code = '<iframe src="'.$found_video->player.'" frameborder="0" allowfullscreen></iframe>';
            $video->telegram_id = null;

            $video->save();
            echo 'Video found: '.$video->title.PHP_EOL;
        }
    }
}
