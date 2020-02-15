<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ChannelName;
use App\Genre;
use App\Helpers\PermissionsHelper;
use App\InterprogramPackage;
use App\Program;
use App\Record;
use Carbon\Carbon;

class ProgramsController extends Controller {

    public function show($id) {
        $program = Program::find($id);
        if (!$program) {
            $program = Program::where(['url' => $id])->first();
        }
        if (!$program) {
            return redirect("/");
        }
        $records = Record::where(['program_id' => $program->id])->paginate(60);
        $records_count =  Record::where(['program_id' => $program->id])->count();
        return view("pages.programs.show", [
            'program' => $program,
            'records' => $records,
            'records_count' => $records_count
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
        if (!$program || !$program->can_edit) {
            return view("pages.errors.403");
        }
        if (!$program->channel || !$program->channel->can_edit) {
            return view("pages.errors.403");
        }
        $all_programs = $program->channel->programs->filter(function($program_item) use ($program) {
            return $program_item->id != $program->id;
        });
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
        $program->save();
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
        $programs = $channel->programs->sortBy('order')->values();
        unset($channel->programs);
        $genres = Genre::all();
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
}
