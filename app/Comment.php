<?php

namespace App;
use App\Helpers\BBCodesHelper;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model {

    protected $guarded = [];

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function getCanEditAttribute() {
        if (PermissionsHelper::allows("comedit")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->user_id == $user->id && PermissionsHelper::allows("comoedit");
        }
        return false;
    }

    public function getCanDeleteAttribute() {
        if (PermissionsHelper::allows("comdel")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->user_id == $user->id && PermissionsHelper::allows("comodel");
        }
        return false;
    }

    public function getOriginalTextAttribute() {
        $original = $this->attributes['original_text'];
        if ($original) {
            return $original;
        }
        return BBCodesHelper::HTMLToBB($this->text);
    }

    public function getCreatedAtAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return "";
        }
        return DatesHelper::format($this->attributes['created_at']);
    }


}
