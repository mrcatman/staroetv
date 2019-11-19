<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Helpers\PermissionsHelper;
use App\Picture;
use App\Program;
use App\Record;
use Carbon\Carbon;
use function foo\func;

class RecordsController extends Controller {

    public function show($id) {
        $record = Record::where(['id' => $id])->first();
        $related_program = null;
        $related_channel = null;
        if ($record->program) {
            $related_program = Record::where(['program_id' => $record->program_id])->inRandomOrder()->limit(6)->get();
        }
        if ($record->channel) {
            $related_channel = Record::where(['channel_id' => $record->channel_id])->inRandomOrder()->limit(6)->get();
        }
        return view("pages.records.show", [
            'record' => $record,
            'related_program' => $related_program,
            'related_channel' => $related_channel,
        ]);
    }

    public function showOld($id) {
        $record = Record::where(['ucoz_id' => $id])->first();
        $related_program = null;
        $related_channel = null;
        if ($record->program) {
            $related_program = Record::where(['program_id' => $record->program_id])->inRandomOrder()->limit(6)->get();
        }
        if ($record->channel) {
            $related_channel = Record::where(['channel_id' => $record->channel_id])->inRandomOrder()->limit(6)->get();
        }
        return view("pages.records.show", [
            'record' => $record,
            'related_program' => $related_program,
            'related_channel' => $related_channel,
        ]);
    }


    public function index($params) {
        $federal = Channel::where(['is_federal' => true])->where($params)->orderBy('order', 'ASC')->get();
        $regional = Channel::where(['is_regional' => true])->where($params)->orderBy('order', 'ASC')->get();
        $abroad = Channel::where(['is_abroad' => true])->where($params)->orderBy('order', 'ASC')->get();
        $other = Channel::where(['is_federal' => false, 'is_regional' => false, 'is_abroad' => false])->where($params)->orderBy('order', 'ASC')->get();
        $cities = [];
        foreach ($regional as $channel) {
            if (!isset($cities[$channel->city])) {
                $cities[$channel->city] = 1;
            } else {
                $cities[$channel->city]++;
            }
        }
        arsort($cities);
         return view("pages.records.index", [
            'cities' => $cities,
            'data' => $params,
            'federal' => $federal,
            'regional' => $regional,
            'abroad' => $abroad,
            'other' => $other,
        ]);
    }

    public function add($params) {
        if (PermissionsHelper::isBanned()) {
            return view('pages.errors.banned');
        }
        return view ("pages.forms.record", [
            'data' => $params,
            'record' => null,
            'channels' => Channel::with('logo', 'names')->where($params)->get()
        ]);
    }

    public function edit($id) {
        if (PermissionsHelper::isBanned()) {
            return view('pages.errors.banned');
        }
        $record = Record::with('channel','program', 'program.coverPicture')->find($id);
        if (!$record->can_edit) {
            return view('pages.errors.403');
        }
        return view ("pages.forms.record", [
            'data' => [
                'is_radio' => $record->is_radio
            ],
            'record' => $record,
            'channels' => Channel::with('logo', 'names')->get()
        ]);
    }


