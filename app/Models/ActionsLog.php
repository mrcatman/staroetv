<?php

namespace App\Models;
use App\Constants\Actions;
use App\Constants\MaterialTypes;
use Illuminate\Database\Eloquent\Model;

class ActionsLog extends Model {

    protected $guarded = [];
    protected $table = 'actions_log';

    protected $casts = [
        'changes' => 'array',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getActionNameAttribute()
    {
        return match ($this->action) {
            Actions::Create => 'добавил(а)',
            Actions::Update => 'изменил(а)',
            Actions::Delete => 'удалил(а)',
            Actions::Merge => 'объединил(а)',
            default => '???',
        };
    }

    public function getMaterialAttribute() {
        $class = MaterialTypes::LIST[$this->material_type];
        return $class::find($this->material_id);
    }

    public function getMaterialNameAttribute()
    {
        $material = $this->material;
        return match ((int)$this->material_type) {
            MaterialTypes::TYPE_ARTICLES,
            MaterialTypes::TYPE_RECORDS,
            MaterialTypes::TYPE_BLOG,
            MaterialTypes::TYPE_NEWS,
            MaterialTypes::TYPE_TELETEXT,
            MaterialTypes::TYPE_HISTORY_EVENTS,
            MaterialTypes::TYPE_PAGES,
            MaterialTypes::TYPE_FORUM_TOPICS,
            MaterialTypes::TYPE_FORUMS => $material->title,
            MaterialTypes::TYPE_USERS => $material->username,
            MaterialTypes::TYPE_AWARDS, MaterialTypes::TYPE_REPUTATION, MaterialTypes::TYPE_WARNINGS => $material->comment,
            MaterialTypes::TYPE_COMMENTS, MaterialTypes::TYPE_SMILES => $material->text,
            MaterialTypes::TYPE_FORUM_MESSAGES => $material->content,
            MaterialTypes::TYPE_VIDEO_CUTS => $material->video ? $material->video->title : '-',
            default => $material->name,
        };
    }

    public function getMaterialTypeNameAttribute()
    {
        return match ((int)$this->material_type) {
            MaterialTypes::TYPE_CHANNELS => 'канал',
            MaterialTypes::TYPE_ARTICLES => 'статью',
            MaterialTypes::TYPE_NEWS => 'новость',
            MaterialTypes::TYPE_BLOG => 'блог',
            MaterialTypes::TYPE_RECORDS => 'запись',
            MaterialTypes::TYPE_TELETEXT => 'телетекст',
            MaterialTypes::TYPE_PROGRAMS => 'программу',
            MaterialTypes::TYPE_INTERPROGRAM => 'пакет оформления',
            MaterialTypes::TYPE_HISTORY_EVENTS => 'подборку записей',
            MaterialTypes::TYPE_USERS => 'пользователя',
            MaterialTypes::TYPE_AWARDS => 'награду',
            MaterialTypes::TYPE_REPUTATION => 'репутацию',
            MaterialTypes::TYPE_WARNINGS => 'замечание',
            MaterialTypes::TYPE_COMMENTS => 'коммент',
            MaterialTypes::TYPE_SMILES => 'смайл',
            MaterialTypes::TYPE_PAGES => 'страницу',
            MaterialTypes::TYPE_FORUM_TOPICS => 'тему на форуме',
            MaterialTypes::TYPE_FORUMS => 'форум',
            MaterialTypes::TYPE_FORUM_MESSAGES => 'сообщение на форуме',
            MaterialTypes::TYPE_VIDEO_CUTS => 'обрезку',
            default => 'неизвестно что',
        };
    }


}
