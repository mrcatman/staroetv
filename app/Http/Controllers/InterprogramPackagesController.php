<?php

namespace App\Http\Controllers;

use App\Award;
use App\Channel;
use App\ChannelName;
use App\Helpers\PermissionsHelper;
use App\InterprogramPackage;
use App\Record;
use App\User;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;


class InterprogramPackagesController extends Controller {

    public function show($channel_url) {
        $channel = Channel::where(['url' => $channel_url])->orWhere(['id' => $channel_url])->first();
        if (!$channel) {
            return redirect("/");
        }
        $packages = $channel->interprogramPackages;
        foreach ($packages as $package) {
            $records = $package->records;
            $records = $records->merge($this->getPackageRecordsByDate($package));
            $package->records_list = $records;
        }
        return view('pages.channels.graphics', [
            'channel' => $channel,
            'packages' => $packages
        ]);
    }


    public function add($channel_id) {
        if (!PermissionsHelper::allows('additionalown') && !PermissionsHelper::allows('additional')) {
            return view("pages.errors.403");
        }
        $channel = Channel::find($channel_id);
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
        $channel = Channel::find($channel_id);
        if (!$channel || !$channel->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $package = new InterprogramPackage();
        $package->channel_id = $channel_id;
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
        //$package->delete();
        return [
            'status' => 1,
            'text' => 'Пакет удален',
            'redirect_to' => '/channels/'.$channel->url.'/graphics'
        ];
    }

    private function fillData($package) {
        $data = request()->validate([
            'name' => 'sometimes',
            'description' => 'sometimes',
            'author' => 'sometimes',
            'date_start' => 'required|date',
            'date_end' => 'required|date',
        ]);
        $package->fill($data);
        $package->date_start = Carbon::parse($data['date_start']);
        $package->date_end = Carbon::parse($data['date_end']);
        $package->save();
        return [
            'status' => 1,
            'text' => 'Информация о пакете оформления обновлена',
            'redirect_to' => '/channels/'.$package->channel_id.'/graphics/edit/'.$package->id
        ];
    }
}
