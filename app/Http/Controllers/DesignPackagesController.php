<?php

namespace App\Http\Controllers;

use App\Constants\Actions;
use App\Constants\CacheTimes;
use App\Helpers\ActionsLogHelper;
use App\Helpers\PermissionsHelper;
use App\Helpers\ViewsHelper;
use App\Models\Annotation;
use App\Models\Channel;
use App\Models\Genre;
use App\Models\DesignPackage;
use App\Models\Program;
use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Mews\Purifier\Facades\Purifier;


class DesignPackagesController extends Controller
{

    public function index() {
        $packages = Cache::remember('design_packages', CacheTimes::PAGE, function () {
            $channel_ids = $this->getChannelsInternalOrder();
            $packages = DesignPackage::whereIn('channel_id', $channel_ids)
                ->whereHas('records')
                ->orderByRaw('FIELD(channel_id, ' . implode(',', $channel_ids->toArray()) . ' )')
                ->get()->groupBy('channel_id');
            $new_list = [];
            foreach ($packages as &$packages_list) {
                $new_list[] = $packages_list->sortBy('date_start');
            }
            return $new_list;
        });
        return view("pages.records.graphics-v2", [
            'packages' => $packages,
        ]);
    }

    public function catalog($start_params) {
        $records_conditions = array_merge($start_params, ['is_interprogram' => true]);

        $query_params = [];

        $regions = Channel::where($start_params)->where(['is_abroad' => false])->whereNotNull('city')->has('interprogramRecords', '>' , 0)->pluck('city')->unique();
        $total_count = Record::approved()->where($records_conditions)->count();

        $other_count = Record::approved()->where($records_conditions)->where('year', '<=', '0')->count();
        $base_url =  $start_params['is_radio'] ? "/radio/jingles" : "/video/graphics";

        $selected_year = null;
        $selected_region = null;

        $years_ranges = [
            'Советское' => [
                'year_end' => 1991,
            ],
            '90-е' => [
                'year_start' => 1992,
                'year_end' => 1999
            ],
            '2000-е' => [
                'year_start' => 2000,
                'year_end' => 2011
            ]
        ];
        $types_to_hide =  [11, 22];
        $hide_commercials = request()->input('hide_commercials', true);
        if ($hide_commercials) {
            $records_conditions['interprogram_type_not_in'] = $types_to_hide;
        }
        $query_params['hide_commercials'] = $hide_commercials ? 1 : 0;
        $selected_years_range = null;
        foreach ($years_ranges as $name => $params) {
            $selected = true;
            foreach ($params as $param => $value) {
                if (request()->input($param) != $value) {
                    $selected = false;
                }
            }
            if ($selected) {
                $selected_years_range =  $name;
                $query_params = $years_ranges[$name];
            }
        }
        if ($selected_years_range) {
            $records_conditions = array_merge($records_conditions, $years_ranges[$selected_years_range]);
        }

        if (request()->has('year')) {
            $selected_year = request()->input('year');
            if ($selected_year != "0") {
                $records_conditions['year'] = $selected_year;
                unset($query_params['year_start']);
                unset($query_params['year_end']);
                $query_params['year'] = $selected_year;
            } else {
                $records_conditions['year'] = null;
            }
        }


        if (request()->has('regional')) {
            $channels = Channel::where(['is_regional' => !!request()->input('regional')])->pluck('id');
            $query_params['region'] = $selected_region;
            $records_conditions['channel_id_in'] = $channels;
        }
        $types = [];
        $type_ids = [];
        foreach(Genre::where(['type' => 'interprogram'])->get() as $type) {
            $type_ids[$type->url] = $type->id;
            $types[$type->url] = $type->name;
        }
        $selected_type = request()->input('type');
        $records_conditions['program_id'] = null;
        if (isset($type_ids[$selected_type])) {
            $query_params['type'] = $selected_type;
            $records_conditions['interprogram_type'] = $type_ids[$selected_type];
        } else {
            $selected_type = null;
        }
        $records_conditions['normal_date'] = true;

        $years = Record::where($start_params)->where(['is_interprogram' => true, 'is_advertising' => false])->where('year','>','1950');
        if ($hide_commercials) {
            $years = $years->whereNotIn('interprogram_type', $types_to_hide);
        }
        $years = $years->selectRaw('count(*) as count_year, year')->groupBy('year')->orderBy('year', 'asc')->pluck('count_year', 'year');

        $page_title = $start_params['is_radio'] ? "Оформление радиостанций" : "Графическое оформление телеканалов";
        return view("pages.records.graphics", [
            'hide_commercials' => $hide_commercials,
            'is_radio' => $start_params['is_radio'],
            'page_title' => $page_title,
            'query_params' => $query_params,
            'types' => $types,
            'selected_type' => $selected_type,
            'records_conditions' => $records_conditions,
            'years_ranges' => $years_ranges,
            'selected_years_range' => $selected_years_range,
            'regions' => $regions,
            'selected_year' => $selected_year,
            'selected_region' => $selected_region,
            'years' => $years,

            'total_count' => $total_count,
            'other_count' => $other_count,
            'base_url' => $base_url,
        ]);
    }

