<?php

namespace App\Http\Controllers;

use App\Award;
use App\Channel;
use App\ChannelName;
use App\Helpers\PermissionsHelper;
use App\Helpers\ViewsHelper;
use App\InterprogramPackage;
use App\Record;
use App\User;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Validation\Rules\In;


class InterprogramPackagesController extends Controller {

    public function show($channel_url, $package_url) {
        $channel = Channel::where(['url' => $channel_url])->orWhere(['id' => $channel_url])->first();
        if (!$channel) {
            return redirect("/");
        }
        $base_link = null;
        $related = null;
        $hide_commercials = false;
        if ($package_url == "other") {
            $types_to_hide =  [11, 22];
            $hide_commercials = request()->input('hide_commercials', true);
            $base_link = $channel->full_url."/graphics/other";
            $is_other = true;
            $conditions = [
                'channel_id' => $channel->id,
                'is_interprogram' => true,
                'is_selected' => false,
            ];
            if ($hide_commercials) {
                $conditions['interprogram_type_not_in'] = $types_to_hide;
            }
            $package = new InterprogramPackage([
                'id' => 0,
                'name' => 'Прочее',
                'pictures' => [],
                'years_range' => '',
                'channel_id' => $channel->id,
                'url' => 'other',
            ]);

            $related = [];
           // $related =  InterprogramPackage::where(['channel_id' => $channel->id])->inRandomOrder()->limit(5)->get();
        } else {
            $is_other = false;
            $package = InterprogramPackage::where(['channel_id' => $channel->id])->where(function ($q) use ($package_url) {
                $q->where(['id' => $package_url]);
                $q->orWhere(['url' => $package_url]);
            })->first();
            $conditions = [
                'channel_id' => $channel->id,
                'is_interprogram' => true,
                'interprogram_package_id' => $package->id,
            ];
            ViewsHelper::increment($package, 'interprogram');
            $related = InterprogramPackage::where(['channel_id' => $channel->id])->where('id', '!=', $package->id)->inRandomOrder()->limit(5)->get();
        }
        if (!$package) {
            return redirect($channel->full_url);
        }

        return view('pages.graphics.show', [
            'hide_commercials' => $hide_commercials,
            'base_link' => $base_link,
            'related' => $related,
            'records_conditions' => $conditions,
            'other' => $is_other,
            'channel' => $channel,
            'package' => $package
        ]);
    }


    public function add($channel_id) {
        if (!PermissionsHelper::allows('additionalown') && !PermissionsHelper::allows('additional')) {
            return view("pages.errors.403");
        }
        $channel = Channel::findByIdOrUrl($channel_id);
        if (!$channel) {
            return redirect("/");
        }
        if (!$channel->can_edit) {
            return view("pages.errors.403");
        }
        return view("pages.forms.interprogram-package", [
            'package' => null,
            'channel' => $channel,
        ]);
    }


