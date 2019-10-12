<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model {

    public $table = "users_meta";
    protected $guarded = [];
    public $timestamps = false;

    public function getDateOfBirthTsAttribute(){
        $date_of_birth = $this->attributes['date_of_birth'];
        $date = strtotime($date_of_birth) * 1000;
        return $date;
    }
}
