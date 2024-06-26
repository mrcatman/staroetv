<?php

namespace App\Http\Controllers;

use App\AdditionalChannel;
use App\Channel;
use App\ChannelName;
use App\Genre;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use App\Helpers\RecordsHelper;
use App\Helpers\ViewsHelper;
use App\HistoryEvent;
use App\InterprogramPackage;
use App\Picture;
use App\Program;
use App\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class RecordsController extends Controller {

    protected $month_names = ["Январь","Февраль","Март","Апрель","Май","Июнь","Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь"];
    protected $month_names_parental_case = ["января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря"];

    public function buildChannelsList($params) {
        $federal = Channel::where(['is_federal' => true])->where($params)->orderBy('order', 'ASC')->get();

        $regions = json_decode(file_get_contents(public_path("data/cities.json")), 1);
        $regions_by_city = [];
        foreach ($regions as $region => $cities) {
            foreach ($cities as $city) {
                $regions_by_city[$city] = $region;
            }
        }
        $channels_by_region = [];
        $regional = Channel::where(['is_regional' => true, 'is_abroad' => false])->where($params)->orderBy('order', 'ASC')->get();
        foreach ($regional as $channel) {
            $channel_data = [
                'id' => $channel->id,
                'name' => $channel->name,
                'logo' => $channel->logo,
                'url' => $channel->full_url,
            ];
            if (isset($regions_by_city[$channel->city])) {
                if (!isset($channels_by_region[$regions_by_city[$channel->city]])) {
                    $channels_by_region[$regions_by_city[$channel->city]] = ['cities' => [], 'channels' => [], 'count' => 0];
                }
                if (!isset($channels_by_region[$regions_by_city[$channel->city]]['cities'][$channel->city])) {
                    $channels_by_region[$regions_by_city[$channel->city]]['cities'][$channel->city] = [];
                }
                $channels_by_region[$regions_by_city[$channel->city]]['count']++;
                $channels_by_region[$regions_by_city[$channel->city]]['cities'][$channel->city][] = $channel_data;
            } elseif (isset($regions[$channel->city])) {
                if (!isset($channels_by_region[$channel->city])) {
                    $channels_by_region[$channel->city] = ['cities' => [], 'channels' => [], 'count' => 0];
                }
                $channels_by_region[$channel->city]['count']++;
                $channels_by_region[$channel->city]['channels'][] = $channel_data;
            }
        }
        //$main_cities = ['Москва', 'Санкт-Петербург', 'Новосибирск', 'Екатеринбург', 'Казань'];

        //dd($channels_by_region);
        ksort($channels_by_region);
        $abroad = Channel::where(['is_abroad' => true])->where($params)->orderBy('order', 'ASC')->get();
        $abroad_by_country = [];
        foreach ($abroad as $channel) {
            $channel_data = [
                'id' => $channel->id,
                'name' => $channel->name,
                'logo' => $channel->logo,
                'url' => $channel->full_url,
            ];
            if (!isset($abroad_by_country[$channel->country])) {
                $abroad_by_country[$channel->country] = ['cities' => [], 'channels' => []];
            }
            if (!$channel->city) {
                $abroad_by_country[$channel->country]['channels'][] = $channel_data;
            } else {
                if (!isset($abroad_by_country[$channel->country]['cities'][$channel->city])) {
                    $abroad_by_country[$channel->country]['cities'][$channel->city] = [];
                }
                $abroad_by_country[$channel->country]['cities'][$channel->city][] = $channel_data;
            }
        }
        ksort($abroad_by_country);
        $other = Channel::where(['is_federal' => false, 'is_regional' => false, 'is_abroad' => false])->where($params)->orderBy('order', 'ASC')->get();
        return [
            'federal' => $federal,
            'regional' => $channels_by_region,
            'abroad' => $abroad_by_country,
            'other' => $other
        ];
    }

    public function index($params) {
        if (!PermissionsHelper::allows('contentapprove')) {
            $params['pending'] = false;
        }
        $data = $this->buildChannelsList($params);
        $last_records = Record::approved()->where($params)->orderBy('original_added_at', 'desc')->paginate(60);
        $data['params'] = $params;
        $data['last_records'] = $last_records;
        $data['events'] = [];
       // $data['events'] = HistoryEvent::approved()->orderBy('id', 'desc')->limit(3)->get();
        return view("pages.records.index", $data);
    }


    public function show($id) {
        $record = Record::approved()->where(['id' => $id])->firstOrFail();

        $related_program = null;
        $related_channel = null;
        $related_interprogram = null;
        $related_advertising = null;
        $related_other = null;
        $playlist = null;
        if ($record->interprogram_package_id && $record->interprogram_type != 22) {
            $package = $record->interprogramPackage;
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
            $types_to_hide = [22];
            $records = $records->filter(function($record) use ($types_to_hide) {
                return !in_array($record['data']->interprogram_type, $types_to_hide);
            });
            $playlist = $records->merge($annotations)->sortBy('order');
            //$index = $playlist->search(function($playlist_record) use ($record) {
            //    return !$playlist_record['is_annotation'] && $playlist_record['data']->id === $record->id;
            //});
        }
        if (!$playlist) {
            if ($record->is_interprogram && $record->interprogram_package_id > 0) {
                if ($record->is_selected) {
                    $related_interprogram = Record::approved()->where(['interprogram_package_id' => $record->interprogram_package_id, 'is_selected' => true])->where('id', '!=', $record->id)->inRandomOrder()->limit(5)->get();
                }
                if (!$record->is_selected || count($related_interprogram) == 0) {
                    $related_interprogram = Record::approved()->where(['interprogram_package_id' => $record->interprogram_package_id])->where('id', '!=', $record->id)->inRandomOrder()->limit(5)->get();
                }
            }
            if ($record->program) {
                $related_program = Record::approved()->where(['program_id' => $record->program_id])->where('id', '!=', $record->id)->inRandomOrder()->limit(5)->get();
            }
            if ($record->channel && !$record->is_advertising) {
                $limit = (!$record->program || ($related_program && count($related_program) === 0)) && !$record->interprogram_package_id ? 10 : 5;
                $related_channel = Record::approved()->where(['channel_id' => $record->channel_id, 'is_advertising' => false])->where('id', '!=', $record->id)->inRandomOrder()->limit($limit)->get();
            }

            if ($record->is_advertising) {
                $related_advertising = Record::approved()->where(['is_radio' => $record->is_radio, 'is_advertising' => true, 'advertising_brand' => $record->advertising_brand])->where('id', '!=', $record->id)->inRandomOrder()->limit(10)->get();
                if (count($related_advertising) == 0 && $record->year) {
                    $related_advertising = Record::approved()->where(['is_radio' => $record->is_radio, 'is_advertising' => true, 'year' => $record->year])->where('id', '!=', $record->id)->inRandomOrder()->limit(10)->get();
                }
            }
            if ((!$related_interprogram || count($related_interprogram) === 0) && (!$related_program || count($related_program) === 0) && (!$related_channel || count($related_channel) === 0) && (!$related_advertising || count($related_advertising) === 0)) {
                $related_other = Record::approved()->where(['is_radio' => $record->is_radio])->where('id', '!=', $record->id)->inRandomOrder()->limit(10)->get();
            }
        }
        $changed_name = false;
        if ($record->program && $record->program->channel_id != $record->channel_id) {
            $additional_channel_data = AdditionalChannel::where(['program_id' => $record->program->id, 'channel_id' => $record->channel_id])->first();
            if ($additional_channel_data) {
                $changed_name = true;
                if ( $additional_channel_data->title != "") {
                    $record->program->name = $additional_channel_data->title;
                }
            }
        }
        ViewsHelper::increment($record, 'records');
        return view("pages.records.show", [
            'record' => $record,
            'playlist' => $playlist,
            'related_interprogram' => $related_interprogram,
            'related_program' => $related_program,
            'related_channel' => $related_channel,
            'related_advertising' => $related_advertising,
            'related_other' => $related_other,
            'changed_name' => $changed_name,
        ]);
    }

    public function showOld($id) {
        $record = Record::where(['ucoz_id' => $id])->firstOrFail();
        return redirect($record->url);
    }

    public function advertising($start_params, $ajax = false) {
        $records_conditions = array_merge($start_params, ['is_advertising' => true]);

        $query_params = [];

        $regions = Record::approved()->where($start_params)->select('region')->whereNotNull('region')->where(['is_advertising' => true])->groupBy('region')->get()->pluck('region');
        $total_count = Record::approved()->where($records_conditions)->count();

        $other_count = Record::approved()->where($records_conditions)->where('year', '<=', '0')->count();
        $base_link = $start_params['is_radio'] ? "/radio/commercials" : "/video/commercials";

        $selected_year = null;
        $selected_region = null;

        $years_ranges = [
            'Советская' => [
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


        if (request()->has('region')) {
            $selected_region = request()->input('region');
            if ($selected_region != "0") {
                $query_params['region'] = $selected_region;
                $records_conditions['region'] = $selected_region;
            } else {
                $records_conditions['region'] = null;
            }
        }
        $types = [];
        $type_ids = [];
        foreach(Genre::where(['type' => 'advertising'])->get() as $type) {
            $type_ids[$type->url] = $type->id;
            $types[$type->url] = $type->name;
        }
        $selected_type = request()->input('type');
        if (isset($type_ids[$selected_type])) {
            $query_params['type'] = $selected_type;
            $records_conditions['advertising_type'] = $type_ids[$selected_type];
        } else {
            $selected_type = null;
        }

        $selected_brand = null;
        if (request()->has('brand')) {
            $selected_brand =  request()->input('brand');;
            $query_params['brand'] = $selected_brand;
            $records_conditions['advertising_brand'] =  $selected_brand;
        }

        $years = Record::where($start_params)->where(['is_advertising' => true])->where('year','>','0')->selectRaw('count(*) as count_year, year')->groupBy('year')->orderBy('year', 'asc')->pluck('count_year', 'year');
        $data = [
            'selected_brand' => $selected_brand,
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
            'base_link' => $base_link,
            'is_radio' => $start_params['is_radio']
        ];
        if ($ajax) {
            return $data;
        }
        return view("pages.records.advertising", $data);
    }

    public function advertisingBrands($params) {
        $base_url = !$params['is_radio'] ? "/video/commercials-search" : "/radio/commercials-search";
        if (request()->has('id')) {
            $record = Record::find(request()->input('id'));
            if ($record) {
                $records_conditions = [
                    'is_advertising' => true,
                    'advertising_brand' => $record->advertising_brand
                ];
                return view("pages.records.advertising_brand", [
                    'brand' => $record->advertising_brand,
                    'is_radio' => $params['is_radio'],
                    'records_conditions' => $records_conditions,
                    'base_url' => $base_url,
                ]);
            }
        }
        $brands = Record::approved()->where($params)->where(['is_advertising' => true, 'advertising_type' => null, 'region' => null, 'country' => null])->groupBy('advertising_brand')->orderBy('advertising_brand', 'asc');
        $search = request()->input('search', '');
        if ($search != '') {
            $brands = $brands->where('advertising_brand', 'LIKE', '%'.$search.'%');
        }
        $brands = $brands->paginate(48);

        return view("pages.records.advertising_brands", [
            'is_radio' => $params['is_radio'],
            'search' => $search,
            'base_url' => $base_url,
            'brands' => $brands
        ]);
    }


    public function interprogram($start_params) {
        $records_conditions = array_merge($start_params, ['is_interprogram' => true]);

        $query_params = [];

        $regions = Channel::where($start_params)->where(['is_abroad' => false])->whereNotNull('city')->has('interprogramRecords', '>' , 0)->pluck('city')->unique();
        $total_count = Record::approved()->where($records_conditions)->count();

        $other_count = Record::approved()->where($records_conditions)->where('year', '<=', '0')->count();
        $base_link =  $start_params['is_radio'] ? "/radio/jingles" : "/video/graphics";

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
            'base_link' => $base_link,
        ]);
    }

    private function getChannelsInternalOrder($start_params) {
        $federal_channel_ids = Channel::where($start_params)->where(['is_federal' => true])->orderBy('order', 'asc')->pluck('id');
        $other_channel_ids = Channel::where($start_params)->where(['is_federal' => false, 'is_regional' => false])->orderBy('order', 'asc')->pluck('id');
        $regional_channel_ids = Channel::where($start_params)->where(['is_regional' => true])->orderBy('order', 'asc')->pluck('id');
        return $federal_channel_ids->merge($other_channel_ids)->merge($regional_channel_ids);
    }

    public function interprogramV2($start_params) {
        $channel_ids = $this->getChannelsInternalOrder($start_params);
        $packages = InterprogramPackage::whereIn('channel_id', $channel_ids)->orderByRaw('FIELD(channel_id, '.implode(',', $channel_ids->toArray()).' )')->get()->groupBy('channel_id');
        $new_list = [];
        foreach ($packages as &$packages_list) {
            $new_list[] = $packages_list->sortBy('date_start');
        }

        return view("pages.records.graphics_v2", [
            'packages' => $new_list,
        ]);
    }

    public function programsGraphics($start_params) {
        $channel_ids = $this->getChannelsInternalOrder($start_params);
        $program_ids = Record::whereNotNull('program_id')->where(['is_interprogram' => true])->pluck('program_id');
        $programs = Program::whereIn('channel_id', $channel_ids)->orderByRaw('FIELD(channel_id, '.implode(',', $channel_ids->toArray()).' )')->whereIn('id', $program_ids)->get()->groupBy('channel_id');
        //$program_packages = InterprogramPackage::whereNotNull('program_id')->pluck('program_id');
        //$programs_with_packages = Program::whereIn('channel_id', $channel_ids)->whereIn('id', $program_packages)->get();

        return view("pages.records.graphics_programs", [
            'programs' => $programs,
        ]);
    }


    public function other($start_params, $category_url = null) {
        $params = ['channel_unknown' => true, 'is_advertising' => false];

        $category = null;
        if ($category_url) {
            $category = Genre::where(['url' => $category_url])->first();

            if (!$category) {
                return redirect('https://staroetv.su/');
            }
            $params['other_category_id'] = $category->id;
        }
        $records_conditions = array_merge($start_params, $params);
        $categories = Genre::where(['type' => 'videos_other'])->get();
        foreach ($categories as $category_item) {
            $category_item->records_count = Record::where(['other_category_id' => $category_item->id])->count();
        }
        return view("pages.records.other", [
            'category' => $category,
            'categories' => $categories,
            'is_radio' => $start_params['is_radio'],
            'records_conditions' => $records_conditions,
        ]);
    }


    public function add($params) {
        if (PermissionsHelper::isBanned()) {
            return view('pages.errors.banned');
        }
        $can_upload = PermissionsHelper::allows('viupload');
        return view ("pages.forms.record", [
            'can_edit_all' => false,
            'can_upload' => $can_upload,
            'data' => $params,
            'record' => null,
            'channels' => Channel::with('logo', 'names')->orderBy('order', 'asc')->where($params)->get()
        ]);
    }

    public function edit($id) {
        if (PermissionsHelper::isBanned()) {
            return view('pages.errors.banned');
        }
        if (!auth()->user()) {
            return redirect('https://staroetv.su/');
        }
        $record = Record::with('channel','program', 'program.coverPicture')->find($id);
        if (!$record->can_edit) {
            return view('pages.errors.403');
        }
        $ts = $record->original_added_at_ts;
        $can_upload = PermissionsHelper::allows('viupload');
        $can_edit_all = PermissionsHelper::allows('viedit');
        $record->original_added_at_ts = $ts;
        return view ("pages.forms.record", [
            'data' => [
                'is_radio' => request()->has('is_radio') ? !!request()->input('is_radio') : $record->is_radio
            ],
            'can_upload' => $can_upload,
            'can_edit_all' => $can_edit_all,
            'record' => $record,
            'channels' => Channel::with('logo', 'names')->get()
        ]);
    }


    public function getInfo() {
        if (request()->has('vk_video_id')) {
            $vk_id = request()->input('vk_video_id');
            $token = config('tokens.vk');
            $data = json_decode(shell_exec(" curl 'https://api.vk.com/method/video.get?access_token=$token&v=5.130&videos=$vk_id&extended=1'"));
            return [
                'status' => 1,
                'data' => [
                    'vk_response' => $data
                ]
            ];
        } elseif (request()->has('youtube_video_id')) {
            $youtube_id = request()->input('youtube_video_id');
            $token = config('tokens.youtube');
             $data = json_decode(shell_exec("curl 'https://www.googleapis.com/youtube/v3/videos?id=$youtube_id&key=$token&part=snippet'"));
            return [
                'status' => 1,
                'data' => [
                    'youtube_response' => $data
                ]
            ];
        } else {
            return [
                'status' => 0,
                'text' => 'Не передан ID видео'
            ];
        }
    }

    public function save() {
        if (!PermissionsHelper::allows('viadd') || PermissionsHelper::isBanned()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $user = auth()->user();
        $record = new Record([
            'ucoz_id' => Record::max('ucoz_id') + 1,
            'is_from_ucoz' => false,
            'original_added_at' => Carbon::now(),
            'author_username' => $user->username,
            'author_id' => $user->id,
            'description' => '',
            'short_contents' => '',
            'views' => 0
        ]);
        if (PermissionsHelper::allows('vipremod')) {
            $record->pending = true;
        }
        return $this->fillData($record, true);
    }

    public function update($id) {
        $record = Record::find($id);
        if (!$record) {
            return [
                'status' => 0,
                'text' => 'Видео не найдено'
            ];
        }
        if (!$record->can_edit || PermissionsHelper::isBanned()) {
           return [
               'status' => 0,
               'text' => 'Ошибка доступа'
           ];
        };
        return $this->fillData($record, false);
    }

    private function fillData($record, $is_new = false) {

        $user = auth()->user();
        $is_radio = request()->input('is_radio', false) === true;
        $errors = [];

        $is_other = request()->input('is_other');
        if ($is_other && request()->has('other_category_id')) {
            $record->other_category_id = request()->input('other_category_id');
        } else {
            $record->other_category_id = null;
        }
        $record->is_advertising = request()->input('is_advertising', false);
        if (!$is_other && !$record->is_advertising) {
            if (!request()->input('channel.name') && !request()->input('channel_id') && !request()->input('channel.id') && request()->input('channel.unknown') !== true) {
                if ($is_radio) {
                    $errors['channel'] = "Выберите радиостанцию";
                } else {
                    if (!request()->input('is_advertising')) {
                        $errors['channel'] = "Выберите канал";
                    }
                }
            } elseif (request()->input('channel.unknown') !== true) {
                if (request()->input('channel.id') > 0) {
                    $record->channel_id = request()->input('channel.id');
                } elseif (request()->input('channel_id') > 0) {
                    $record->channel_id = request()->input('channel_id');
                } elseif (request()->input('channel.name') != "") {
                    $channel = Channel::firstOrNew(['name' => request()->input('channel.name')]);
                    if (!$channel->exists) {
                        $channel->fill(['author_id' => $user->id, 'is_regional' => false, 'is_abroad' => false, 'pending' => !PermissionsHelper::allows('contentapprove')]);
                    }
                    $channel->save();
                    $record->channel_id = $channel->id;
                }
            }
        } else {
            $record->channel_id = null;
        }
        $record->is_interprogram =  request()->input('is_interprogram', false);
        $record->is_clip =  request()->input('is_clip', false);

        if ($record->is_advertising) {
            $record->advertising_brand = request()->input('advertising_brand', '');
            if (request()->input('advertising_type') > 0) {
                $record->advertising_type = request()->input('advertising_type');
            }
        }
        $is_program_design = request()->input('is_program_design');

        if (!$is_other) {
            if (!request()->input('program.name') && !request()->input('program.id') && request()->input('program.unknown') !== true && (!$record->is_interprogram && !$record->is_clip  && !$record->is_advertising)) {
                $errors['program'] = "Выберите программу";
            } else {
                if ($is_program_design || (!$record->is_interprogram && !$record->is_advertising && !$record->is_clip)) {
                    if (request()->input('program.id') > 0) {
                        $record->program_id = request()->input('program.id');
                    } elseif(request()->input('program.name') != "") {
                        $program = Program::firstOrNew(['name' => request()->input('program.name'), 'channel_id' => $record->channel_id]);
                        if (!$program->exists) {
                            $program->fill(['author_id' => $user->id, 'cover' => '', 'channel_id' => $record->channel_id, 'pending' => !PermissionsHelper::allows('contentapprove')]);
                        }
                        $program->save();
                        $record->program_id = $program->id;
                    }
                } else {
                    $record->program_id = null;
                }
            }
        } else {
            $record->program_id = null;
        }
        if ( request()->input('program.unknown') === true) {
            $record->program_id = null;
        }
        if ($is_program_design) {
            $record->is_interprogram = true;
        }
        $has_uploaded_video = request()->input('record.use_own_player', false) && request()->has('record.source_path');
        if (request()->input('record.code') == "" && !$has_uploaded_video && !$record->use_own_player) {
            $errors['url'] = "Укажите ссылку";
        } else {
            $record->embed_code = request()->input('record.code');
        }
        if (request()->input('date.year') > 0) {
            $record->year = request()->input('date.year');
        }
        if (request()->input('date.month') > 0) {
            $record->month = request()->input('date.month');
        }
        if (request()->input('date.day') > 0) {
            $record->day = request()->input('date.day');
        }
        if (request()->input('date.year') > 0 && request()->input('date.month') > 0 && request()->input('date.day') > 0) {
            $record->date = Carbon::createFromDate(request()->input('date.year'), request()->input('date.month'), request()->input('date.day'));
        }
        if (request()->input('date.year_start') > 0) {
            $record->year_start = request()->input('date.year_start');
        }
        if (request()->input('date.year_end') > 0) {
            $record->year_end = request()->input('date.year_end');
        }
        if (request()->input('short_description') != "") {
            $record->short_description = request()->input('short_description');
        } else {
            $record->short_description = "";
        }
        if (request()->input('description') != "") {
            $record->description = request()->input('description');
        } else {
            $record->description = "";
        }
        if (request()->input('region') != "") {
            $record->region = request()->input('region');
        }
        if (request()->input('country') != "") {
            $record->country = request()->input('country');
        }
        if (request()->input('is_selected')) {
            $record->is_selected = !!request()->input('is_selected');
        }
        if ($record->is_interprogram) {
            if (request()->input('interprogram_package_id') > 0) {
                $record->interprogram_package_id = request()->input('interprogram_package_id');
            }
            if (request()->input('interprogram_type') > 0) {
                $record->interprogram_type = request()->input('interprogram_type');
            } else {
               // $errors['interprogram_type'] = "Выберите тип материала";
            }
        }
        $cover_url = null;
        if (request()->input('cover_id', 0) > 0) {
            $record->cover_id = request()->input('cover_id');
        } else {
            if (request()->input('cover') != "") {
                $cover_url = request()->input('cover');
            } elseif (request()->input('record.cover') != "") {
                $cover_url = request()->input('record.cover');
            } elseif (request()->has('record.covers') && is_array(request()->input('record.covers')) && count(request()->input('record.covers')) > 0) {
                $covers = request()->input('record.covers');
                $cover_url = $covers[count($covers) - 1];
            }
            if ($cover_url) {
                $cover = Picture::where(['url' => $cover_url])->first();
                if ($cover) {
                    $record->cover_id = $cover->id;
                } else {
                    $cover = new Picture();
                    $cover->loadFromURL($cover_url, md5($cover_url));
                    $cover->save();
                    $record->cover_id = $cover->id;
                }
            }
        }
        if (request()->input("record.title", "") != "") {
            $record->title = request()->input('record.title');
        } else {
            $record->title = $record->generateTitle();
        }
        if ($has_uploaded_video) {
            $record->use_own_player = true;
            $record->source_type = "local";
            $record->source_path = request()->input('record.source_path');
            if (request()->has('record.original_cover')) {
                $cover_url = request()->input('record.original_cover');
                $cover = Picture::where(['url' => $cover_url])->first();
                if ($cover) {
                    $record->cover_id = $cover->id;
                } else {
                    $cover = new Picture([
                        'url' => $cover_url
                    ]);
                    $cover->save();
                    $record->cover_id = $cover->id;
                }
            }
        }
        if ($record->exists && PermissionsHelper::allows('viedit')) {
            if (request()->has('original_added_at')) {
                $record->original_added_at = Carbon::parse(request()->input('original_added_at'));
            }
            if (request()->has('author_id')) {
                $record->author_id = request()->input('author_id');
            }
        }
        if (count($errors) > 0) {
            return [
                'status' => 0,
                'text' => 'В форме есть ошибки',
                'errors' => $errors
            ];
        }
        if (request()->has('storage_file')) {
            $filename = request()->input('storage_file');
            $original_path = '/storage/temp-upload/'.$filename;
            if (file_exists($original_path)) {
                $new_path = "/storage/staroetv-storage/videos/$filename";
                rename($original_path, $new_path);
                $record->use_own_player = true;
                $record->source_type = "local";
                $record->source_path = "/videos/$filename";
            }
        }
        $record->is_radio = $is_radio;
        if ($record->channel && $record->channel->is_radio) {
            $record->is_radio = true;
        }
        $record->save();
        $record->setSupposedDate();
        $cover = $record->cover;
        return [
            'status' => 1,
            'text' => $record->is_radio ? ($is_new ? 'Радиозапись добавлена' : 'Радиозапись обновлена') : ($is_new ? 'Видео добавлено' : 'Видео обновлено'),
            'data' => [
                'record' => $record
            ]
        ];
    }

    public function search($initial_params) {
        if (isset($initial_params['is_radio'])) {
            $records = Record::approved()->where(['is_radio' => $initial_params['is_radio']]);
        } else {
            $records = Record::approved();
        }
        $show_programs = true;
        $search = null;
        if (request()->has('search')) {
            $search = request()->input('search');
            $show_programs = false;
            $records->where(function($q) use ($search) {
                $q->where('title', 'LIKE', '%'. $search .'%');
                $q->orWhere('short_description', 'LIKE', '%'. $search.'%');
                $q->orWhere('description', 'LIKE', '%'. $search.'%');
                $q->orWhere('advertising_brand', 'LIKE', '%'. $search.'%');
            });
        }
        $params = request()->all();
        if (request()->has('channels')) {
            $channels = request()->input('channels');
            if ($channels) {
                if (!is_array($channels)) {
                    $channels = explode(",", $channels);
                }
                $records = $records->whereIn('channel_id', $channels);
            }
        }
        if (request()->has('channel_id')) {
            $records = $records->where('channel_id', request()->input('channel_id'));
        }
        iF (request()->has('exclude_ids')) {
            $ids = request()->input('exclude_ids');
            if (!is_array($ids)) {
                $ids = explode(",",$ids);
            }
            $records = $records->whereNotin('id', $ids);
        }
        if (request()->has('programs')) {
            $show_programs = false;
            $programs = request()->input('programs');
            if (!is_array($programs)) {
                $programs = explode(",", $programs);
            }
            $records = $records->whereIn('program_id', $programs);
        }
        if (request()->has('program_id')) {
            $show_programs = false;
            $program_id = request()->input('program_id');
            $records = $records->where('program_id', $program_id);
        }
        if (request()->has('is_interprogram')) {
            if (request()->input('is_interprogram')) {
                $show_programs = false;
            }
            $records = $records->where(['is_interprogram' => request()->input('is_interprogram')]);
        }
        if (request()->has('interprogram_type')) {
            $records = $records->where('interprogram_type', request()->input('interprogram_type'));
        }
        if (request()->has('interprogram_package_id')) {
            $records = $records->where('interprogram_package_id', request()->input('interprogram_package_id'));
        }
        if (request()->has('year')) {
            $records = $records->where('year', request()->input('year'));
        }
        if (request()->has('is_advertising')) {
            $records = $records->where(['is_advertising' => request()->input('is_advertising')]);
        }
        $has_dates = request()->has('date') || request()->has('date_day') || request()->has('date_month') || request()->has('date_year');
        if ($has_dates) {
            $records = $records->where(function($q) {
                $date_start = null;
                $date_end = null;
                $year = request()->has('date.year') ?  request()->input('date.year') : request()->input('date_year');
                $month = request()->has('date.year') ?  request()->input('date.month') : request()->input('date_month');
                $day = request()->has('date.year') ?  request()->input('date.day') : request()->input('date_day');
                if ($year) {
                    $q->where(["year" => $year]);
                    $date = Carbon::createFromDate($year, 1, 1);
                    $date_start = $date->copy()->startOfYear();
                    $date_end = $date->copy()->endOfYear();
                    if ($month) {
                        $date = Carbon::createFromDate($year, $month, 1);
                        $date_start = $date->copy()->startOfMonth();
                        $date_end = $date->copy()->endOfMonth();
                        if ($day) {
                            $date = Carbon::createFromDate($year, $month, $day);
                            $date_start = $date->copy()->startOfDay();
                            $date_end = $date->copy()->endOfDay();
                        }
                    }
                }
                if ($month) {
                    $q->where(["month" => $month]);
                }
                if ($day) {
                    $q->where(["day" => $day]);
                }
                if ($date_start && $date_end) {
                    $q->orWhere(function($sub) use ($date_start, $date_end) {
                        $sub->whereBetween('date', [$date_start, $date_end]);
                    });
                }
            });
        }
        $range_start = request()->has('dates_range.start') ? request()->input('dates_range.start')  : request()->input('dates_range_start');
        $range_end = request()->has('dates_range.end') ? request()->input('dates_range.end')  : request()->input('dates_range_end');

        if ($range_start || $range_end) {
            $records = $records->where(function ($q) use ($range_start, $range_end) {
                if ($range_start) {
                    $start = Carbon::createFromTimestamp($range_start);
                } else {
                    $start = Carbon::createFromDate(1950, 1, 1);
                }
                if ($range_end) {
                    $end = Carbon::createFromTimestamp($range_end);
                } else {
                    $end = Carbon::createFromDate(2015, 1, 1);
                }

                $q->whereBetween('date', [$start, $end]);
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
        }

        if (request()->has('sort')) {
            $sort = request()->input('sort');
            $order = request()->input('sort_order', 'desc');
            $records = $records->orderBy($sort, $order);
            $params['sort'] = $sort;
        } else {
            $records = $records->orderBy('id', 'desc');
        }
        $programs = null;
        $records_count = $records->count();

        if ($show_programs) {
            $recommended_program_ids = $records->whereNotNull('program_id')->get()->groupBy('program_id')->map(function($values) {
                return $values->count();
            })->sort()->reverse()->slice(0, 10)->keys();
            $programs = Program::whereIn('id', $recommended_program_ids)->get();
        }

        $records = $records->paginate(30);
        $data = [
            'programs' => $programs,
            'search' => $search,
            'params' => $params,
            'records' => $records->appends(request()->except('page')),
            'records_count' => $records_count,
            'is_radio' => isset($initial_params['is_radio']) ? $initial_params['is_radio'] : null
        ];
        if (request()->isMethod('post')) {
            return ['status' => 1, 'data' => $data];
        }
        return view("pages.records.search", $data);
    }

    public function massEdit() {
        if (PermissionsHelper::allows('viedit')) {
            $ids = request()->input('ids', []);
            $params = request()->input('params', []);
            Record::whereIn('id', $ids)->update($params);
            return ['status' => 1, 'text' => 'Обновлено'];
        } else {
            return ['status' => 0, 'text' => 'Ошибка доступа'];
        }
    }

    public function delete() {
        $record = Record::find(request()->input('record_id'));
        if (!$record) {
            return ['status' => 0, 'text' => 'Запись не найдена'];
        }
        if ($record->can_edit) {
            if ($record->use_own_player && strpos($record->source_path, "videos/") !== false) {
                $source_path = public_path($record->source_path);
                $do_not_delete = Record::where(['source_path' => $source_path])->where('id', '!=', $record->id)->count() > 0;
                if (!$do_not_delete) {
                    if (file_exists($source_path)) {
                        unlink($source_path);
                    }
                    if ( strpos($record->original_cover, "video_covers/") !== false) {
                        $screenshot_path = public_path($record->original_cover);
                        if (file_exists($screenshot_path)) {
                            unlink($screenshot_path);
                        }
                    }
                }
            }
            $record->delete();
            return ['status' => 1, 'text' => 'Удалено', 'redirect_to' => '/video'];
        } else {
            return ['status' => 0, 'text' => 'Ошибка доступа'];
        }
    }



    public function categories() {
        $categories = Genre::all();
        return [
            'status' => 1,
            'data' => [
                'categories' => $categories
            ]
        ];
    }

    public function download($id = null) {
        $record = Record::find($id ? $id : request()->input('record_id'));
        if (!$record) {
           return ['status' => 0, 'text' => 'Запись не найдена'];
        }
        //if (!$record->can_edit) {
           // return ['status' => 0, 'text' => 'Ошибка доступа'];
        //}
        preg_match('/iframe(.*?)src="(.*?)"/', $record->embed_code, $matches);
        if (!isset($matches[2]) || $matches[2] == "") {
            preg_match('/iframe(.*?)src=(.*?) (.*?)/', $record->embed_code, $matches);
        }
        if (!isset($matches[2]) || $matches[2] == "") {
            return ['status' => 0, 'text' => 'Не найден исходный URL видео для скачивания'];
        }
        $url = $matches[2];
        $path = "videos/" .$record->id.".mp4";
        $output_path = public_path($path);
        $command = "youtube-dl -f 'mp4' -i '$url' --output $output_path";
        shell_exec($command);
        if (file_exists($output_path)) {
            $record->use_own_player = true;
            $record->source_type = "local";
            $record->source_path = "/" . $path;
            $record->save();
            $screenshot_path = $this->makeScreenshot(public_path($record->source_path), null);
            $cover = Picture::firstOrNew([
                'url' => $screenshot_path
            ]);
            $cover->save();
            $record->cover_id = $cover->id;
            $record->save();
            return [
                'status' => 1,
                'text' => 'Видео загружено',
                'redirect_to' => $record->url
            ];
        } else {
            if (strpos($record->embed_code, "dailymotion") !== false) {
                $record->delete();
            }
            return ['status' => 0, 'text' => 'Не удалось скачать видео, возможно оно удалено'];
        }
    }

    public function ajax() {
        $conditions = request()->input('conditions');
        $records_data = RecordsHelper::get($conditions);
        $data =  [
            'ajax' => true,
            'records_data' => $records_data,
            'conditions' => $conditions
        ];
        if (request()->has('search')) {
            $data['search'] = request()->input('search');
        }
        if (request()->has('block_title')) {
            $data['block_title'] = request()->input('block_title');
        }
        if (request()->has('title_param')) {
            $data['title_param'] = request()->input('title_param');
        }
        return [
            'status' => 1,
            'data' => [
                'html' => view('blocks.records_list', $data)->render()
            ]
        ];
    }

    public function upload() {
        if (!PermissionsHelper::allows('viupload') || PermissionsHelper::isBanned()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $record = request()->file('record');
        if (!$record) {
            return [
                'status' => 0,
                'text' => 'Файл не найден'
            ];
        }
        $extension = $record->extension();
        $command = null;
        //$name = "record_".md5(time()).".".$extension;
        $is_radio = request()->input('is_radio', "0") == "1";
        $path = Storage::disk('public_data')->put($is_radio ? "radio-recordings" : "videos", $record);
        if ($extension !== "mp4" && !$is_radio) {
            $old_path = $path;
            $path = str_replace(".".$extension, ".mp4", $path);
            $command = "ffmpeg -y -i ".public_path($old_path)." -strict -2 ".public_path($path)." && rm ".public_path($old_path);
            exec('bash -c "exec nohup setsid '.$command.' > /dev/null 2>&1 &"');
        }
        $screenshot_path = null;
        if (!$is_radio) {
            $screenshot_path = $this->makeScreenshot(public_path($path));
        }
        return [
            'status' => 1,
            'text' => 'Запись загружена',
            'data' => [
                'command' => $command,
                'url' => "/".$path,
                'screenshot' => $screenshot_path,
            ]
        ];
    }

    public function afterUpload() {
        if (!PermissionsHelper::allows('viupload') || PermissionsHelper::isBanned()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $file_path = "/storage/uploads/".request()->input('server_upload_id');
        if (!file_exists($file_path)) {
            return [
                'status' => 0,
                'text' => 'Файл не найден'
            ];
        }
        $meta = json_decode(file_get_contents($file_path.".info"));
        $original_filename = $meta->MetaData->filename;
        $extension = last(explode(".", $original_filename));
        $command = null;
        $filename =  uniqid().".".$extension;
        $is_radio = request()->input('is_radio', false);
        $folder = $is_radio ? "radio-recordings" : "videos";
        $path = $folder."/".$filename;
        $full_path = "/storage/staroetv-storage/$path";
        shell_exec("mv $file_path $full_path");
        if ($extension !== "mp4" && !$is_radio) {
            $path = str_replace(".".$extension, ".mp4", $path);
            $mp4_path = str_replace(".".$extension, ".mp4", $full_path);
            $command = "ffmpeg -y -i ".$full_path." -strict -2 ".$mp4_path." && rm ".public_path($full_path);
            exec('bash -c "exec nohup setsid '.$command.' > /dev/null 2>&1 &"');
        }
        $screenshot_path = null;
        if (!$is_radio) {
            $screenshot_path = $this->makeScreenshot($full_path);
        }
        return [
            'status' => 1,
            'text' => 'Запись загружена',
            'data' => [
                'command' => $command,
                'url' => "/".$path,
                'screenshot' => $screenshot_path,
            ]
        ];
    }

    protected function makeScreenshot($path, $seconds = null) {
        $filename = pathinfo($path, PATHINFO_FILENAME);
        if ($seconds) {
            $screenshot_time = $seconds;
        } else {
            $frames = (int)shell_exec("ffprobe -v error -select_streams v:0 -show_entries stream=nb_frames -of default=nokey=1:noprint_wrappers=1 $path");
            $middle = floor($frames / 2);
            $fps = (int)shell_exec("ffprobe -v error -select_streams v -of default=noprint_wrappers=1:nokey=1 -show_entries stream=r_frame_rate $path");
            $fps = (int)explode("/", $fps)[0];
            if ($fps > 100 || $fps === 0) {
                $fps = 30;
            }
          //  $screenshot_time = $middle / $fps;
            $screenshot_time = ($frames / $fps) - 3;
        }
        $screenshot_path = "/pictures/video_covers/$filename.jpg";
        $screenshot_command = "ffmpeg -y -ss $screenshot_time -i '$path' -vframes 1 '".public_path($screenshot_path)."'";
         shell_exec($screenshot_command);
        return $screenshot_path;
    }

    public function setTelegramID() {
        $record = Record::find(request()->input('record_id'));
        if (!$record) {
            return [
                'status' => 0,
                'text' => 'Видео не найдено'
            ];
        }
        if (!$record->can_edit || PermissionsHelper::isBanned()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        };
        $record->telegram_id = request()->input('telegram_id');
        $record->save();
        return [
            'status' => 1,
            'text' => 'Видео будет воспроизводиться из Telegram',
            'redirect_to' => $record->url
        ];
    }

    public function screenshot() {
        $record = Record::find(request()->input('record_id'));
        if (!$record) {
            return [
                'status' => 0,
                'text' => 'Видео не найдено'
            ];
        }
        if (!$record->can_edit || PermissionsHelper::isBanned()) {
            //return [
            //    'status' => 0,
             //   'text' => 'Ошибка доступа'
            //];
        };
        if ($record->original_url && strpos($record->original_url, 'vk.com') !== false && !$record->source_path) {
            $token = config('tokens.vk');
            preg_match('/(.*?)video_ext.php\?oid=(.*?)&id=(.*?)&hash=(.*?)[&"](.*?)/', $record->embed_code, $matches);
            $user_id = $matches[2];
            $video_id = $matches[3];
            $hash = $matches[4];
            $full_id = $user_id."_".$video_id."_".$hash;
            $data = json_decode(file_get_contents("https://api.vk.com/method/video.get?access_token=$token&v=5.101&videos=$full_id&extended=1"));
            if (isset($data->response) && count($data->response->items) > 0) {
                $video = $data->response->items[0];
                $screenshot = $video->image[count($video->image) - 2]->url;
                $cover = Picture::firstOrNew([
                    'url' => $screenshot
                ]);
                $cover->save();
                $record->cover_id = $cover->id;
                $record->save();
                return [
                    'status' => 1,
                    'text' => 'Превью обновлено',
                    'redirect_to' => $record->url
                ];
            }
            return [
                'status' => 0,
                'text' => 'Видео не загружено на сайт'
            ];
        }
        if ($record->original_url && strpos($record->original_url, 'youtu') !== false && !$record->source_path) {
            $token = config('tokens.vk');
            preg_match('/youtube.com\/embed\/(.*?)"/', $record->embed_code, $output);
            if ($output && count($output) == 2) {
                $screenshot = 'https://i.ytimg.com/vi/'.$output[1].'/hqdefault.jpg';
                $cover = Picture::firstOrNew([
                    'url' => $screenshot
                ]);
                $cover->save();
                $record->cover_id = $cover->id;
                $record->save();
                return [
                    'status' => 1,
                    'text' => 'Превью обновлено',
                    'redirect_to' => $record->url
                ];
            }
            return [
                'status' => 0,
                'text' => 'Видео не загружено на сайт'
            ];
        }
        if (!$record->source_path) {
            return [
                'status' => 0,
                'text' => 'Видео не загружено на сайт',
            ];
        }
        $seconds = request()->input('seconds');
        if (!$seconds || $seconds == "") {
            $seconds = null;
        }
        $screenshot_path = $this->makeScreenshot("/storage/staroetv-storage".$record->source_path, $seconds);

        $cover = Picture::firstOrNew([
            'url' => $screenshot_path
        ]);
        $cover->save();
        $record->cover_id = $cover->id;
        $record->save();
        return [
            'status' => 1,
            'text' => 'Превью обновлено',
            'redirect_to' => $record->url
        ];
    }

    public function approve() {
        $record = Record::find(request()->input('id'));
        if (!$record) {
            return [
                'status' => 0,
                'text' => 'Запись не найдена'
            ];
        }
        $can_approve = PermissionsHelper::allows('viapprove');
        if ($can_approve) {
            $status = request()->input('status', !$record->pending);
            $record->pending = $status;
            $record->save();
            return [
                'status' => 1,
                'data' => [
                    'approved' => !$status
                ]
            ];
        } else {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
    }

    public function embed($id) {
        $record = Record::findOrFail($id);
        return view('pages.embed', ['record' => $record]);
    }

    public function calendar() {
        $years = Record::approved()->where(['is_radio' => false])->whereNotNull('year')->selectRaw('count(*) as count_year, year')->where('year', '>=', 1950)->groupBy('year')->orderBy('year', 'asc')->get();
        return view('pages.records.calendar', ['years' => $years]);
    }

    public function calendarYear($year) {
        $year_records = Record::approved()->select(['month', 'day', 'year'])->where(['is_radio' => false, 'is_advertising' => false])->where(['year' => $year])->get();
        $records_by_month = [];

        $month_names = DatesHelper::monthNames();
        foreach ($year_records as $year_record) {
            $month = $year_record->month  && $year_record->month > 0 ? $year_record->month : 0;
            if ($month > 12) {
                continue;
            }
            //$day = $year_record->day ? $year_record->day : '-';
            if (!isset($records_by_month[$month])) {
                $month_name = isset($month_names[$year_record->month - 1]) ? $month_names[$year_record->month - 1] : "-";
                $records_by_month[$month] = ['name' => $month > 0 ? $month_name . " ".$year : "Неизвестно", 'count' => 0];
            }
            //if (!isset($records_by_date[$month]['days'][$day])) {
            //    $records_by_date[$month]['days'][$day] = 0;
            //}

            $records_by_month[$month]['count']++;
        }
        ksort($records_by_month);
        //foreach ($records_by_date as $month => &$records) {
            //ksort($records['days']);
       // }
        return view('pages.records.calendar_year', ['year' => $year,'records_by_month' => $records_by_month]);
    }

    public function calendarMonth($year, $month) {
        $month_names = DatesHelper::monthNames();
        if ($year == "-") {
            $month_name = $month_names[$month - 1];
            $month_name_full = "Записи за " . mb_strtolower($month_names[$month - 1], "UTF-8");
            $month_name_parental_case = "";
            $month_records = Record::approved()->where(['is_radio' => false, 'is_advertising' => false])->where(['month' => $month])->get();
        } else {
            if ($month > 12) {
                return redirect("https://staroetv.su/video/calendar/$year");
            }
            if ($month < 0) {
                return;
            }
            if ($month > 0) {
                $month_name = $month_names[$month - 1];
                $month_name_full = "Записи за " . mb_strtolower($month_names[$month - 1], "UTF-8") . " $year";
                $month_name_parental_case = $this->month_names_parental_case[$month - 1];
                $month_records = Record::approved()->where(['is_radio' => false, 'is_advertising' => false])->where(['year' => $year, 'month' => $month])->get();
            } else {
                $month_name = "Неизвестный месяц";
                $month_name_full = "Записи за $year год с неизвестной датой";
                $month_name_parental_case = "";
                $month_records = Record::approved()->where(['is_radio' => false, 'is_advertising' => false])->where(['year' => $year, 'month' => null])->get();
            }
        }
        if ($year == "-") {
            $year = 2000;
        }
        $records_by_day = [];
        $channels = Channel::whereIn('id', $month_records->pluck('channel_id')->unique())->get();
        $channels_by_id = [];
        $date = Carbon::createFromDate($year, $month, 1);
        foreach ($channels as $channel) {
            $name = ChannelName::where(['channel_id' => $channel->id])->whereDate('date_start', '<=', $date)->whereDate('date_end', '>=', $date)->first();
            if (!$name) {
                $name = ChannelName::where(['channel_id' => $channel->id])->whereDate('date_start', '<=', $date)->where(['date_end' => null])->first();
            }
            if ($name) {
                if ($name->name != "") {
                    $channel->name = $name->name;
                }
                if ($name->logo) {
                    $channel->logo = $name->logo;
                }
            }
            $channel->logo_path = $channel->logo ? $channel->logo->url : '/pictures/logo-grey.svg';
            $channels_by_id[$channel->id] = $channel;
        }
        foreach ($month_records as $month_record) {
            $day = $month_record->day  && $month_record->day > 0 ? $month_record->day : 0;
            if (!isset($records_by_day[$day])) {
                $records_by_day[$day] = [];
            }
            if (!isset($records_by_day[$day][$month_record->channel_id])) {
                $records_by_day[$day][$month_record->channel_id] = [];
            }
            $records_by_day[$day][$month_record->channel_id][] = $month_record;
        }
        ksort($records_by_day);
        if (isset( $records_by_day[0])) {
            $unknown =  $records_by_day[0];
            unset($records_by_day[0]);
            $records_by_day = $records_by_day + [0 => $unknown];
        }


        return view('pages.records.calendar_month', [
            'channels_by_id' => $channels_by_id,
            'month_name' => $month_name,
            'month_name_full' => $month_name_full,
            'month_name_parental_case' => $month_name_parental_case,
            'year' => $year,
            'month' => $month,
            'records_by_day' => $records_by_day
        ]);
    }

    public function playlistAjax($id) {
        $record = Record::find($id);
        if (!$record || $record->pending) {
            return [
                'status' => 0,
                'text' => 'Запись не найдена',
            ];
        }
        ViewsHelper::increment($record, 'records');
        $player_code = trim(view('blocks/player', ['autoplay' => true, 'record' => $record])->render());
        $share_title = $record->title_without_tags;
        $share_url = "https://staroetv.su". ($record->is_radio ? "/radio/".$record->id : "/video/".$record->id);
        $record_info = trim(view('blocks/record_info', ['record' => $record])->render()).trim(view('blocks/share', ['share_title' => $share_title, 'share_url' => $share_url])->render());
        $comments = view("blocks/comments", ['ajax' => false, 'page' =>  1, 'conditions' => ['material_type' => Record::TYPE_VIDEOS, 'material_id' => $record->id]])->render();
        return [
            'status' => 1,
            'data' => [
                'record' => $record,
                'record_info' => $record_info,
                'player_code' => $player_code,
                'comments' => $comments
            ]
        ];
    }

    public function getYoutubeVideoIds($author_id) {
        $video_ids = Record::where(['author_id' => $author_id])->where('embed_code', 'LIKE', '%youtu%')->pluck('embed_code')->map(function($video_id) {
            $video_id = explode('embed/', $video_id)[1];
            $video_id = explode('"', $video_id)[0];
            return $video_id;
        });
        return [
            'status' => 1,
            'data' => [
                'video_ids' => $video_ids
            ]
        ];
    }

    public function getVideosForAuthor($author_id) {
        $videos = Record::where(['author_id' => $author_id])->select('title', 'id')->get();
        return [
            'status' => 1,
            'data' => [
                'videos' => $videos
            ]
        ];
    }

}