    public function getPackageRecordsByDate($package) {
        $start = Carbon::createFromDate($package->date_start);
        $end = Carbon::createFromDate($package->date_end);
        $records = Record::where(['channel_id' => $package->channel_id, 'is_interprogram' => true, 'is_advertising' => false]);
        $records->where(function($q) use ($start, $end) {
            $q = $q->whereBetween('date', [$start, $end]);
            $start_year = $start->year;
            $end_year = $end->year;
            if ($start_year != $end_year) {
                $full_years = [];
                for ($i = $start_year + 1; $i < $end_year; $i++) {
                    $full_years[] = $i;
                }
                $q->orWhereIn('year', $full_years);
            }
            $start_month = $start->month;
            $end_month = $end->month;
            $start_year_months = [];
            $end_year_months = [];
            for ($i = $start_month + 1; $i <= 12; $i++) {
                $start_year_months[] = $i;
            }
            for ($i = 1; $i < $end_month; $i++) {
                $end_year_months[] = $i;
            }
            $q->orWhere(function($sub) use ($start_year, $start_year_months) {
                $sub->where(['year' => $start_year]);
                $sub->whereIn('month', $start_year_months);
            });
            $q->orWhere(function($sub) use ($end_year, $end_year_months) {
                $sub->where(['year' => $end_year]);
                $sub->whereIn('month', $end_year_months);
            });
            $start_day = $start->day;
            $end_day = $end->day;
            $start_month_days = [];
            $end_month_days = [];
            for ($i = $start_day + 1; $i <= date('t', $start_month); $i++) {
                $start_month_days[] = $i;
            }
            for ($i = 1; $i < $end_day; $i++) {
                $end_month_days[] = $i;
            }
            $q->orWhere(function($sub) use ($start_year, $start_month, $start_month_days) {
                $sub->where(['year' => $start_year]);
                $sub->where(['month' => $start_month]);
                $sub->whereIn('day', $start_month_days);
            });
            $q->orWhere(function($sub) use ($end_year, $end_month, $end_month_days) {
                $sub->where(['year' => $end_year]);
                $sub->where(['month' => $end_month]);
                $sub->whereIn('day', $end_month_days);
            });
        });

        $records = $records->get();
        return $records;
    }

    public function save($channel_id) {
        if (!PermissionsHelper::allows('additionalown') && !PermissionsHelper::allows('additional')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $channel = Channel::findByIdOrUrl($channel_id);
        if (!$channel || !$channel->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $package = new InterprogramPackage();
        $package->channel_id = $channel->id;
        return $this->fillData($package);
    }


    public function edit($channel_id, $id) {
        $package = InterprogramPackage::find($id);
        if (!$package || !$package->can_edit) {
            return view("pages.errors.403");
        }
        $channel = $package->channel;

        if (!$channel->can_edit) {
            return view("pages.errors.403");
        }
        return view("pages.forms.interprogram-package", [
            'package' => $package,
            'channel' => $channel,
        ]);
    }

    public function update($channel_id, $id) {
        $package = InterprogramPackage::find($id);
        if (!$package || !$package->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $channel = $package->channel;
        if (!$channel || !$channel->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        return $this->fillData($package);
    }

    public function delete() {
        $package = InterprogramPackage::find(request()->input('package_id'));
        if (!$package || !$package->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $channel = $package->channel;
        if (!$channel || !$channel->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $package->delete();
        return [
            'status' => 1,
            'text' => 'Пакет удален',
            'redirect_to' => '/channels/'.$channel->url.'/graphics'
        ];
    }

    private function fillData($package) {
        $is_new = !$package->id;
        $data = request()->validate([
            'name' => 'sometimes',
            'description' => 'sometimes',
            'author' => 'sometimes',
            'date_start' => 'required|date',
            'date_end' => 'required|date',
            'cover_id' => 'sometimes',
            'url' => 'sometimes'
        ]);
        $package->fill($data);
        if(request()->input('name', '') == "") {
            $package->name = "";
        }
        $package->date_start = Carbon::parse($data['date_start']);
        $package->date_end = Carbon::parse($data['date_end']);

        $package->save();
        if (!$is_new) {
            if (request()->has('record_ids')){
                $ids = explode(",", request()->input('record_ids'));
                $index = 0;
                foreach ($ids as $id) {
                    $record = Record::find($id);
                    if ($record) {
                        $record->internal_order = $index;
                        $record->save();
                        $index++;
                    }
                }
            }
        }
        return [
            'status' => 1,
            'text' => 'Информация о пакете оформления обновлена',
            'redirect_to' => '/channels/'.$package->channel_id.'/graphics/edit/'.$package->id
        ];
    }

    public function ajax($id) {
        $channel = Channel::find($id);
        if (!$channel) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        return [
            'status' => 1,
            'data' => [
                'interprogram_packages' => $channel->interprogramPackages
            ]
        ];
    }
}