    public function getInfo() {
        if (request()->has('vk_video_id')) {
            $vk_id = request()->input('vk_video_id');
            $token = config('tokens.vk');
            $data = json_decode(file_get_contents("https://api.vk.com/method/record.get?access_token=$token&v=5.101&videos=$vk_id&extended=1"));
            return [
                'status' => 1,
                'data' => [
                    'vk_response' => $data
                ]
            ];
        } elseif (request()->has('youtube_video_id')) {
            $youtube_id = request()->input('youtube_video_id');
            $token = config('tokens.youtube');
            $data = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/videos?id=$youtube_id&key=$token&part=snippet"));
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
            'is_from_ucoz' => false,
            'original_added_at' => Carbon::now(),
            'author_username' => $user->username,
            'author_id' => $user->id,
            'description' => '',
            'short_contents' => '',
            'views' => 0
        ]);
        return $this->fillData($record);
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
        return $this->fillData($record);
    }

    private function fillData($record) {
        $user = auth()->user();
        $is_radio = request()->input('is_radio', false);
        $errors = [];
        if (!request()->input('channel.name') && request()->input('channel.unknown') !== 'true') {
            if ($is_radio) {
                $errors['channel'] = "Выберите радиостанцию";
            } else {
                $errors['channel'] = "Выберите канал";
            }
        } else {
            if (request()->input('channel.id') > 0) {
                $record->channel_id = request()->input('channel.id');
            } else {
                $channel = new Channel(['author_id' => $user->id, 'name' => request()->input('channel.name'),'is_regional' => false, 'is_abroad' => false, 'pending' => true]);
                $channel->save();
                $record->channel_id = $channel->id;
            }
        }
        $is_interprogram = request()->input('is_interprogram', false);
        $record->is_interprogram = $is_interprogram === "true" || $is_interprogram == 1;
        if (!request()->input('program.name') && request()->input('program.unknown') !== 'true' && !$is_interprogram) {
            $errors['program'] = "Выберите программу";
        } else {
            if (!$record->is_interprogram) {
                if (request()->input('program.id') > 0) {
                    $record->program_id = request()->input('program.id');
                } else {
                    $program = new Program(['author_id' => $user->id, 'name' => request()->input('program.name'), 'cover' => '', 'channel_id' => $record->id, 'pending' => true]);
                    $program->save();
                    $program->program_id = $program->id;
                }
            }
        }
        if (!request()->input('record.code')) {
            $errors['url'] = "Укажите ссылку на видео";
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
        if (request()->input('short_description') != "") {
            $record->short_description = request()->input('short_description');
        }

        if ($record->is_interprogram) {
            if (request()->input('interprogram_package_id') > 0) {
                $record->interprogram_package_id = request()->input('interprogram_package_id');
            }
        }
        if (request()->input('cover') != "") {
            $cover = Picture::where(['url' => request()->input('cover')])->first();
            if ($cover) {
                $record->cover_id = $cover->id;
            } else {
                $cover = new Picture();
                $cover->loadFromURL(request()->input('cover'), md5(request()->input('cover')));
                $cover->save();
                $record->cover_id = $cover->id;
            }
        }
        $record->title = $record->generateTitle();
        if (count($errors) > 0) {
            return [
                'status' => 0,
                'text' => 'В форме есть ошибки',
                'errors' => $errors
            ];
        }
        $record->save();
        return [
            'status' => 1,
            'text' => $is_radio ? 'Радиозапись добавлена' : 'Видео добавлено',
            'data' => [
                'record' => $record
            ]
        ];
    }

    public function search($params) {
        $is_radio = $params['is_radio'];
        $params = request()->all();
        $search = request()->input('search');
        $records = Record::where(['is_radio' => $is_radio])->where('title', 'LIKE', '%'.$search.'%');
        if (request()->has('channels')) {
            $channels = request()->input('channels');
            if (!is_array($channels)) {
                $channels = explode(",", $channels);
            }
            $records = $records->whereIn('channel_id', $channels);
        }
        if (request()->has('programs')) {
            $programs = request()->input('programs');
            if (!is_array($programs)) {
                $programs = explode(",", $programs);
            }
            $records = $records->whereIn('program_id', $programs);
        }
        if (request()->has('is_interprogram')) {
            $records = $records->where(['is_interprogram' => request()->input('is_interprogram') === "true"]);
        }
        if (request()->has('is_advertising')) {
            $records = $records->where(['is_advertising' => request()->input('is_advertising') === "true"]);
        }
        if (request()->has('date')) {
            $records = $records->where(function($q) {
                $date_start = null;
                $date_end = null;
                if (request()->has('date.year')) {
                    $q->where(["year" => request()->input('date.year')]);
                    $date = Carbon::createFromDate(request()->input('date.year'), 1, 1);
                    $date_start = $date->copy()->startOfYear();
                    $date_end = $date->copy()->endOfYear();
                    if (request()->has('date.month')) {
                        $date = Carbon::createFromDate(request()->input('date.year'), request()->input('date.month'), 1);
                        $date_start = $date->copy()->startOfMonth();
                        $date_end = $date->copy()->endOfMonth();
                        if (request()->has('date.day')) {
                            $date = Carbon::createFromDate(request()->input('date.year'), request()->input('date.month'), request()->input('date.day'));
                            $date_start = $date->copy()->startOfDay();
                            $date_end = $date->copy()->endOfDay();
                        }
                    }
                }
                if (request()->has('date.month')) {
                    $q->where(["month" => request()->input('date.month')]);
                }
                if (request()->has('date.day')) {
                    $q->where(["day" => request()->input('date.day')]);
                }
                if ($date_start && $date_end) {
                    $q->orWhere(function($sub) use ($date_start, $date_end) {
                        $sub->whereBetween('date', [$date_start, $date_end]);
                    });
                }
            });
        }
        if (request()->has('dates_range')) {
            if (request()->has('dates_range.start') && request()->has('dates_range.end')) {
                $records = $records->where(function($q) {
                    $start = Carbon::createFromTimestamp(request()->input('dates_range.start'));
                    $end = Carbon::createFromTimestamp(request()->input('dates_range.end'));
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
            }
        }
        if (request()->has('sort')) {
            $sort = request()->input('sort');
            $order = request()->input('sort_order', 'desc');
            $records = $records->orderBy($sort, $order);
        }
        $records_count = $records->count();
        $records = $records->paginate(30);
        $data = [
            'params' => $params,
            'records' => $records->appends(request()->except('page')),
            'records_count' => $records_count,
            'is_radio' => $is_radio
        ];
        if (request()->isMethod('post')) {
            return $data;
        }
        return view("pages.records.search", $data);
    }
}