    public function programs($is_radio = false) {
        $channel_ids = $this->getChannelsInternalOrder($is_radio);
        $program_ids = Record::whereNotNull('program_id')->where(['is_interprogram' => true])->pluck('program_id');
        $programs = Program::whereIn('channel_id', $channel_ids)->orderByRaw('FIELD(channel_id, '.implode(',', $channel_ids->toArray()).' )')->whereIn('id', $program_ids)->get()->groupBy('channel_id');
        //$program_packages = InterprogramPackage::whereNotNull('program_id')->pluck('program_id');
        //$programs_with_packages = Program::whereIn('channel_id', $channel_ids)->whereIn('id', $program_packages)->get();

        return view("pages.programs.design-index", [
            'programs' => $programs,
            'is_radio' => false
        ]);
    }



    public function show($channel_url, $package_url)
    {
        $channel = Channel::where(['url' => $channel_url])->orWhere(['id' => $channel_url])->first();
        if (!$channel) {
            return redirect(route('index'));
        }
        $annotations = [];

        $is_other = $package_url == "other";
        $hide_unsorted = request()->input('hide_unsorted', !$is_other);

        if ($is_other) {
            $types_to_hide = [11, 22];
            $base_url = typed_route('design.[CHANNEL].show', $channel->is_radio, [$channel->url ?? $channel->id, 'other']);

            $conditions = [
                'channel_id' => $channel->id,
                'program_id' => null,
                'is_interprogram' => true,
                'is_selected' => false,
                'show_years' => true
            ];
            if ($hide_unsorted) {
                $conditions['interprogram_package_id'] = null;
                $conditions['interprogram_type_not_in'] = $types_to_hide;
            }
            $package = new DesignPackage([
                'id' => 'other_'.$channel->id,
                'name' => 'Прочее',
                'pictures' => [],
                'years_range' => '',
                'channel_id' => $channel->id,
                'url' => 'other',
            ]);

            $related = [];
            // $related =  InterprogramPackage::where(['channel_id' => $channel->id])->inRandomOrder()->limit(5)->get();
        } else {

            $conditions = [];
            $package = DesignPackage::where(['channel_id' => $channel->id])->where(function ($q) use ($package_url) {
                $q->where(['id' => $package_url]);
                $q->orWhere(['url' => $package_url]);
            })->first();
            if (!$package) {
                return redirect($channel->full_url);
            }
            $base_url = $package->full_url;

            ViewsHelper::increment($package, 'interprogram');
            $related = DesignPackage::where(['channel_id' => $channel->id])->where('id', '!=', $package->id)->inRandomOrder()->limit(5)->get();

            $annotations = Cache::remember('design_package_records_'.$package->id, CacheTimes::RELATION, function () use ($package) {
                $types_to_hide = [22];
                $records = $package->records;
               // if ($hide_unsorted) {
                    $records = $records->filter(function ($record) use ($types_to_hide) {
                        return !in_array($record->interprogram_type, $types_to_hide);
                    });
              //  }

                $annotations = $package->annotations;
                $annotations = $annotations->map(function ($annotation, $index) use ($annotations, $records) {
                    return [
                        'annotation' => $annotation,
                        'records' => $records->filter(function ($record) use ($annotations, $annotation, $index) {
                            return $record->internal_order > $annotation->order && (!isset($annotations[$index + 1]) || $record->internal_order < $annotations[$index + 1]->order);
                        })
                    ];
                });

                if (count($annotations) > 0) {
                    $annotations->push([
                        'annotation' => null,
                        'records' => $records->filter(function ($record) use ($annotations) {
                            return $record->internal_order > $annotations[count($annotations) - 1]['annotation']->order;
                        })
                    ]);
                }

                $other_records = $records->filter(function ($record) use ($annotations) {
                    return count($annotations) == 0 || $record->internal_order < $annotations[0]['annotation']->order;
                });
                if (count($other_records) > 0) {
                    $annotations->push([
                        'annotation' => null,
                        'records' => $other_records
                    ]);
                }
                return $annotations;
            });
        }

        return view('pages.graphics.show', [
            'annotations' => $annotations,
            'hide_unsorted' => $hide_unsorted,
            'base_url' => $base_url,
            'related' => $related,
            'records_conditions' => $conditions,
            'other' => $is_other,
            'channel' => $channel,
            'package' => $package
        ]);
    }

