<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireVariant extends Model {

    public $table = "questionnaires_variants";
    protected $guarded = [];
    public $timestamps = false;

    public function answers() {
        return $this->hasMany(QuestionnaireAnswer::class, 'variant_id', 'id');
    }

    public function getAnswersCountAttribute() {
        return count($this->answers) + $this->initial_count;
    }
}
