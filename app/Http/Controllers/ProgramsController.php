<?php

namespace App\Http\Controllers;

use App\AdditionalChannel;
use App\Channel;
use App\ChannelName;
use App\Genre;
use App\Helpers\PermissionsHelper;
use App\Helpers\ViewsHelper;
use App\InterprogramPackage;
use App\Program;
use App\Record;
use Carbon\Carbon;
use \Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ProgramsController extends Controller {

    public function index($params) {
        $page_title = "Передачи";
        $is_radio = $params['is_radio'];
        $category = Genre::where(['url' => request()->input('category')])->first();
        if (!$category) {
            //return redirect(!$is_radio ? '/video' : '/radio');
        }

        $channel_ids  = Channel::where(['is_radio' => $is_radio])->where(['is_regional' => false])->where(['is_abroad' => false])->pluck('id');
        $programs = Program::where(['pending' => false])->withCount('records')->whereIn('channel_id', $channel_ids);

        if ($category) {
            $programs = $programs->where(['genre_id' => $category->id]);
            $page_title = $category->name;
        }
        $program_ids = $programs->orderBy('records_count', 'desc')->pluck('id');

        $programs = $programs->orderBy('records_count', 'desc')->limit(15)->get();
        $records_conditions = [
            'is_radio' => $is_radio,
            'is_interprogram' => false,
            'program_id_in' => $program_ids
        ];

        return view('pages.records.programs', [
            'params' => $params,
            'page_title' => $page_title,
            'records_conditions' => $records_conditions,
            'programs' => $programs,
            'category' => $category,
        ]);
    }

    public function loadAll($params) {
        $is_radio = $params['is_radio'];
        $channel_ids  = Channel::where(['is_radio' => $is_radio])->where(['is_regional' => false])->where(['is_abroad' => false])->pluck('id');
        $programs = Program::where(['pending' => false])->withCount('records')->whereIn('channel_id', $channel_ids);
        $category = Genre::where(['url' => request()->input('category')])->first();
        if ($category) {
            $programs = $programs->where(['genre_id' => $category->id]);
        }

        $programs = $programs->orderBy('records_count', 'desc')->get();
        $programs = $programs->slice(12);
        return [
            'status' => 1,
            'data' => [
                'dom' => [
                    [
                        'replace' => '.programs-list--all',
                        'html' => view("blocks/programs_list", ['programs' => $programs])->render()
                    ],
                    [
                        'replace' => '.programs-list__show-all',
                        'html' => ''
                    ]
                ]
            ]
        ];
    }


    public function show($id) {
        $program = Program::where(['url' => $id])->first();
        if (!$program) {
            $program = Program::find($id);
        }
        if (!$program) {
            return redirect("https://staroetv.su/");
        }
        ViewsHelper::increment($program, 'programs');

        $conditions = [ 'program_id' => $program->id, 'is_interprogram' => false];
        if ($program->channel) {
            $conditions['is_radio'] = $program->channel->is_radio;
        }
        $unknown = $program->url == 'unknown-program';
        if ($unknown) {
            $conditions['is_interprogram'] = false;
            $conditions['is_advertising'] = false;
            $conditions['is_clip'] = false;
            unset($conditions['program_id']);
            $conditions['program_id_in'] = [$program->id, null];
        }

        $program->original_name = $program->name;
        $channel = $program->channel;
        if (request()->has('from')) {
            $from_channel_id = request()->input('from');
            $additional_channel_data = AdditionalChannel::where(['program_id' => $program->id, 'channel_id' => $from_channel_id])->first();
            if ($additional_channel_data && $additional_channel_data->channel) {
                $channel = $additional_channel_data->channel;
                if ($additional_channel_data->title != "") {
                    $program->name = $additional_channel_data->title;
                }
            }
        }
        return view("pages.programs.show", [
            'program' => $program,
            'unknown' => $unknown,
            'channel' => $channel,
            'records_conditions' => $conditions
        ]);
    }

    public function add($channel_id) {
        if (!PermissionsHelper::allows('programsown') && !PermissionsHelper::allows('programs')) {
            return view("pages.errors.403");
        }
        $channel = Channel::findByIdOrUrl($channel_id);
        if (!$channel || !$channel->can_edit) {
            return view("pages.errors.403");
        }
        return view("pages.forms.program", [
            'program' => null,
            'channel' => $channel,
        ]);
    }

    public function edit($id) {
        $all_channels = null;
        $program = Program::find($id);
        if (!$program) {
            return redirect("https://staroetv.su/");
        }
        if (!$program->can_edit || ($program->channel && !$program->channel->can_edit)) {
            return view("pages.errors.403");
        }
        if (request()->has('all_programs')) {
            $all_programs = Program::where('id','!=', $program->id)->get();
        } elseif ($program->channel) {
            $all_programs = $program->channel->programs->filter(function ($program_item) use ($program) {
                return $program_item->id != $program->id;
            });
        } else {
            $all_channels = Channel::pluck('name', 'id');
            $all_programs = [];
        }
        return view("pages.forms.program", [
            'program' => $program,
            'channel' => $program->channel,
            'all_channels' => $all_channels,
            'all_programs' => $all_programs
        ]);
    }

    public function save($channel_id) {
        if (!PermissionsHelper::allows('programsown') && !PermissionsHelper::allows('programs')) {
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
        $program = new Program();
        $program->channel_id = $channel->id;
        return $this->fillData($program);
    }

    public function update($id) {
        $program = Program::find($id);
        if (!$program) {
            return [
                'status' => 0,
                'text' => 'Программа не найдена'
            ];
        }
        if (!$program->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        return $this->fillData($program);
    }

    private function fillData($program) {
        $data = request()->validate([
            'name' => 'required|min:1',
            'description' => 'sometimes',
            'date_of_start' => 'sometimes',
            'date_of_closedown' => 'sometimes',
            'genre_id' => 'sometimes',
            'cover_id' => 'sometimes',
            'url' => 'sometimes',
            'channel_id' => 'sometimes'
        ]);
        if (request()->has('url') && request()->input('url') != '') {
            $same_url_program = Program::where(['url' => request()->input('url')])->first();
            if ($same_url_program && $same_url_program->id != $program->id) {
                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'url' => ['Программа с таким URL уже существует'],
                ]);
                throw $error;
            }
        }
        if (isset($data['date_of_start'])) {
            $data['date_of_start'] = Carbon::parse($data['date_of_start']);
        } else {
            $data['date_of_start'] = null;
        }
        if (isset($data['date_of_closedown'])) {
            $data['date_of_closedown'] = Carbon::parse($data['date_of_closedown']);
        } else {
            $data['date_of_closedown'] = null;
        }
        $program->fill($data);
        $program->author_id = auth()->user()->id;
        $program->save();
        if (request()->has('additional_channels')) {
              $additional_channels = request()->input('additional_channels');
              $additional_channels = json_decode($additional_channels);
              $old_ids = AdditionalChannel::where(['program_id' => $program->id])->pluck('id')->toArray();
              $new_ids = [];
              if (is_array($additional_channels)) {
                  foreach ($additional_channels as $additional_channel) {
                      $data = AdditionalChannel::firstOrNew([
                          'program_id' => $program->id,
                          'channel_id' => $additional_channel->channel_id
                      ]);
                      $data->title = $additional_channel->title;
                      $data->date_start = $additional_channel->date_start;
                      $data->date_end = $additional_channel->date_end;
                      $data->save();
                      $new_ids[] = $data->id;
                  }
                  $ids_to_delete = array_diff($old_ids, $new_ids);
                  AdditionalChannel::whereIn('id', $ids_to_delete)->delete();
              }

        }
        Cache::clear('programs_channels___names_'.$program->id);
        return [
            'status' => 1,
            'text' => 'Информация о программе обновлена',
            'redirect_to' => '/programs/'.$program->id.'/edit'
        ];
    }

    public function merge() {
        $original = Program::find(request()->input('original_id'));
        $merged = null;
        if (!$original) {
            return [
                'status' => 0,
                'text' => 'Программа не найдена'
            ];
        }
        if (!$original->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        if (request()->input('is_interprogram')) {
            Record::where(['program_id' => $original->id])->update(['program_id' => null, 'is_interprogram' => true]);
        } else {
            $merged = Program::find(request()->input('merged_id'));
            if (!$merged) {
                return [
                    'status' => 0,
                    'text' => 'Программа для объединения не найдена'
                ];
            }
            Record::where(['program_id' => $original->id])->update(['program_id' => $merged->id]);
        }
        $original->delete();
        return [
            'status' => 1,
            'text' => 'Программа объединена',
            'redirect_to' => $merged ? '/programs/'.$merged->id.'/edit' : '/video'
        ];
    }



    public function delete() {
        $program = Program::find(request()->input('program_id'));
        if (!$program) {
            return [
                'status' => 0,
                'text' => 'Канал не найден'
            ];
        }
        if (!$program->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        Record::where(['program_id' => $program->id])->update(['program_id' => -1]);
        $program->delete();
        if (request()->input('_from_confirm_form')) {
            return [
                'status' => 1,
                'text' => 'Программа удалена',
                'redirect_to' => '/video'
            ];
        } else {
            return [
                'status' => 1,
                'text' => 'Программа удалена'
            ];
        }
    }

    public function editList($channel_id) {
        if (!PermissionsHelper::allows('programs')) {
            return redirect('https://staroetv.su/');
        }

        $channel = Channel::findByIdOrUrl($channel_id);
        if (!$channel || !$channel->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $programs = $channel->programs;
        if (!PermissionsHelper::allows('contentapprove')) {
            $programs = $programs->where(['pending' => false]);
        }
        $programs = $programs->sortBy('order')->values();
        unset($channel->programs);
        $genres = Genre::where(['type' => 'programs'])->get();
        return view('pages.forms.programs-list', [
            'channel' => $channel,
            'programs' => $programs,
            'genres' => $genres
        ]);
    }

    public function saveList($channel_id) {
        if (!PermissionsHelper::allows('programs')) {
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
        $order = request()->input('order');
        $i = 0;
        foreach ($order as $genre_id => $programs) {

            foreach ($programs as $program_id) {
                $program = Program::find($program_id);
                if ($program && $program->channel_id == $channel->id) {
                    $program->order = $i;
                    $program->genre_id = $genre_id != -1 ? $genre_id : null;
                    $program->save();
                }
                $i++;
            }
        }
        return [
            'status' => 1,
            'text' => 'Обновлено'
        ];
    }

    public function approve() {
        $program = Program::find(request()->input('id'));
        if (!$program) {
            return [
                'status' => 0,
                'text' => 'Программа не найдена'
            ];
        }
        $can_approve = PermissionsHelper::allows('contentapprove');
        if ($can_approve) {
            $status = request()->input('status', !$program->pending);
            $program->pending = $status;
            $program->save();
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

    public function autocomplete() {
        $count = 30;
        $programs = Program::select('id', 'name')->orderBy('id', 'asc');
        if (request()->has('term')) {
            $programs = $programs->where('name', 'LIKE', '%'.request()->input('term').'%');
        }
        $total = $programs->count();
        $page = request()->input('page', 1);
        $programs = $programs->limit($count)->offset($count * ($page - 1))->get();
        return [
            'status' => 1,
            'data' => [
                'total' => $total,
                'programs' => $programs
            ]
        ];
    }

}
