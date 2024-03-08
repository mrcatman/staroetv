<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Helpers\ViewsHelper;
use App\HistoryEvent;
use App\HistoryEventBlock;
use App\Program;
use Carbon\Carbon;

class HistoryEventsController extends Controller {

    public function add() {
        if (!PermissionsHelper::allows('historyown') && !PermissionsHelper::allows('history')) {
            return view("pages.errors.403");
        }

        return view("pages.forms.history-event", [
            'event' => null,
        ]);
    }


    public function save() {
        if (!PermissionsHelper::allows('historyown') && !PermissionsHelper::allows('history')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $event = new HistoryEvent();
        if (!PermissionsHelper::allows('historyapprove')) {
            $event->pending = true;
        }
        return $this->fillData($event);
    }


    public function edit($id) {
        $event = HistoryEvent::find($id);
        if (!$event || !$event->can_edit) {
            return view("pages.errors.403");
        }
        foreach ($event->blocks as $block) {
            $block->loadRecords();
        }
        return view("pages.forms.history-event", [
            'event' => $event,
        ]);
    }

    public function update($id) {
        $event = HistoryEvent::find($id);
        if (!$event || !$event->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        return $this->fillData($event);
    }

    public function delete() {
        $event = HistoryEvent::find(request()->input('event_id'));
        if (!$event || !$event->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $event->delete();
        return [
            'status' => 1,
            'text' => 'Удалено',
            'redirect_to' => '/'
        ];
    }

    private function fillData($event) {
       // HistoryEvent::where('id', '>', '0')->delete();
        //HistoryEventBlock::where('id', '>', '0')->delete();
        $data = request()->validate([
            'title' => 'required|min:1',
            'short_description' => 'sometimes',
            'url' => 'sometimes',
            'description' => 'sometimes',
            'date' => 'sometimes',
            'date_end' => 'sometimes',
            'cover_id' => 'sometimes'
        ]);
        $event->user_id = auth()->user()->id;
        $event->fill($data);
        $event->date = Carbon::parse($data['date']);
        if (isset($data['date_end'])) {
            $event->date_end = Carbon::parse($data['date_end']);
        }
        $event->save();
        $blocks = json_decode(request()->input('records'));
        $index = 0;
        $old_block_ids = $event->blocks->pluck('id');
        $new_block_ids = collect([]);
        foreach ($blocks as $block) {
            if (!isset($block->id)) {
                $event_block = new HistoryEventBlock([
                    'event_id' => $event->id,
                ]);
            } else {
                $event_block = HistoryEventBlock::find($block->id);
            }
            $event_block->order = $index;
            $event_block->video_ids = isset($block->records) ? $block->records : [];
            $event_block->description = $block->description;
            $event_block->save();
            $index++;
            $new_block_ids->push($event_block->id);
        }
        $blocks_to_delete = $old_block_ids->diff($new_block_ids);
        HistoryEventBlock::whereIn('id', $blocks_to_delete)->delete();
        return [
            'status' => 1,
            'text' => $event->pending ? 'Добавлено. Подборка появится на сайте после премодерации' : 'Добавлено',
            'redirect_to' => '/events/'.$event->id. '/edit'
        ];
    }

    public function show($url) {
        $event = HistoryEvent::where(['url' => $url])->first();
        if (!$event) {
            $event = HistoryEvent::find($url);
        }
        if (!$event) {
            return redirect("https://staroetv.su/");
        }
        ViewsHelper::increment($event, 'events');
        foreach ($event->blocks as $block) {
            $block->loadRecords();
        }

        return view("pages.events.show", [
            'event' => $event,
        ]);
    }

    public function approve() {
        $event = HistoryEvent::find(request()->input('id'));
        if (!$event) {
            return [
                'status' => 0,
                'text' => 'Подборка не найдена'
            ];
        }
        $can_approve = PermissionsHelper::allows('historyapprove');
        if ($can_approve) {
            $status = request()->input('status', !$event->pending);
            $event->pending = $status;
            $event->save();
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

    public function index() {
        $events = HistoryEvent::approved()->orderBy('id', 'desc');
        $page = request()->input('page', 1);
        $big_event = null;
        $first_events = null;
        if ($page == 1 && $events->count() >= 4) {
            $big_event =  HistoryEvent::approved()->orderBy('id', 'desc')->first();
            $first_events = HistoryEvent::approved()->orderBy('id', 'desc')->where('id','!=', $big_event->id)->limit(3)->get();
            $events = $events->where('id','!=', $big_event->id);
            $events = $events->whereNotIn('id', $first_events->pluck('id'));
        }
        $events = $events->paginate(24);
        $can_add = PermissionsHelper::allows('historyown');
        return view("pages.events.index", [
            'first_events' => $first_events,
            'big_event' => $big_event,
            'page' => $page,
            'can_add' => $can_add,
            'events' => $events,
        ]);
    }

}
