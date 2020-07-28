<?php

namespace App\Console\Commands;

use App\API\UcozAPI;
use App\Channel;
use App\Helpers\CSVHelper;
use App\Program;
use App\Record;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportRadio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'radio:import';

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
        $records = CSVHelper::transform(public_path("data/dir.csv"), [
            'ucoz_id', '', '', '', '', 'created_at', '', '', '', '', '', '', '', 'views', '', '', 'title', '', '', '', '', '', '', 'author_username', '', '', '', 'source', 'cover', '', '', '', 'embed_code', '', '', 'author_id', '', '', 'url', 'updated_at'
        ], false);

        $channels = Channel::pluck('id', 'name');
        $ucoz_ids = Record::pluck('ucoz_id')->where(['is_radio' => false])->toArray();
        $programs_data = Program::all();
        $programs = [];
        foreach ($programs_data as $program) {
            if (!isset($programs[$program->channel_id])) {
                $programs[$program->channel_id] = [];
            }
            $programs[$program->channel_id][$program->name] = $program->id;
        }

        $interprogram_keys = ["анонс","вещани","реклам","заставк","ролик","программа передач","погод","эфира","спонсор", "отбивк", "джингл", "начало часа"];

        foreach ($records as $record) {
            unset($record['']);
            $created_at = $record['created_at'];
            unset($record['created_at']);
            $record_obj = new Record($record);
            $record_obj->is_radio = true;
            $record_obj->created_at = Carbon::createFromTimestamp($created_at);
            preg_match('/(.*?)\((.*?), (.*?)\)(.*)/', $record_obj->title, $matches);
            if (count($matches) < 3) {
                echo "Нераспознанное название: ".$record_obj->title.PHP_EOL;
                preg_match('/(.*?)\((.*?)\)(.*)/', $record_obj->title, $matches);
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
                        'name' => $channel,
                        'is_radio' => true
                    ]);
                    $channel_item->save();
                    $channels[$channel] = $channel_item->id;
                    $channel_id = $channel_item->id;
                } else {
                    $channel_id = $channels[$channel];
                }
                $record_obj->channel_id = $channel_id;

                $is_interprogram = false;
                $program_lower = mb_strtolower($program, "UTF-8");

                foreach ($interprogram_keys as $interprogram_key) {
                    if (mb_strpos($program_lower, $interprogram_key, null, "UTF-8") !== false) {
                        $is_interprogram = true;
                    }
                }
                if (mb_strpos($program_lower, "реклам", null, "UTF-8") !== false) {
                    $record_obj->is_advertising = true;
                }
                $cover = $record_obj->cover;
                echo "Межпрограммка: " . ($is_interprogram ? "Да" : "Нет") . PHP_EOL;
                $record_obj->is_interprogram = $is_interprogram;
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
                    $record_obj->program_id = $program_id;
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
                                $record_obj->year_end = $splitted_min_end[2];
                            }
                        } else {
                            $splitted_min[1] = (int)$splitted_min[1];
                            if ($splitted_min[1] != "") {
                                $record_obj->year_end = $splitted_min[1];
                            }
                        }
                        $record_obj->year = (int)$splitted_min[0];

                        $date = $splitted_min[1];
                    }
                    if ((int)$date == $date) {
                        $splitted = explode(" ", $date);
                        if (count($splitted) === 1) {
                            $record_obj->year = (int)$splitted[0];
                        } elseif (count($splitted) === 2) {
                            $record_obj->year = (int)$splitted[1];
                            $month_names = ["январь" => 1, "февраль" => 2, "март" => 3, "апрель" => 4, "май" => 5, "июнь" => 6, "июль" => 7, "август" => 8, "сентябрь" => 9, "октябрь" => 10, "ноябрь" => 11, "декабрь" => 12];
                            $month = mb_strtolower($splitted[0], "UTF-8");
                            if (isset($month_names[$month])) {
                                $record_obj->month = $month_names[$month];
                            }
                        }
                    } else {
                        $date = trim($date);
                        $date = explode(" ", $date)[0];
                        $date = preg_replace('/[^0-9.]+/', '', $date);
                        $record_obj->date = Carbon::createFromFormat("d.m.Y", $date);
                        $splitted = explode(".", $date);
                        $record_obj->day = $splitted[0];
                        $record_obj->month = $splitted[1];
                        $record_obj->year = $splitted[2];
                    }
                }
                $additional_description = trim($matches[4]);
                echo "Доп.описание: " . $additional_description . PHP_EOL;
                $record_obj->short_description = $additional_description;
                if (!in_array($record_obj->id, $ucoz_ids)) {
                    echo "Сохраняем радиозапись";
                    $record_obj->save();
                } else {
                    DB::table('videos')->where(['ucoz_id' => $record_obj->ucoz_id])->update($record_obj->toArray());
                    echo "Радиозапись уже в базе";
                }
                echo PHP_EOL.PHP_EOL;
            }
        }

    }
}
