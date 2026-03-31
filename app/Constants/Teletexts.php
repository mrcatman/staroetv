<?php

namespace App\Constants;

class Teletexts
{
    public const LIST = [
        [
            'name' => 'Телеинф',
            'url' => 'teleinf',
            'channels' => [
                ['url' => 'ort', 'date_start' => [1, 1, 1991], 'date_end' => [31, 12, 2003]],
            ]
        ],
        [
            'name' => 'Телетекст Первого канала',
            'url' => 'channel-one',
            'channels' => [
                ['url' => 'ort', 'date_start' => [1, 1, 2004], 'date_end' => [1, 1, 2020]],
            ]
        ],
        [
            'name' => 'Р-Тел',
            'url' => 'r-tel',
            'channels' => [
                ['url' => 'rtr']
            ]
        ],
        [
            'name' => 'Блиц-Текст',
            'url' => 'blitz-text',
            'channels' => [
                ['url' => 'ntv']
            ]
        ],
        [
            'name' => 'Центр-Инфо',
            'url' => 'center-info',
            'channels' => [
                ['url' => 'tv-center']
            ]
        ],
        [
            'name' => 'Мостекст',
            'url' => 'mostext',
            'channels' => [
                ['url' => 'mtk'],
                ['url' => '2x2'],
            ]
        ],
        [
            'name' => 'ТелЕк',
            'url' => 'tel-ek',
            'channels' => [
                ['url' => '4-kanal'],
            ]
        ],
    ];

}
