<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Channel;
use App\Models\ChannelName;
use App\Models\DesignPackage;
use App\Models\Picture;
use App\Models\Program;
use App\Models\Record;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class FixPictures extends Command
{

    protected $signature = 'pictures:fix {--type=}';

    protected $description = '';

    private $folders = ['imported', 'logos', 'uploads', 'video_covers'];

    public function handle()
    {
        $type = $this->option('type');
        switch ($type) {
            case 'db-duplicates':
                $this->fixDBDuplicates();
                break;
            case 'hash-duplicates':
                $this->fixHashDuplicates();
                break;
            case 'compress':
                $this->compress();
                break;
        }
    }

    private function fixDBDuplicates() {
        $duplicates = DB::table('pictures')->select('url', DB::raw('count(*) as count'))->groupBy('url')->having('count', '>', 1)->get();
        foreach ($duplicates as $duplicate) {
            $pictures = Picture::where(['url' => $duplicate->url])->orderByDesc('id')->get();
            $first = $pictures->shift();
            foreach ($pictures as $picture) {
                $this->fixModels($picture, $first);
            }
        }
    }

    private function fixHashDuplicates() {
        $storage = Storage::disk('public_data');
        $pictures_by_hash = [];
        foreach ($this->folders as $folder) {
            $pictures = $storage->allFiles("pictures/$folder");
            $filesWithTimestamp = collect($pictures)->filter(function ($file) {
                return !str_contains($file, '.svg');
            })->map(function ($file) use ($storage) {
                return [
                    'path' => $file,
                    'timestamp' => $storage->lastModified($file),
                ];
            });
            $pictures = $filesWithTimestamp->sortBy('timestamp')->pluck('path');

            foreach ($pictures as $picture) {
                $hash = hash_file('sha256', $storage->path($picture));
                if (isset($pictures_by_hash[$hash])) {
                    echo 'Found duplicate: ' . $picture . ' (original: '.$pictures_by_hash[$hash]->url.')'. PHP_EOL;
                    $picture_entity_to_fix = Picture::where('url', 'LIKE', '%'.$picture.'%')->first();
                    if (!$picture_entity_to_fix) {
                        echo 'Picture to fix not found in DB, deleting: ' . $picture_entity_to_fix . PHP_EOL;
                        $storage->delete($picture);
                    } else {
                        $this->fixModels($picture_entity_to_fix, $pictures_by_hash[$hash]);
                        $storage->delete($picture);
                        echo 'Deleted duplicate: ' . $picture . PHP_EOL;
                    }
                } else {
                    $picture_entity = Picture::where('url', 'LIKE', '%'.$picture.'%')->first();
                    if (!$picture_entity) {
                        echo 'Original picture not found in DB, deleting: ' . $picture . PHP_EOL;
                        $storage->delete($picture);
                    }
                    $pictures_by_hash[$hash] = $picture_entity;
                }
            }
        }
    }

    private function fixModels(Picture $picture, Picture $picture_to_set) {
        $models = [
            [Article::class, 'cover_id', 'title'],
            [Channel::class, 'logo_id', 'name'],
            [ChannelName::class, 'logo_id', 'name'],
            [Program::class, 'cover_id', 'name'],
            [Record::class, 'cover_id', 'title'],
            [DesignPackage::class, 'cover_id', 'url'],
            [User::class, 'avatar_id', 'username']
        ];
        $has_entities = false;
        $entities = [];
        foreach ($models as $model) {
            $entities[$model[0]] = $model[0]::where([$model[1] => $picture->id])->get();
            if (count($entities[$model[0]]) > 0) {
                $has_entities = true;
                foreach ($entities[$model[0]] as $entity) {
                    echo 'Duplicate picture #'.$picture->id. ' ('.$picture->url.') used in ' . $model[0] . ' ' . $entity->{$model[2]}.', changing to #'.$picture_to_set->id.PHP_EOL;
                    $entity->{$model[1]} = $picture_to_set->id;
                    $entity->save();
                }
            }
        }
        if (!$has_entities) {
            echo 'No entities found for duplicate picture #' . $picture->id.' ('.$picture->url.')' . PHP_EOL;
        } else {
            $picture->delete();
        }
    }

    private function readableFilesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

    private function compress() {
        $storage = Storage::disk('public_data');
        $limit = 1024 * 1024 * .25; // 256 KB

        foreach ($this->folders as $folder) {
            $pictures = $storage->allFiles("pictures/$folder");
            $filesWithTimestamp = collect($pictures)->map(function ($file) use ($storage) {
                return [
                    'path' => $file,
                    'size' => $storage->size($file),
                ];
            });
            $pictures = $filesWithTimestamp->sortByDesc('size');
            foreach ($pictures as $picture) {
                if (str_contains($picture['path'], 'svg')) {
                    continue;
                }
                if ($picture['size'] < $limit) {
                    continue;
                }
                echo 'Compressing: ' . $picture['path'] . ' ('.$this->readableFilesize($picture['size']).')'.PHP_EOL;
                $picture_entity = Picture::where('url', 'LIKE', '%'.$picture['path'].'%')->first();
                if (!$picture_entity) {
                    echo 'Picture not found in DB, skipping: ' . $picture['path'] . PHP_EOL;
                    continue;
                }
                try {
                    $new_path = $picture_entity->compress();
                    $picture_entity->save();

                    echo 'Reencoded '.$picture['path'].' to ' . $new_path . PHP_EOL;
                } catch (\Exception $e) {
                    echo 'Error while compressing image: ' . $e->getMessage() . PHP_EOL;
                }
            }
        }
    }
}
