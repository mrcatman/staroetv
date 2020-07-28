<?php

namespace App\Console\Commands;

use App\API\UcozAPI;
use App\Channel;
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
    public function handle()
    {
        $api = new UcozAPI();
        $start_page = 1;
        $end_page = 120;
        $ucoz_domain = "http://s67.ucoz.net";

        $interprogram_keys = ["анонс","вещани","реклам","заставк","ролик","программа передач","погод","эфира","спонсор", "часы"];

        $programs_data = Program::all();
        $programs = [];
        foreach ($programs_data as $program) {
            if (!isset($programs[$program->channel_id])) {
                $programs[$program->channel_id] = [];
            }
            $programs[$program->channel_id][$program->name] = $program->id;
        }

        $channels = Channel::pluck('id', 'name');
        $ucoz_ids = Record::pluck('ucoz_id')->where(['is_radio' => false])->toArray();

        for ($i = $start_page; $i <= $end_page; $i++) {
            $videos = $api->getVideos($i);
            echo "Страница: ".$i.PHP_EOL;
            foreach ($videos as $video) {
                $cover = str_replace($ucoz_domain, "", $video->screenshot);
                $obj = new Record([
                    'is_from_ucoz' => true,
                    'original_added_at' => Carbon::createFromTimestamp($video->add_date_ts),
                    'author_username' => $video->author,
                    'title' => $video->title,
                    'description' => $video->description,
                    'embed_code' => $video->embobject,
                    'views' => $video->reads,
                    'cover' => $cover,
                    'ucoz_id' => $video->id,
                    'ucoz_url' => $video->entry_url
                ]);
                preg_match('/(.*?)\((.*?), (.*?)\)(.*)/', $video->title, $matches);
                if (count($matches) < 3) {
                    echo "Нераспознанное название: ".$video->title.PHP_EOL;
                    preg_match('/(.*?)\((.*?)\)(.*)/', $video->title, $matches);
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
                                'cover' => $cover
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
                        $date = str_replace("–","-",$date);
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
                            $date = explode(" ",$date)[0];

                            $date = preg_replace('/[^0-9.]+/', '', $date);
                            $obj->date = Carbon::createFromFormat("d.m.Y", $date);
                            $splitted = explode(".", $date);
                            $obj->day = $splitted[0];
                            $obj->month = $splitted[1];
                            $obj->year = $splitted[2];
                        }
                    }
                    $additional_description = trim($matches[4]);
                    echo "Доп.описание: ".$additional_description.PHP_EOL;
                    $obj->short_description = $additional_description;
                }

                if (!in_array($video->id, $ucoz_ids)) {
                    echo "Сохраняем видео";
                    $obj->save();
                } else {
                    DB::table('videos')->where(['ucoz_id' => $video->id])->update($obj->toArray());
                    echo "Видео уже в базе";
                }
                echo PHP_EOL.PHP_EOL;
            }
            echo "Страница: ".$i.PHP_EOL;
            sleep(1);
        }
    }
}
