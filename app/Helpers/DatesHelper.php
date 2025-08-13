<?php
namespace App\Helpers;

use Carbon\Carbon;

class DatesHelper {

    public static function monthNames()
    {
        return ["Январь","Февраль","Март","Апрель","Май","Июнь","Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь"];
    }

    public static function monthNamesParentalCase()
    {
        return ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"];
    }

    public static function format($date) {
        $ts = strtotime($date);
        return self::formatTS($ts);
    }

    public static function formatTS($ts) {
        $ts = $ts + 3 * 3600;
        $month_index = (int)date("m", $ts) - 1;
        $month = self::monthNamesParentalCase()[$month_index];
        return date("d", $ts)." ".$month." ".date("Y, H:i", $ts);
    }

    public static function guess($date)
    {
        $date = trim($date);
        $date = explode(";", $date)[0];
        $date = str_replace("–", "-", $date);
        $splitted_min = explode("-", $date);

        $data = [];

        if (count($splitted_min) === 2) {
            $splitted_min_end = explode(".", $splitted_min[1]);
            if (count($splitted_min_end) === 3) {
                if ($splitted_min_end[2] != "") {
                    $data['year_end'] = $splitted_min_end[2];
                }
            } else {
                $splitted_min[1] = (int)$splitted_min[1];
                if ($splitted_min[1] != "") {
                    $data['year_end'] = $splitted_min[1];
                }
            }
            $data['year'] = (int)$splitted_min[0];

            $date = $splitted_min[1];
        }
        if ((int)$date == $date) {
            $splitted = explode(" ", $date);
            if (count($splitted) === 1) {
                $data['year'] = (int)$splitted[0];
            } elseif (count($splitted) === 2) {
                $data['year'] = (int)$splitted[1];
                $month_names = self::monthNames();
                $month = mb_strtolower($splitted[0], "UTF-8");
                if (in_array($month, $month_names)) {
                    $data['month'] = array_search($month, $month_names) + 1;
                }
            }
        } else {
            $date = trim($date);
            $date = explode(" ", $date)[0];

            $date = preg_replace('/[^0-9.]+/', '', $date);
            $data['date'] = Carbon::createFromFormat("d.m.Y", $date);
            $splitted = explode(".", $date);
            $data['day'] = $splitted[0];
            $data['month'] = $splitted[1];
            $data['year'] = $splitted[2];
        }
        return $data;
    }


}
