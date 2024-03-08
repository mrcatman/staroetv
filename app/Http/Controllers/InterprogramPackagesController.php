<?php

namespace App\Http\Controllers;

use App\AdditionalChannel;
use App\Annotation;
use App\Award;
use App\Channel;
use App\ChannelName;
use App\Helpers\PermissionsHelper;
use App\Helpers\ViewsHelper;
use App\InterprogramPackage;
use App\Program;
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
            return redirect("https://staroetv.su/");
        }
        $base_link = null;
        $related = null;
        $records_with_annotations = null;
        $hide_unsorted = request()->input('hide_unsorted', true);

         if ($package_url == "other") {
             $types_to_hide =  [11, 22];
            $base_link = $channel->full_url."/graphics/other";
            $is_other = true;
            $conditions = [
                'channel_id' => $channel->id,
                'is_interprogram' => true,
                'is_selected' => false,
            ];
            if ($hide_unsorted) {
                $conditions['interprogram_package_id'] = null;
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
             $types_to_hide =  [22];
             $conditions = [];
            $is_other = false;
            $package = InterprogramPackage::where(['channel_id' => $channel->id])->where(function ($q) use ($package_url) {
                $q->where(['id' => $package_url]);
                $q->orWhere(['url' => $package_url]);
            })->first();
            if (!$package) {
                return redirect("https://staroetv.su/video");
            }
            $base_link = $package->full_url;

            ViewsHelper::increment($package, 'interprogram');
            $related = InterprogramPackage::where(['channel_id' => $channel->id])->where('id', '!=', $package->id)->inRandomOrder()->limit(5)->get();
            $records = $package->records->map(function($record) {
                return [
                    'order' => $record->internal_order,
                    'is_annotation' => false,
                    'data' => $record
                ];
            });
            $annotations = $package->annotations->map(function($annotation) {
                return [
                    'order' => $annotation->order,
                    'is_annotation' => true,
                    'data' => $annotation
                ];
            });
            if ($hide_unsorted) {
                $records = $records->filter(function($record) use ($types_to_hide) {
                    return !in_array($record['data']->interprogram_type, $types_to_hide);
                });
            }
            $records_with_annotations = $records->merge($annotations)->sortBy('order');
            if (request()->has('test')) {
                foreach ($records_with_annotations as $record) {
                    if ($record['is_annotation']) {
                        echo "<br>".$record['data']->title."<br>";
                    } else {
                        echo "file 'https://staroetv.su".$record['data']->source_path."'<br>";
                    }
                }
                dd($records_with_annotations);
            }

        }
        if (!$package) {
            return redirect($channel->full_url);
        }

        return view('pages.graphics.show', [
            'records_with_annotations' => $records_with_annotations,
            'hide_unsorted' => $hide_unsorted,
            'base_link' => $base_link,
            'related' => $related,
            'records_conditions' => $conditions,
            'other' => $is_other,
            'channel' => $channel,
            'package' => $package
        ]);
    }

    public function showAll($channel_url) {
        $channel = Channel::where(['url' => $channel_url])->orWhere(['id' => $channel_url])->first();
        if (!$channel) {
            return redirect("https://staroetv.su/");
        }
        $base_link = null;
        $related = null;

        $types_to_hide =  [22];
        $packages = InterprogramPackage::where(['channel_id' => $channel->id])->orderBy('date_start', 'asc')->get();
        foreach ($packages as $package) {
            $records = $package->records->map(function ($record) {
                return [
                    'order' => $record->internal_order,
                    'is_annotation' => false,
                    'data' => $record
                ];
            });
            $annotations = $package->annotations->map(function ($annotation) {
                return [
                    'order' => $annotation->order,
                    'is_annotation' => true,
                    'data' => $annotation
                ];
            });
            $records = $records->filter(function ($record) use ($types_to_hide) {
                return !in_array($record['data']->interprogram_type, $types_to_hide);
            });
            $package->records_with_annotations = $records->merge($annotations)->sortBy('order');
        }
        return view('pages.graphics.show_all', [
            'packages' => $packages,
            'base_link' => $base_link,
            'channel' => $channel,
        ]);
    }


    public function add($data) {
        if (!PermissionsHelper::allows('additionalown') && !PermissionsHelper::allows('additional')) {
            return view("pages.errors.403");
        }
        $channel = null;
        $program = null;

        if (isset($data['channel_id'])) {
            $channel = Channel::findByIdOrUrl($data['channel_id']);
            if (!$channel) {
                return redirect("https://staroetv.su/");
            }
            if (!$channel->can_edit) {
                return view("pages.errors.403");
            }
        } elseif (isset($data['program_id'])) {
            $program = Program::findByIdOrUrl($data['program_id']);
            if (!$program) {
                return redirect("https://staroetv.su/");
            }
            if (!$program->can_edit) {
                return view("pages.errors.403");
            }
            $channel = $program->channel;
            if ($channel && !$channel->can_edit) {
                return view("pages.errors.403");
            }
        }
        return view("pages.forms.interprogram-package", [
            'package' => null,
            'program' => $program,
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

    public function save($data) {
        if (!PermissionsHelper::allows('additionalown') && !PermissionsHelper::allows('additional')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $channel = null;
        $program = null;
        if (isset($data['channel_id'])) {
            $channel = Channel::findByIdOrUrl($data['channel_id']);
            if (!$channel || !$channel->can_edit) {
                return [
                    'status' => 0,
                    'text' => 'Ошибка доступа'
                ];
            }
        } elseif (isset($data['program_id'])) {
            $program = Program::findByIdOrUrl($data['program_id']);
            if (!$program || !$program->can_edit) {
                return [
                    'status' => 0,
                    'text' => 'Ошибка доступа'
                ];
            }
            $channel = $program->channel;
            if (!$channel || !$channel->can_edit) {
                return [
                    'status' => 0,
                    'text' => 'Ошибка доступа'
                ];
            }
        }

        $package = new InterprogramPackage();
        if ($program) {
            $package->program_id = $program->id;
        } else {
            $package->channel_id = $channel->id;
        }

        return $this->fillData($package);
    }


    public function edit($data, $id) {
        $package = InterprogramPackage::find($id);
        if (!$package || !$package->can_edit) {
            return view("pages.errors.403");
        }
        $channel = $package->channel;
        $program = $package->program;
        if ($program) {
            $channel = $package->program->channel;
        }
        //if (!$channel->can_edit) {
        //    return view("pages.errors.403");
        //}
         return view("pages.forms.interprogram-package", [
            'package' => $package,
            'program' => $program,
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
        $new_annotation_ids = [];
        $old_annotation_ids = Annotation::where(['interprogram_package_id' => $package->id])->pluck('id')->toArray();
        if (!$is_new) {
            if (request()->has('records_data')){
                $data = json_decode(request()->input('records_data'));
                $index = 0;
                foreach ($data as $item) {
                    if ($item->is_annotation) {
                        $annotation = null;
                        if (isset($item->id)) {
                            $annotation = Annotation::find($item->id);
                        }
                        if (!$annotation) {
                            $annotation = new Annotation([
                                'interprogram_package_id' => $package->id
                            ]);
                        }
                        if (isset($item->title)) {
                            $annotation->title = $item->title;
                        }
                        if (isset($item->text)) {
                            $annotation->text = $item->text;
                        }
                        $annotation->order = $index;
                        $annotation->save();
                        $index++;
                        $new_annotation_ids[] = $annotation->id;
                    } else {
                        $record = Record::find($item->id);
                        if ($record) {
                            $record->internal_order = $index;
                            $record->save();
                            $index++;
                        }
                    }
                }
            }
            $ids_to_delete = array_diff($old_annotation_ids, $new_annotation_ids);
            if (count($ids_to_delete) > 0) {
                Annotation::whereIn('id', $ids_to_delete)->delete();
            }
        }
        return [
            'status' => 1,
            'text' => 'Информация о пакете оформления обновлена',
            'redirect_to' => $package->program_id ? '/programs/'.$package->program_id.'/graphics/edit/'.$package->id : '/channels/'.$package->channel_id.'/graphics/edit/'.$package->id

        ];
    }

    public function ajax($conditions) {
        $graphics = InterprogramPackage::where($conditions)->get();
        return [
            'status' => 1,
            'data' => [
                'graphics' => $graphics
            ]
        ];
    }


    public function showByProgram($id) {
        $program = Program::findByIdOrUrl($id);
        if (!$program) {
            return redirect("https://staroetv.su/");
        }
        $packages = $program->interprogramPackages;
        foreach ($packages as $package) {
            //$package->records = $package->records->sortByDesc('id');
            //$records = $records->merge($this->getPackageRecordsByDate($package));
            //$package->records_list = $records;
        }
        $not_sorted_interprogram = $program->records->sortByDesc('id')->filter(function($record) {
            return $record->is_interprogram && !$record->interprogram_package_id;
        });
        if (count($not_sorted_interprogram) > 0) {
            $not_sorted_interprogram = $not_sorted_interprogram->slice(0, 50);
            $packages->push(new InterprogramPackage([
                'id' => 0,
                'name' => 'Прочее',
                'pictures' => [],
                'years_range' => '',
                'is_other' => true,
                'records' => $not_sorted_interprogram
            ]));

        }
        return view('pages.programs.graphics', [
            'program' => $program,
            'packages' => $packages
        ]);
    }
}
