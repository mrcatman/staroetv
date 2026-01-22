<?php

namespace App\Models;

use App\Constants\CacheTimes;
use App\Helpers\BBCodesHelper;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use App\Mail\ResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable
{
    use Notifiable;


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
        'password', 'remember_token', 'ip_address', 'ip_address_reg', 'verify_code', 'last_page_seen', 'email'
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
        return $this->hasOne('App\Models\Picture', 'id', 'avatar_id');
    }

    public function meta() {
        return $this->hasOne('App\Models\UserMeta');
    }

    public function reputation() {
        return $this->hasMany('App\Models\UserReputation', 'to_id', 'id')->orderBy('id', 'desc');
    }


    public function awards() {
        return $this->hasMany('App\Models\UserAward', 'to_id', 'id')->orderBy('id', 'desc');
    }

    public function warnings() {
        return $this->hasMany('App\Models\UserWarning', 'to_id', 'id')->orderBy('id', 'desc');
    }

    public function forum_messages() {
        return $this->hasMany('App\Models\ForumMessage', 'user_id', 'id')->orderBy('id', 'desc');
    }

    public function getReputationNumberAttribute() {
        return $this->reputation->sum('weight');
    }

    public function getBanLevelAttribute() {
        $level = $this->warnings->sum('weight') * 100 / 5;
        if ($level < 0) {
            $level = 0;
        } elseif ($level > 100) {
            $level = 100;
        }
        return $level;
    }


    public function getUrlAttribute() {
        return route('users.show', $this);
    }

    public function comments() {
        return $this->hasMany('App\Models\Comment', 'user_id', 'id')->orderBy('id', 'desc');
    }


    public function videos() {
        return $this->hasMany('App\Models\Record', 'author_username', 'username')->orderBy('id', 'desc');
    }

    public function getGroupIconAttribute() {
        if ($this->group->icon_svg_code) {
            return "<div class='group-icon group-icon--".$this->group->id." group-icon--svg'>".$this->group->icon_svg_code."</div>";
        } else {
            return "<div class='group-icon group-icon--".$this->group->id."'><img src='".$this->group->icon."' /></div>";
        }
    }

    public function group() {
        return $this->belongsTo('App\Models\UserGroup', 'group_id', 'id');
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

    public function getCreatedAtOrigAttribute() {
        return $this->attributes['created_at'];
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

    public function unreadMessages() {
        $user = $this;
        $messages_in = PrivateMessage::where(['to_id' => $user->id, 'is_deleted_receiver' => false, 'is_read' => false])->get();
        $messages_group = PrivateMessage::where(['is_group' => true])->where('group_ids', 'like', "%".$user->group_id.",%")->where(function($q) use($user) {
            $q->whereNull('deleted_ids');
            $q->orWhere('deleted_ids', 'not like', "%".$user->id.",%");
        })->where('read_ids', 'not like', "%".$user->id.",%")->get();
        return count($messages_in) + count($messages_group);
    }

    public function sendPasswordResetNotification($token){
        $url = route('password.reset', ['token' => $token, 'email' => $this->email]);
        Mail::to($this)->send(new ResetPassword($this, $url));
    }

    public function getForumMessagesCountAttribute() {
        return Cache::remember('forum_messages'.$this->id, CacheTimes::RELATION, function () {
            return ForumMessage::where(['user_id' => $this->id])->count();
        });
    }

}
