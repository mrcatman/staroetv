<?php

namespace App\Models;
use App\Constants\RecordComplaintTypes;
use Illuminate\Database\Eloquent\Model;

class RecordComplaint extends Model {

    public $table = 'records_complaints';

    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function record() {
        return $this->belongsTo(Record::class);
    }

    public function getTypeTextAttribute() {
        switch ($this->type) {
            case RecordComplaintTypes::PlayerNotWorking->value:
                return 'Не работает плеер';
                break;
            case RecordComplaintTypes::CopyrightIssues->value:
                return 'Авторские права';
                break;
            case RecordComplaintTypes::Other->value:
                return 'Другое';
                break;
        }
        return '???';
    }

}
