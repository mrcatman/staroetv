<?php
namespace App\Helpers;

class DatesHelper {

    public static function format($date) {
        $ts = strtotime($date);
        return self::formatTS($ts);
    }

    public static function formatTS($ts) {
        $month_index = (int)date("m", $ts) - 1;
        $month_names = ["Января","Февраля","Марта","Апреля","Мая","Июня","Июля","Августа","Сентября","Октября","Ноября","Декабря"];
        $month = $month_names[$month_index];
        return date("d", $ts)." ".$month." ".date("Y, H:i", $ts);
    }


}