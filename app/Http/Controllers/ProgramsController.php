<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ChannelName;
use App\Helpers\PermissionsHelper;
use App\InterprogramPackage;
use App\Program;
use App\Record;
use Carbon\Carbon;

class ProgramsController extends Controller {

    public function show($id) {
        $program = Program::find($id);
        return view("pages.programs.show", [
            'program' => $program,
            'records' => $program->records,
        ]);
    }

    public function add($channel_id) {
        if (!PermissionsHelper::allows('programsown') && !PermissionsHelper::allows('programs')) {
            return view("pages.errors.403");
        }
        $channel = Channel::find($channel_id);
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

    public function save() {
        if (!PermissionsHelper::allows('programsown') && !PermissionsHelper::allows('programs')) {
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
            'name' => 'sometimes|min:1',
            'description' => 'sometimes',
            'logo_id' => 'sometimes',
            'is_regional' => 'sometimes',
            'is_federal' => 'sometimes',
            'is_abroad' => 'sometimes',
            'country' => 'sometimes',
            'city' => 'sometimes',
            'is_radio' => 'sometimes',
            'url' => 'sometimes'
        ]);

        foreach(['is_regional', 'is_abroad', 'is_federal'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = ($data[$key] === "true" || $data[$key] === true) ? 1 : 0;
            }
        }
        $channel->fill($data);
        $channel->save();
        if (request()->has('channel_names')) {
            $names = request()->input('channel_names');
            $names = json_decode($names);
            $ids = [];
            foreach ($names as $name) {
                $start = Carbon::parse($name->date_start);
                $end = Carbon::parse($name->date_end);

                $name_data = [
                    'channel_id' => $channel->id,
                    'name' => $name->name,
                    'logo_id' => $name->logo_id,
                    'date_start' => !$start->isToday() ? $start  : null,
                    'date_end' => !$end->isToday() ? $end : null
                ];
                if (!isset($name->id)) {
                    $name = new ChannelName($name_data);
                    $name->save();
                    $ids[] = $name->id;
                } else {
                    $ids[] = $name->id;
                    $name = ChannelName::find($name->id);
                    $name->fill($name_data);
                    $name->save();
                }
            }
            ChannelName::where(['channel_id' => $channel->id])->whereNotIn('id', $ids)->delete();
        }
        return [
            'status' => 1,
            'text' => 'Информация о канале обновлена',
            'redirect_to' => '/channels/'.$channel->id.'/edit'
        ];
    }

    public function merge() {
        $original = Program::find(request()->input('original_id'));
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

        $merged = Program::find(request()->input('merged_id'));
        if (!$merged) {
            return [
                'status' => 0,
                'text' => 'Программа для объединения не найдена'
            ];
        }
        Record::where(['program_id' => $original->id])->update(['program_id' => $merged->id]);
        $original->delete();
        return [
            'status' => 1,
            'text' => 'Программа объединена',
            'redirect_to' => '/video'
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
}
