<?php

namespace App;
use App\Helpers\DatesHelper;
use Illuminate\Database\Eloquent\Model;

class PrivateMessage extends Model {

    protected $guarded = [];

    public function getCreatedAtAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return "";
        }
        return DatesHelper::format($this->attributes['created_at']);
    }

    public function getIsUnreadAttribute() {
       if ($this->is_group) {
            return strpos($this->read_ids, auth()->user()->id.",") === false;
        } else {
            return !$this->is_read;
        }
    }

    public function getIsOutAttribute() {
        return $this->from_id == auth()->user()->id;
    }

}
