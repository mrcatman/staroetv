<?php

namespace App;

use App\Helpers\BBCodesHelper;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    const GROUP_ID_MODERATOR = 1;
    const GROUP_ID_ACTIVIST = 8;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function avatar() {
        return $this->hasOne('App\Picture', 'id', 'avatar_id');
    }

    public function meta() {
        return $this->hasOne('App\UserMeta');
    }

    public function reputation() {
        return $this->hasMany('App\UserReputation', 'to_id', 'id')->orderBy('id', 'desc');
    }


    public function awards() {
        return $this->hasMany('App\UserAward', 'to_id', 'id')->orderBy('id', 'desc');
    }

    public function warnings() {
        return $this->hasMany('App\UserWarning', 'to_id', 'id')->orderBy('id', 'desc');
    }

    public function forum_messages() {
        return $this->hasMany('App\ForumMessage', 'user_id', 'id')->orderBy('id', 'desc');
    }


    public function getReputationNumberAttribute() {
        return $this->reputation->sum('weight');
    }

    public function getBanLevelAttribute() {
        return $this->warnings->sum('weight') * 100 / 5;
    }


    public function getUrlAttribute() {
        return "/index/8-".$this->id;
    }

    public function comments() {
        return $this->hasMany('App\Comment', 'user_id', 'id')->orderBy('id', 'desc');
    }


    public function videos() {
        return $this->hasMany('App\Record', 'author_username', 'username')->orderBy('id', 'desc');
    }

    public function getGroupIconAttribute() {
        return $this->group->icon;
    }

    public function group() {
        return $this->belongsTo('App\UserGroup', 'group_id', 'id');
    }

    public function getCanChangeReputationAttribute() {
        $user = auth()->user();
        if ($user) {
            return $this->id != $user->id && PermissionsHelper::allows("dorep");
        }
        return false;
    }


    public function getCreatedAtAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return "";
        }
        return DatesHelper::format($this->attributes['created_at']);
    }


    public function getWasOnlineAttribute() {
        if (!isset($this->attributes['was_online'])) {
            return "";
        }
        return DatesHelper::format($this->attributes['was_online']);
    }

    public function getSignatureOriginalAttribute() {
        return BBCodesHelper::HTMLToBB($this->signature);
    }

}
