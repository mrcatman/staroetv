<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model {

    protected $guarded = [];
    public $timestamps = false;

    public function variants() {
        return $this->hasMany(QuestionnaireVariant::class, 'questionnaire_id', 'id');
    }

    public function getTotalAnswersAttribute() {
        $total = 0;
        foreach ($this->variants as $variant) {
            $total+= $variant->answers_count;
        }
        return $total;
    }
}
