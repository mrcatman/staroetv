<?php

namespace App\Constants;

use App\Models\Article;
use App\Models\Channel;
use App\Models\HistoryEvent;
use App\Models\InterprogramPackage;
use App\Models\Program;
use App\Models\Record;
use App\Models\Teletext;

class MaterialTypes
{
    const LIST = [
        Channel::TYPE_CHANNELS => Channel::class,
        Article::TYPE_ARTICLES => Article::class,
        Article::TYPE_NEWS => Article::class,
        Article::TYPE_BLOG => Article::class,
        Record::TYPE_VIDEOS => Record::class,
        Teletext::TYPE_TELETEXT => Teletext::class,
        Program::TYPE_PROGRAMS => Program::class,
        InterprogramPackage::TYPE_INTERPROGRAM => InterprogramPackage::class,
        HistoryEvent::TYPE_HISTORY_EVENT => HistoryEvent::class,
    ];


}
