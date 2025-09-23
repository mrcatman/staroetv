<?php

namespace App\Traits;

use App\Models\ChannelName;
use Carbon\Carbon;

trait HasChannel {

    private $_channel_name_data = null;

    public function getChannelNameDataAttribute()
    {
        $name = null;
        if ($this->_channel_name_data) {
            return $this->_channel_name_data;
        }
        if (!$this->year) {
            $this->year = $this->year_start;
        }
        if ($this->date) {
            $name = ChannelName::where(['channel_id' => $this->channel_id])->whereDate('date_start', '<', $this->date)->whereDate('date_end', '>', $this->date)->first();
        }
        if (!$name && $this->year) {
            $year = $this->year;
            if ($this->interprogramPackage) {
                $year = Carbon::parse($this->interprogramPackage->date_end)->year;
            }
            $year_start = Carbon::createFromDate($year, 1, 1);
            $year_end = Carbon::createFromDate($year, 12, 31);
            $name = ChannelName::where(['channel_id' => $this->channel_id])->whereDate('date_start', '<', $year_end)->whereDate('date_end', '>', $year_start)->first();
            if (!$name) {
                $name = ChannelName::where(['channel_id' => $this->channel_id])->whereDate('date_start', '<', $year_end)->whereNull('date_end')->first();
            }
        }
        if ($name) {
            $this->_channel_name_data = $name;
            return $name;
        }
        return null;
    }


    public function getChannelNameAttribute() {
        if ($this->channel_name_data && $this->channel_name_data->name != '') {
            return $this->channel_name_data->name;
        }

        return $this->channel->name;
    }

    public function getChannelLogoAttribute() {
        if ($this->channel_name_data && $this->channel_name_data->logo) {
            return $this->channel_name_data->logo->url;
        }
        if ($this->channel && $this->channel->logo) {
            return $this->channel->logo->url;
        }
        return "/pictures/unknown.png";
    }
}
