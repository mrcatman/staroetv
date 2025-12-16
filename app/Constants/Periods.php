<?php

namespace App\Constants;

use Carbon\Carbon;

class Periods
{
    const LIST = [
        [
            'name' => 'СССР',
            'url' => 'ussr',
            'years' => [1920, 1991]
        ],
        [
            'name' => 'Начало 90-х',
            'url' => '90s-start',
            'years' => [1992, 1995]
        ],
        [
            'name' => 'Конец 90-х',
            'url' => '90s-end',
            'years' => [1996, 2000]
        ],
        [
            'name' => 'Ранние нулевые',
            'url' => '2000s-start',
            'years' => [2001, 2005]
        ],
        [
            'name' => 'Поздние нулевые',
            'url' => '2000s-end',
            'years' => [2006, 2010]
        ],
    ];

    public static function find($url)
    {
        $items = array_values(array_filter(self::LIST, function($period) use ($url) {
            return $period['url'] == $url;
        }));
        return count($items) > 0 ? $items[0] : null;
    }

    public static function getDatesInterval($period)
    {
        $start = Carbon::createFromDate($period['years'][0], 1, 1);
        $end = Carbon::createFromDate($period['years'][1], 12, 31);
        return [$start, $end];
    }
}
