<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class ArticleBinding extends Model {

    public $table = "articles_bindings";
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = ['name'];

    public function article() {
        return $this->belongsTo(Article::class);
    }

    public function channel() {
        return $this->belongsTo(Channel::class);
    }

    public function program() {
        return $this->belongsTo(Program::class);
    }

    public function getNameAttribute() {
        if ($this->channel_id) {
            return $this->channel ? $this->channel->name : "-";
        } else {
            return $this->program ? $this->program->name : "-";
        }
    }
}
