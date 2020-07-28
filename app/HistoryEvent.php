<?php

namespace App;
use App\Helpers\PermissionsHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HistoryEvent extends Model {

    protected $guarded = [];
    protected $table = "events";

    const TYPE_HISTORY_EVENT = 102;

    public function getCanEditAttribute() {
        if (PermissionsHelper::allows("history")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->user_id == $user->id && PermissionsHelper::allows("historyown");
        }
        return false;
    }

    public function blocks() {
        return $this->hasMany(HistoryEventBlock::class, 'event_id', 'id');
    }

    public function coverPicture() {
        return $this->hasOne('App\Picture', 'id', 'cover_id');
    }

    public function getDateFormattedAttribute() {
        return Carbon::parse($this->date)->format('d.m.Y');
    }

    public function getFullUrlAttribute() {
        return $this->url != "" ? "/events/".$this->url : "/events/".$this->id;
    }

    public function scopeApproved($query) {
        if (!PermissionsHelper::allows('viapprove')) {
            $query->where(['pending' => false]);
        }
        return $query;
    }
}
