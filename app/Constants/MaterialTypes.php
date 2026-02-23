<?php

namespace App\Constants;

use App\Models\Article;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\Forum;
use App\Models\ForumMessage;
use App\Models\ForumTopic;
use App\Models\Genre;
use App\Models\HistoryEvent;
use App\Models\DesignPackage;
use App\Models\Page;
use App\Models\Program;
use App\Models\Record;
use App\Models\Smile;
use App\Models\Teletext;
use App\Models\User;
use App\Models\UserAward;
use App\Models\UserReputation;
use App\Models\UserWarning;
use App\Models\VideoCut;

class MaterialTypes
{

    const TYPE_ARTICLES = 1;
    const TYPE_NEWS = 2;
    const TYPE_BLOG = 8;

    const TYPE_RECORDS = 10;
    const TYPE_TELETEXT = 11;

    const TYPE_USERS = 12;
    const TYPE_AWARDS = 13;
    const TYPE_REPUTATION = 14;

    const TYPE_WARNINGS = 15;

    const TYPE_COMMENTS = 16;
    const TYPE_FORUM_TOPICS = 17;

    const TYPE_FORUMS = 18;
    const TYPE_FORUM_MESSAGES = 19;
    const TYPE_SMILES = 20;
    const TYPE_PAGES = 21;

    const TYPE_CHANNELS = 100;
    const TYPE_PROGRAMS = 101;
    const TYPE_INTERPROGRAM = 102;
    const TYPE_HISTORY_EVENTS = 103;

    const TYPE_GENRES = 104;

    const TYPE_VIDEO_CUTS = 105;

    const LIST = [
        self::TYPE_CHANNELS => Channel::class,
        self::TYPE_ARTICLES => Article::class,
        self::TYPE_NEWS => Article::class,
        self::TYPE_BLOG => Article::class,
        self::TYPE_RECORDS => Record::class,
        self::TYPE_TELETEXT => Teletext::class,
        self::TYPE_PROGRAMS => Program::class,
        self::TYPE_INTERPROGRAM => DesignPackage::class,
        self::TYPE_HISTORY_EVENTS => HistoryEvent::class,
        self::TYPE_USERS => User::class,
        self::TYPE_AWARDS => UserAward::class,
        self::TYPE_REPUTATION => UserReputation::class,
        self::TYPE_WARNINGS => UserWarning::class,
        self::TYPE_COMMENTS => Comment::class,
        self::TYPE_SMILES => Smile::class,
        self::TYPE_PAGES => Page::class,
        self::TYPE_FORUM_TOPICS => ForumTopic::class,
        self::TYPE_FORUMS => Forum::class,
        self::TYPE_FORUM_MESSAGES => ForumMessage::class,
        self::TYPE_GENRES => Genre::class,
        self::TYPE_VIDEO_CUTS => VideoCut::class,
    ];


}
