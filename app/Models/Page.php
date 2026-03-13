<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Page extends Model {

    public $table = "static_pages";
    protected $guarded = [];

    public function getContentAttribute() {
        $content = $this->attributes['content'];
        $content = str_replace("\ ", "", $content);
        return $content;
    }

    public function getFixedContentAttribute() {
         $content = str_replace('social-links', view('blocks.global.social')->render(), $this->content);
         $content = preg_replace_callback(
             '/team\|\d+/',
             function ($matches) {
                $group_id = explode("|", $matches[0])[1];
                $users = User::where(['group_id' => $group_id])->get();
                return view('blocks.global.group-users-list', ['users' => $users]);
             }, $content
         );
         return $content;
    }

    public function getFullUrlAttribute() {
        return route('pages.show', $this);
    }
}
