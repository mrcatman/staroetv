<?php

namespace App\Http\Controllers;

use App\Helpers\ExternalServicesHelper;
use App\Helpers\MediaHelper;
use App\Helpers\PermissionsHelper;
use App\Jobs\DownloadExternalVideo;
use App\Models\Picture;
use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class RecordsEditController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!PermissionsHelper::allows('viedit')) {
                return [
                    'status' => 0,
                    'text' => 'Ошибка доступа'
                ];
            }
            return $next($request);
        });
    }

    public function menu()
    {
        $records = Record::whereIn('id', request()->input('ids'))->orderBy('id', 'asc')->get();

        $actions = [];
        if ($records->count() == 1) {
            $actions[] = [
                'name' => 'Изменить базовую информацию',
                'url' => route('records.edit.basic-info.form')
            ];
        }
        $actions[] = [
            'name' => 'Изменить канал/передачу',
            'url' => route('records.edit.transfer.form')
        ];
        $actions[] = [
            'name' => 'Изменить тип',
            'url' => route('records.edit.type.form')
        ];
        if ($records->count() == 1 && $records->first()->is_advertising) {
            $actions[] = [
                'name' => 'Изменить информацию о рекламном ролике',
                'url' => route('records.edit.commercials-info.form')
            ];
        }

        $actions[] = [
            'name' => 'Обновить обложки',
            'url' => route('records.edit.update-thumbnails'),
            'instant' => true
        ];

        if ($records->where(function ($record) {
            return !$record->source_path;
        })->count() > 0) {
            $actions[] = [
                'name' => 'Загрузить на сервер',
                'url' => route('records.edit.upload-to-server'),
                'instant' => true
            ];
        }

        if ($records->where(function ($record) {
            return $record->pending;
        })->count() > 0) {
            $actions[] = [
                'name' => 'Одобрить',
                'url' => route('records.edit.approve'),
                'instant' => true
            ];
        } else {
            $actions[] = [
                'name' => 'Скрыть',
                'url' => route('records.edit.unapprove'),
                'instant' => true
            ];
        }

        return [
            'status' => 1,
            'data' => [
                'html' => [
                    [
                        'replace' => '.menu--records[data-ids="' . $records->pluck('id')->implode(',') . '"]',
                        'html' => view("blocks.records.edit.menu", ['actions' => $actions])->render()
                    ]
                ]
            ]
        ];
    }

    public function basicInfoForm()
    {
        $record = Record::whereIn('id', request()->input('ids'))->firstOrFail();
        return [
            'status' => 1,
            'data' => [
                'title' => 'Изменить базовую информацию: ' . $record->title,
                'html' => view('blocks.records.edit.basic-info', ['record' => $record])->render()
            ]
        ];
    }

    public function saveBasicInfo()
    {
        $record = Record::findOrFail(request()->input('id'));
        $data = request()->validate([
            'title' => 'required',
            'short_description' => 'sometimes',
            'description' => 'sometimes',
            'source' => 'sometimes'
        ]);

        $record->year = request()->input('date.year') > 0 ? request()->input('date.year') : null;
        $record->month = request()->input('date.month') > 0 ? request()->input('date.month') : null;
        $record->day = request()->input('date.day') > 0 ? request()->input('date.day') : null;

        if ($record->year && $record->month && $record->day) {
            $record->date = Carbon::createFromDate($record->year, $record->month, $record->day);
        }

        $record->fill($data)->save();
        $record->setSupposedDate();

        return [
            'status' => 1,
            'text' => 'Сохранено',
            'data' => [
                'html' => $this->updateTemplates([$record])
            ]
        ];
    }

    public function commercialsInfoForm()
    {
        $record = Record::whereIn('id', request()->input('ids'))->firstOrFail();
        return [
            'status' => 1,
            'data' => [
                'title' => 'Изменить информацию о рекламном ролике: ' . $record->title,
                'html' => view('blocks.records.edit.commercials-info', ['record' => $record])->render()
            ]
        ];
    }

    public function saveCommercialsInfo()
    {
        $record = Record::findOrFail(request()->input('id'));
        $data = request()->validate([
            'title' => 'sometimes',
            'short_description' => 'sometimes',
            'description' => 'sometimes',
            'country' => 'sometimes',
            'city' => 'sometimes',
            'advertising_type' => 'sometimes',
            'advertising_category' => 'sometimes',
            'advertising_brand' => 'sometimes',
        ]);

        $record->fill($data)->save();
        return [
            'status' => 1,
            'text' => 'Сохранено',
            'data' => [
                'html' => $this->updateTemplates([$record])
            ]
        ];
    }

    public function transferForm()
    {
        $records = Record::whereIn('id', request()->input('ids'))->with(['channel', 'program'])->get();
        $programs = $records->countBy('program.name')->map(function ($count, $program) {
            return ($program ?? '-') . ': ' . $count;
        })->join(', ');
        $channels = $records->countBy('channel.name')->map(function ($count, $channel) {
            return ($channel ?? '-') . ': ' . $count;
        })->join(', ');

        $selected_program_id = $records->countBy('program.id')->keys()->first();
        $selected_channel_id = $records->countBy('channel.id')->keys()->first();

        return [
            'status' => 1,
            'data' => [
                'title' => 'Изменить канал/передачу',
                'html' => view('blocks.records.edit.transfer', [
                    'records' => $records,
                    'programs' => $programs,
                    'channels' => $channels,
                    'selected_program_id' => $selected_program_id,
                    'selected_channel_id' => $selected_channel_id
                ])->render()
            ]
        ];
    }

    public function saveTransfer()
    {
        $channel_id = request()->input('channel_id');
        if (!$channel_id) {
            return [
                'status' => 0,
                'text' => 'Выберите канал'
            ];
        }

        $program_id = request()->input('program_id', null);
//        if (!$program_id) {
//            return [
//                'status' => 0,
//                'text' => 'Выберите программу'
//            ];
//        }

        $ids = explode(',', request()->input('ids'));
        Record::whereIn('id', $ids)->update([
            'channel_id' => $channel_id,
            'program_id' => $program_id
        ]);
        return [
            'status' => 1,
            'text' => 'Сохранено',
            'data' => [
                'html' => $this->updateTemplatesByIds($ids)
            ]
        ];
    }

    public function typeForm()
    {
        $records = Record::whereIn('id', request()->input('ids'))->with(['channel', 'program'])->get();

        return [
            'status' => 1,
            'data' => [
                'title' => 'Изменить тип записи',
                'html' => view('blocks.records.edit.type', ['records' => $records])->render()
            ]
        ];
    }

    public function saveType()
    {
        $data = [
            'is_interprogram' => false,
            'is_advertising' => false,
            'is_clip' => false,
        ];

        $type = request()->input('type');
        switch ($type) {
            case 'programs':
                break;
            case 'interprogram':
            case 'program-design':
                $data['interprogram_type'] = request()->input('interprogram_type', null);
                $data['is_interprogram'] = true;
                break;
            case 'other':
                $data['other_category_id'] = request()->input('other.category_id', null);
                break;
            case 'advertising':
                $data['is_advertising'] = true;
                break;
            case 'clips':
                $data['is_clip'] = true;
                break;
        }
        $ids = explode(',', request()->input('ids'));
        Record::whereIn('id', $ids)->update($data);

        return [
            'status' => 1,
            'text' => 'Сохранено',
            'data' => [
                'html' => $this->updateTemplatesByIds($ids)
            ]
        ];
    }

    public function updateThumbnails()
    {
        $records = Record::whereIn('id', request()->input('ids'))->get();
        foreach ($records as $record) {
            if (!$record->source_path) {
                $thumbnail = ExternalServicesHelper::getThumbnail($record);
            } else {
                $thumbnail = MediaHelper::makeThumbnail(Storage::disk('media-storage')->path($record->source_path));
            }

            if ($thumbnail) {
                $cover = Picture::firstOrNew([
                    'url' => $thumbnail
                ]);
                $cover->save();

                $record->cover_id = $cover->id;
                $record->save();
            }
        }
        return [
            'status' => 1,
            'data' => [
                'html' => $this->updateTemplates($records)
            ]
        ];
    }

    public function uploadToServer()
    {
        $records = Record::whereIn('id', request()->input('ids'))->get();
        foreach ($records as $record) {
            if (!$record->source_path) {
                DownloadExternalVideo::dispatch($record);
            }
        }
        return [
            'status' => 1,
            'data' => [
                'html' => $this->updateTemplates($records)
            ]
        ];
    }

    public function approve()
    {
        $ids = request()->input('ids');
        Record::whereIn('id', $ids)->update(['pending' => false]);
        return [
            'status' => 1,
            'data' => [
                'html' => $this->updateTemplatesByIds($ids)
            ]
        ];
    }

    public function unapprove()
    {
        $ids = request()->input('ids');
        Record::whereIn('id', $ids)->update(['pending' => true]);
        return [
            'status' => 1,
            'data' => [
                'html' => $this->updateTemplatesByIds($ids)
            ]
        ];
    }

    private function updateTemplatesByIds(array $ids)
    {
        return $this->updateTemplates(Record::whereIn('id', $ids)->get());
    }

    private function updateTemplates($records)
    {
        $replacements = [];
        foreach ($records as $record) {
            Cache::forget('record_' . $record->id);
            Cache::forget('record_cover_' . $record->id);

            $class = $record->is_radio ? 'radio-recording' : 'record-item';

            $replacements[] = [
                'replace' => ".{$class}[data-id='{$record->id}']",
                'html' => $record->is_radio ? view('blocks.records.radio-item', ['record' => $record])->render() : view('blocks.records.item', ['record' => $record])->render()
            ];
        }
        return $replacements;
    }

}
