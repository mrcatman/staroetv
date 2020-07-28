<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class HistoryEventBlock extends Model {

    protected $guarded = [];
    protected $table = "events_videos";
    protected $casts = [
        'video_ids' => 'array'
    ];

    public function loadRecords() {
        if ($this->records) {
            return;
        }
        $video_ids = [];
        $descriptions = [];
        $video_data = $this->video_ids;
        foreach ($video_data as $item) {
            if (is_array($item) && isset($item['id'])) {
                $descriptions[] = isset($item['description']) ? $item['description'] : "";
                $video_ids[] = $item['id'];
            } else {
                $descriptions[] = "";
                $video_ids[] = $item;
            }
        }
        $records = Record::whereIn('id', $video_ids)->get();
        $records = $records->sortBy(function($model) {
            return array_search($model->getKey(), $this->video_ids);
        });
        $records = $records->values();
        $index = 0;
        foreach ($records as $record) {
            $record->block_description = $descriptions[$index];
            $index++;
        }
        $this->records = $records;
    }
}
