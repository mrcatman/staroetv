<?php

namespace App\Console\Commands;

use App\API\UcozAPI;
use App\Channel;
use App\ChannelName;
use App\Helpers\CSVHelper;
use App\Picture;
use App\Program;
use App\Record;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function saveCover($url) {
        $cover = Picture::where(['url' => $url])->first();
        if (!$cover) {
            $cover = new Picture();
            $cover->url = $url;
            $cover->save();
        }
        return $cover->id;
    }

    public function handle() {
        $interprogram_keys = ["анонс", "вещани", "реклам", "заставк", "ролик", "программа передач", "погод", "эфира", "спонсор", "часы"];

        $programs_data = Program::all();
        $programs = [];
        foreach ($programs_data as $program) {
            if (!isset($programs[$program->channel_id])) {
                $programs[$program->channel_id] = [];
            }
            $programs[$program->channel_id][$program->name] = $program->id;
        }

        $channels = Channel::pluck('id', 'name');
        foreach (ChannelName::all() as $name) {
            $channels[$name->name] = $name->channel_id;
        }
        $ucoz_ids = Record::pluck('ucoz_id')->toArray();

        $videos = CSVHelper::transform(public_path("data_new/video.txt"), [
            'ucoz_id', '', '', 'created_at',  '', '', '', '', '', '', '', 'views', '', 'description', 'title', '', '', '', '', '', '', 'cover', '', '', '', 'author_username', '', '', '', 'embed_code', '', '', '', '', '',  '', '', '', '', '', 'author_id', '', '', 'ucoz_url'
        ], false);
        //$videos = array_slice($videos, 1000, 10);

        foreach ($videos as $video) {
           // $record = Record::where(['ucoz_id' => $video['ucoz_id']])->first();
            if (in_array($video['ucoz_id'], $ucoz_ids)) {
              //  $record = Record::where(['ucoz_id' => $video['ucoz_id']])->first();
              //  $record->created_at = $video['created_at'];
               // $record->save();
               // echo "Record: ".$record->title." Created at:".$video['created_at'].PHP_EOL;
            } else {
                if ($video['created_at'] < 1567167825) {
                    continue;
                }
                var_dump($video['title']);
                continue;
                $video['embed_code'] = html_entity_decode($video['embed_code']);
                $obj = new Record([
                    'is_from_ucoz' => true,
                    'original_added_at' => Carbon::createFromTimestamp($video['created_at']),
                    'author_username' => $video['author_username'],
                    'author_id' => $video['author_id'],
                    'title' => $video['title'],
                    'description' => $video['description'],
                    'embed_code' => $video['embed_code'],
                    'views' => $video['views'],
                    'ucoz_id' => $video['ucoz_id'],
                    'ucoz_url' => $video['ucoz_url'],
                    'cover_id' => $this->saveCover($video['cover'])
                ]);
                preg_match('/(.*?)\((.*?), (.*?)\)(.*)/', $video['title'], $matches);
                if (count($matches) < 3) {
                    echo "Нераспознанное название: " . $video['title'] . PHP_EOL;
                    preg_match('/(.*?)\((.*?)\)(.*)/', $video['title'], $matches);
                    if (count($matches) > 3) {
                        $matches[4] = $matches[3];
                        $matches[3] = "";
                    }
                }
                if (count($matches) >= 3) {
                    $program = trim($matches[1]);
                    echo "Программа: " . $program . PHP_EOL;

                    $channel = trim($matches[2]);
                    echo "Канал: " . $channel . PHP_EOL;
                    $channel_id = null;
                    if (!isset($channels[$channel])) {
                        echo "Канала нет в базе. Создаем" . PHP_EOL;
                        $channel_item = new Channel([
                            'name' => $channel
                        ]);
                        $channel_item->save();
                        $channels[$channel] = $channel_item->id;
                        $channel_id = $channel_item->id;
                    } else {
                        $channel_id = $channels[$channel];
                    }
                    $obj->channel_id = $channel_id;

                    $is_interprogram = false;
                    $program_lower = mb_strtolower($program, "UTF-8");
                    if (mb_strpos($program_lower, "концерт", null, "UTF-8") !== false) {
                        $program = "Концерты";
                    }
                    if (mb_strpos($program_lower, "квн", null, "UTF-8") !== false) {
                        $program = "КВН";
                    }

                    foreach ($interprogram_keys as $interprogram_key) {
                        if (mb_strpos($program_lower, $interprogram_key, null, "UTF-8") !== false) {
                            $is_interprogram = true;
                        }
                    }
                    echo "Межпрограммка: " . ($is_interprogram ? "Да" : "Нет") . PHP_EOL;
                    $obj->is_interprogram = $is_interprogram;
                    if (!$is_interprogram) {
                        $program_id = null;
                        if (!isset($programs[$channel_id]) || !isset($programs[$channel_id][$program])) {
                            echo "Программы нет в базе. Создаем" . PHP_EOL;
                            $program_item = new Program([
                                'name' => $program,
                                'channel_id' => $channel_id,
                                'cover_id' => $this->saveCover($video['cover'])
                            ]);
                            $program_item->save();
                            if (!isset($programs[$channel_id])) {
                                $programs[$channel_id] = [];
                            }
                            $programs[$channel_id][$program] = $program_item->id;
                            $program_id = $program_item->id;
                        } else {
                            $program_id = $programs[$channel_id][$program];
                        }
                        $obj->program_id = $program_id;
                    }

                    if ($matches[3] != "") {
                        $date = $matches[3];
                        $date = trim($date);
                        echo "Дата: " . $date . PHP_EOL;
                        $date = explode(";", $date)[0];
                        $date = str_replace("–", "-", $date);
                        $splitted_min = explode("-", $date);
                        if (count($splitted_min) === 2) {
                            $splitted_min_end = explode(".", $splitted_min[1]);
                            if (count($splitted_min_end) === 3) {
                                if ($splitted_min_end[2] != "") {
                                    $obj->year_end = $splitted_min_end[2];
                                }
                            } else {
                                $splitted_min[1] = (int)$splitted_min[1];
                                if ($splitted_min[1] != "") {
                                    $obj->year_end = $splitted_min[1];
                                }
                            }
                            $obj->year = (int)$splitted_min[0];

                            $date = $splitted_min[1];
                        }
                        if ((int)$date == $date) {
                            $splitted = explode(" ", $date);
                            if (count($splitted) === 1) {
                                $obj->year = (int)$splitted[0];
                            } elseif (count($splitted) === 2) {
                                $obj->year = (int)$splitted[1];
                                $month_names = ["январь" => 1, "февраль" => 2, "март" => 3, "апрель" => 4, "май" => 5, "июнь" => 6, "июль" => 7, "август" => 8, "сентябрь" => 9, "октябрь" => 10, "ноябрь" => 11, "декабрь" => 12];
                                $month = mb_strtolower($splitted[0], "UTF-8");
                                if (isset($month_names[$month])) {
                                    $obj->month = $month_names[$month];
                                }
                            }
                        } else {
                            $date = trim($date);
                            $date = explode(" ", $date)[0];

                            $date = preg_replace('/[^0-9.]+/', '', $date);
                            $obj->date = Carbon::createFromFormat("d.m.Y", $date);
                            $splitted = explode(".", $date);
                            $obj->day = $splitted[0];
                            $obj->month = $splitted[1];
                            $obj->year = $splitted[2];
                        }
                    }
                    $additional_description = trim($matches[4]);
                    echo "Доп.описание: " . $additional_description . PHP_EOL;
                    $obj->short_description = $additional_description;
                }

                if (!in_array($video['ucoz_id'], $ucoz_ids)) {
                    echo "Сохраняем видео";
                    $obj->save();
                    $obj->setSupposedDate();
                } else {
                    echo "Видео уже в базе";
                }
                echo PHP_EOL . PHP_EOL;
            }
        }
    }
}
