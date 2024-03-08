<?php

namespace App;
use App\Crossposting\CrossposterManager;
use Illuminate\Database\Eloquent\Model;

class SocialPostConnection extends Model {

    public $table = "crossposting_posts";
    protected $guarded = [];

    protected $appends = ['links'];
    protected $casts = ['last_data' => 'array', 'media_data' => 'array'];

    public function getLinksAttribute() {
        if (!$this->post_ids || $this->post_ids == "") {
            return [];
        }
        $resolver = new CrossposterManager();
        $crossposter = $resolver->get($this->service);
        if ($crossposter) {
            $links = $crossposter->makeLinks($this->post_ids);
            return $links;
        }
        return [];
    }
}