    public function showAll($channel_url)
    {
        $channel = Channel::where(['url' => $channel_url])->orWhere(['id' => $channel_url])->first();
        if (!$channel) {
            return redirect(route('index'));
        }
        $base_url = null;
        $related = null;

        $types_to_hide = [22];
        $packages = DesignPackage::where(['channel_id' => $channel->id])->orderBy('date_start', 'asc')->get();
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
            'base_url' => $base_url,
            'channel' => $channel,
        ]);
    }


    public function add($data)
    {
        if (!PermissionsHelper::allows('additionalown') && !PermissionsHelper::allows('additional')) {
            return redirect('/');
        }
        $channel = null;
        $program = null;

        if (isset($data['channel_id'])) {
            $channel = Channel::findByIdOrUrl($data['channel_id']);
            if (!$channel) {
                return redirect(route('index'));
            }
            if (!$channel->can_edit) {
                return redirect('/');
            }
        } elseif (isset($data['program_id'])) {
            $program = Program::findByIdOrUrl($data['program_id']);
            if (!$program) {
                return redirect(route('index'));
            }
            if (!$program->can_edit) {
                return redirect('/');
            }
            $channel = $program->channel;
            if ($channel && !$channel->can_edit) {
                return redirect('/');
            }
        }
        return view("pages.graphics.form", [
            'package' => null,
            'program' => $program,
            'channel' => $channel,
        ]);
    }


    public function getPackageRecordsByDate($package)
    {
        $start = Carbon::createFromDate($package->date_start);
        $end = Carbon::createFromDate($package->date_end);
        $records = Record::where(['channel_id' => $package->channel_id, 'is_interprogram' => true, 'is_advertising' => false]);
        $records->where(function ($q) use ($start, $end) {
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
            $q->orWhere(function ($sub) use ($start_year, $start_year_months) {
                $sub->where(['year' => $start_year]);
                $sub->whereIn('month', $start_year_months);
            });
            $q->orWhere(function ($sub) use ($end_year, $end_year_months) {
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
            $q->orWhere(function ($sub) use ($start_year, $start_month, $start_month_days) {
                $sub->where(['year' => $start_year]);
                $sub->where(['month' => $start_month]);
                $sub->whereIn('day', $start_month_days);
            });
            $q->orWhere(function ($sub) use ($end_year, $end_month, $end_month_days) {
                $sub->where(['year' => $end_year]);
                $sub->where(['month' => $end_month]);
                $sub->whereIn('day', $end_month_days);
            });
        });

        $records = $records->get();
        return $records;
    }

    public function save($data)
    {
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

        $package = new DesignPackage();
        if ($program) {
            $package->program_id = $program->id;
        } else {
            $package->channel_id = $channel->id;
        }

        return $this->fillData($package);
    }


    public function edit($data, $id)
    {
        $package = DesignPackage::find($id);
        if (!$package || !$package->can_edit) {
            return redirect('/');
        }
        $channel = $package->channel;
        $program = $package->program;
        if ($program) {
            $channel = $package->program->channel;
        }

        $types_to_hide = [22];
        $package->records = $package->records->filter(function ($record) use ($types_to_hide) {
            return !in_array($record->interprogram_type, $types_to_hide);
        })->values();
        return view("pages.graphics.form", [
            'package' => $package,
            'program' => $program,
            'channel' => $channel,
        ]);
    }

    public function update($channel_id, $id)
    {
        $package = DesignPackage::find($id);
        if (!$package || !$package->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        return $this->fillData($package);
    }

    public function delete()
    {
        $package = DesignPackage::find(request()->input('id'));
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

        ActionsLogHelper::create($package, Actions::Delete);

        return [
            'status' => 1,
            'text' => 'Пакет удален',
            'redirect_to' => $channel->full_url
        ];
    }

    private function fillData($package)
    {
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

        if (isset($data['description'])) {
            $data['description'] = Purifier::clean($data['description']);
        }

        $package->fill($data);
        if (request()->input('name', '') == "") {
            $package->name = "";
        }
        $package->date_start = Carbon::parse($data['date_start']);
        $package->date_end = Carbon::parse($data['date_end']);

        ActionsLogHelper::create($package, $package->id ? Actions::Update : Actions::Create);

        $new_annotation_ids = [];
        $old_annotation_ids = Annotation::where(['interprogram_package_id' => $package->id])->pluck('id')->toArray();
        if (!$is_new) {
            if (request()->has('records_data')) {
                $data = json_decode(request()->input('records_data'));
                $index = 0;

                Record::where(['interprogram_package_id' => $package->id])->update(['interprogram_package_id' => null]);

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
                        if (isset($item->model->title)) {
                            $annotation->title = $item->model->title;
                        }
                        if (isset($item->model->text)) {
                            $annotation->text = $item->model->text;
                        }
                        $annotation->order = $index;
                        $annotation->save();
                        $index++;
                        $new_annotation_ids[] = $annotation->id;
                    } else {
                        $record = Record::find($item->id);
                        if ($record) {
                            $record->interprogram_package_id = $package->id;
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

        Cache::forget('design_package_records_'.$package->id);
        Cache::forget('design_package_url_'.$package->id);
        Cache::forget('design_package_random_pictures_'.$package->id);

        return [
            'status' => 1,
            'text' => 'Информация о пакете оформления обновлена',
            'redirect_to' => $package->program_id ? route('design.programs.edit', ['id' => $package->program_id, 'package_id' => $package->id]) : typed_route('design.[CHANNEL].edit', $package->channel->is_radio, ['id' => $package->channel_id, 'package_id' => $package->id]),

        ];
    }

    public function ajax($conditions)
    {
        $design_packages = DesignPackage::where($conditions)->get();
        return [
            'status' => 1,
            'data' => [
                'design_packages' => $design_packages
            ]
        ];
    }


    public function showByProgram($id)
    {
        $program = Program::findByIdOrUrl($id);
        if (!$program) {
            return redirect(route('index'));
        }
        $packages = $program->interprogramPackages;

        $not_sorted_interprogram = $program->records->sortBy('supposed_date')->filter(function ($record) {
            return $record->is_interprogram && !$record->interprogram_package_id;
        });
        if (count($not_sorted_interprogram) > 0) {
            //$not_sorted_interprogram = $not_sorted_interprogram->slice(0, 50);
            $packages->push(new DesignPackage([
                'name' => count($packages) > 0 ? 'Прочее' : '',
                'pictures' => [],
                'years_range' => '',
                'is_other' => true,
                'records' => $not_sorted_interprogram
            ]));
        }
        $related_program_ids = DesignPackage::whereNotNull('program_id')->inRandomOrder()->limit(10)->pluck('program_id');
        $related_programs = Program::whereIn('id', $related_program_ids)->get();
        return view('pages.programs.design', [
            'program' => $program,
            'packages' => $packages,
            'related_programs' => $related_programs
        ]);
    }

    private function getChannelsInternalOrder($is_radio = false) {
        $query = Channel::approved()->where(['is_radio' => $is_radio]);
        $federal_channel_ids = $query->clone()->where(['is_federal' => true])->orderBy('order', 'asc')->pluck('id');
        $other_channel_ids = $query->clone()->where(['is_federal' => false, 'is_regional' => false])->orderBy('order', 'asc')->pluck('id');
        $regional_channel_ids = $query->clone()->where(['is_regional' => true])->orderBy('order', 'asc')->pluck('id');
        return $federal_channel_ids->merge($other_channel_ids)->merge($regional_channel_ids);
    }

}
