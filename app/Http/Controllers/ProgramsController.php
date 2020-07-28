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

class ProgramsController extends Controller {

    public function index() {
        $category = Genre::where(['url' => request()->input('category')])->first();
        if (!$category) {
            return redirect('/video');
        }
        $unnecessary_channel_ids = Channel::where(['is_radio' => true])->orWhere(['is_regional' => true])->orWhere(['is_abroad' => true])->pluck('id');
        $programs = Program::where(['pending' => false])->withCount('records')->whereNotIn('channel_id', $unnecessary_channel_ids)->where(['genre_id' => $category->id])->orderBy('records_count', 'desc')->get();
        $records_conditions = [
            'is_interprogram' => false,
            'program_id_in' => $programs->pluck('id')
        ];
        return view('pages.records.programs', [
            'records_conditions' => $records_conditions,
            'programs' => $programs,
            'category' => $category,
        ]);
    }


    public function show($id) {
        $program = Program::find($id);
        if (!$program) {
            $program = Program::where(['url' => $id])->first();
        }
        if (!$program) {
            return redirect("/");
        }
        ViewsHelper::increment($program, 'programs');
        return view("pages.programs.show", [
            'program' => $program,
            'records_conditions' => ['program_id' => $program->id, 'is_interprogram' => false]
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
        $program = Program::find($id);
         if (!$program || !$program->channel) {
            return redirect("/");
        }
        if (!$program->can_edit || !$program->channel->can_edit) {
            return view("pages.errors.403");
        }
        if (request()->has('all_programs')) {
            $all_programs = Program::where('id','!=', $program->id)->get();
        } else {
            $all_programs = $program->channel->programs->filter(function ($program_item) use ($program) {
                return $program_item->id != $program->id;
            });
        }
        return view("pages.forms.program", [
            'program' => $program,
            'channel' => $program->channel,
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
            'url' => 'sometimes'
        ]);
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
            return redirect('/');
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

}
