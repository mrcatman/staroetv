<?php

namespace App\Console\Commands;

use App\Channel;
use App\ChannelName;
use App\Picture;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PHPHtmlParser\Dom;


class importLogosFromTvpedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logos:import {page} {--dbChannelName=} {--deleteOld=} {--rows=}';

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
        $page = $this->argument('page');
        $rows = $this->option('rows');
        $url = "https://tvpedia.fandom.com/ru/wiki/$page";
        $dom = new Dom();
        $dom->loadFromUrl($url);
        $tables = $dom->find('.collapsible.collapsed');
        $found = false;
        $logos = [];

        $regexps = [
            '/([0-9]{1,2}) ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4}) [0-9]{1,2}:[0-9]{1,2} [-|—] ([0-9]{1,2}) ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4}) [0-9]{1,2}:[0-9]{1,2}/u' => [['day_start', 'month_start', 'year_start', 'day_end', 'month_end', 'year_end'], []],
            '/([0-9]{1,2}) [-|—] ([0-9]{1,2}) ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4})/u' => [['day_start', 'day_end', 'month_start', 'year_start'], ['month_equal' => true, 'year_equal' => true]],
            '/([0-9]{1,2}) ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) [-|—] ([0-9]{1,2}) ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4})/u' => [['day_start', 'month_start', 'day_end', 'month_end', 'year_end'], ['year_equal' => true]],
            '/([0-9]{1,2}) ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4}) [-|—] ([0-9]{1,2}) ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4})/u' => [['day_start', 'month_start', 'year_start', 'day_end', 'month_end', 'year_end']],
            '/([0-9]{1,2}) ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4}) [-|—] настоящее время/u' => [['day_start', 'month_start', 'year_start'], ['now' => true]],
            '/([0-9]{1,2}) ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4}) ([0-9]{1,2}):([0-9]{1,2}) — ([0-9]{1,2}):([0-9]{1,2})/u' => [['day_start', 'month_start', 'year_start'], ['day_equal' => true, 'month_equal' => true, 'year_equal' => true]],
            '/([0-9]{1,2}) ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4})/u' => [['day_start', 'month_start', 'year_start'], ['day_equal' => true, 'month_equal' => true, 'year_equal' => true]],
            '/([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4}) [-|—] ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4})/u' => [['month_start', 'year_start', 'month_end', 'year_end'], ['day_equal' => true, 'month_equal' => true, 'year_equal' => true]],
            '/([0-9]{1,2}) ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) [-|—] ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4})/u' => [['day_start', 'month_start', 'month_end', 'year_end'], ['day_equal' => true, 'month_equal' => true]],
            '/([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) [-|—] ([a-zA-Z\p{Cyrillic}\d\s\-]{1,10}) ([0-9]{1,4})/u' => [['month_start', 'month_end', 'year_start'], ['day_equal' => true, 'year_equal' => true]]
        ];

        foreach ($tables as $table) {
            $tableHeaders = $table->find('th');
            if (count($tableHeaders) > 0 && !$found) {
                $tableHeaderText = $tableHeaders[0]->text();
                if (mb_strpos($tableHeaderText, "История") !== false) {
                    echo "Найден раздел с лого".PHP_EOL;
                    $found = true;
                    $tr = $table->find('tr');
                    $otherTable = false;
                    if (count($tr) === 1) {
                        $otherTable = true;
                        $nextTable = $table->nextSibling();
                        $tr = $nextTable->find('tr');
                        if (trim($nextTable->outerHtml()) == "") {
                            $nextTable = $nextTable->nextSibling();
                            $tr = $nextTable->find('tr');
                        }
                    }
                    $trCount = ceil((count($tr) - 1) / 2);
                    if ($rows) {
                        $rowsData = explode("-", $rows);
                        $start = (int)$rowsData[0];
                        $end = (int)$rowsData[1];
                    } else {
                        $start = 1;
                        $end = $trCount;
                    }
                    $offset = 0;
                    if ($otherTable) {
                        $offset--;
                    }
                    for ($i = $start; $i <= $end; $i++) {
                        $logosRow = $tr[$i * 2 - 1 + $offset];
                        $textsRow = $tr[$i * 2 + $offset];
                        if ($textsRow && $logosRow) {
                            $logosTds = $logosRow->find('td');
                            $textsTds = $textsRow->find('td');
                            $bgColor = $textsTds[0]->getAttribute('bgcolor');
                            $text = $textsTds[0]->text();
                            if ($bgColor !== "#DDDDDD" || mb_strpos($text, "во время") !== false) {
                                $offset++;
                                $textsRow = $tr[$i * 2 + $offset];
                                $textsTds = $textsRow->find('td');
                                $bgColor = $textsTds[0]->getAttribute('bgcolor');
                                $text = $textsTds[0]->text();
                                if ($bgColor !== "#DDDDDD" || mb_strpos($text, "во время") !== false) {
                                    $offset++;
                                    $textsRow = $tr[$i * 2 + $offset];
                                    $textsTds = $textsRow->find('td');
                                }
                            }
                            $j = 0;
                            // var_dump($textsRow->innerHtml());
                            $incrementOffset = false;
                            foreach ($textsTds as $textsTd) {
                                $text = trim($textsTd->text());
                                $img = $logosTds[$j]->find('img')[0];
                                if ($img) {
                                    $logo = $img->getAttribute('data-src');
                                    //echo "Лого: ".$logo." Текст: ".$textsTd->innerHtml().PHP_EOL;
                                    $logos[] = ['logo' => $logo, 'text' => $text];
                                } else {
                                    $incrementOffset = true;
                                }
                                $logoColspan = $logosTds[$j]->getAttribute('colspan');
                                if (!$logoColspan) {
                                    $logoColspan = 1;
                                }
                                $colspan = $textsTd->getAttribute('colspan');
                                if (!$colspan) {
                                    $colspan = 1;
                                }
                                $j += $colspan / $logoColspan;
                            }

                            if ($incrementOffset) {
                                $offset++;
                            }
                        }
                    }
                }
            }
        }
        $logos = array_filter($logos, function($logo) {
            return strlen($logo['text']) > 0;
        });
        echo "Найдено ".count($logos)." лого".PHP_EOL;
        $name = $this->option('dbChannelName');
        if (!$name) {
            $name = $page;
        }
        $channel = Channel::where(['name' => $name])->first();
        if ($channel) {
            $deleteOld = $this->option('deleteOld');
             if ($deleteOld) {
                 Picture::where(['tag' => 'logo', 'channel_id' => $channel->id])->delete();
                ChannelName::where(['channel_id' => $channel->id])->delete();
            }
            foreach ($logos as $logo) {
                $matched = false;
                $matches = [];
                $regexp_data = null;
                $matched_regexp = null;
                $max_matched = 0;
                foreach ($regexps as $regexp => $params) {
                    if (preg_match($regexp, $logo['text'], $temp_matches)) {
                        $matched = true;
                        if (count($params[0]) > $max_matched) {
                            $max_matched = count($params[0]);
                            $matched_regexp = $regexp;
                            $matches = $temp_matches;
                            $regexp_data = $params;
                        }
                    }
                }
                $fields = $regexp_data[0];
                if (!$matched) {
                    // echo $logo['text'].PHP_EOL;
                }
                $date_start = null;
                $date_end = null;
                $day_start = null;
                $month_start = null;
                $year_start = null;
                $day_end = null;
                $month_end = null;
                $year_end = null;
                $i = 1;
                $month_names = ["января" => 1, "февраля" => 2, "марта" => 3, "апреля" => 4, "мая" => 5, "июня" => 6, "июля" => 7, "августа" => 8, "сентября" => 9, "октября" => 10, "ноября" => 11, "декабря" => 12];
                $month_names_p = ["январь" => 1, "февраль" => 2, "март" => 3, "апрель" => 4, "май" => 5, "июнь" => 6, "июль" => 7, "август" => 8, "сентябрь" => 9, "октябрь" => 10, "ноябрь" => 11, "декабрь" => 12];

                if ($matched) {
                    foreach ($fields as $field) {
                        $value = $matches[$i];
                        if ((int)$value === 0) {
                            if (isset($month_names[$value])) {
                                $value = $month_names[$value];
                            }
                            if (isset($month_names_p[$value])) {
                                $value = $month_names_p[$value];
                            }
                        }
                        if ($field == "day_start") {
                            $day_start = $value;
                        }
                        if ($field == "month_start") {
                            $month_start = $value;
                        }
                        if ($field == "year_start") {
                            $year_start = $value;
                        }
                        if ($field == "day_end") {
                            $day_end = $value;
                        }
                        if ($field == "month_end") {
                            $month_end = $value;
                        }
                        if ($field == "year_end") {
                            $year_end = $value;
                        }
                        $i++;
                    }
                    echo $logo['text']." | $year_start, $month_start, $day_start - $year_end, $month_end, $day_end".PHP_EOL;
                    $settings = isset($regexp_data[1]) ? $regexp_data[1] : [];
                    if (isset($settings['year_equal']) && $settings['year_equal']) {
                        if ($year_start) {
                            $year_end = $year_start;
                        } else {
                            $year_start = $year_end;
                        }
                    }
                    if (isset($settings['month_equal']) && $settings['month_equal']) {
                        if ($month_start) {
                            $month_end = $month_start;
                        } else {
                            $month_start = $month_end;
                        }
                    }
                    if (isset($settings['day_equal']) && $settings['day_equal']) {
                        if ($day_start) {
                            $day_end = $day_start;
                        } elseif ($day_end) {
                            $day_start = $day_end;
                        } else {
                            $day_start = 1;
                            $day_end = 1;
                        }
                    }
                    echo $logo['text']." | $year_start, $month_start, $day_start - $year_end, $month_end, $day_end".PHP_EOL;
                    if ($year_start && $month_start && $day_start) {
                        $date_start = Carbon::createFromDate($year_start, $month_start, $day_start);
                    }
                    if ($year_end && $month_end && $day_end) {
                        $date_end = Carbon::createFromDate($year_end, $month_end, $day_end);
                    }
                    $picture = new Picture([
                        'tag' => 'logo',
                        'channel_id' => $channel->id
                    ]);
                    $url = $logo['logo'];
                    if (strpos($url, "scale-to-width") === false) {

                    } else {
                        $url = explode("scale-to-width-down", $url)[0] . "scale-to-width-down/640?cb" . explode("?cb", $url)[1];
                    }
                    $url = str_replace("&amp;", "&", $url);
                    if (!file_exists(public_path("pictures/logos/" . $channel->id))) {
                        mkdir(public_path("pictures/logos/" . $channel->id));
                    }
                    $picture->loadFromURL($url, md5($url), true, "logos/" . $channel->id);
                    $picture->save();
                    $name = new ChannelName([
                        'channel_id' => $channel->id,
                        'name' => '',
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'logo_id' => $picture->id
                    ]);
                    $name->save();
                } else {
                    echo $logo['text'].PHP_EOL;
                }
            }
            if (!$found) {
                echo "Не найдена таблица с лого" . PHP_EOL;
            }
        } else {
            echo "Не найден канал в базе".PHP_EOL;
        }
    }
}
