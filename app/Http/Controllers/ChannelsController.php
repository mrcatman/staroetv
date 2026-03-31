<?php

namespace App\Http\Controllers;

use App\Constants\Actions;
use App\Constants\CacheTimes;
use App\Helpers\ActionsLogHelper;
use App\Helpers\PermissionsHelper;
use App\Helpers\ViewsHelper;
use App\Models\AdditionalChannel;
use App\Models\Channel;
use App\Models\ChannelName;
use App\Models\Genre;
use App\Models\DesignPackage;
use App\Models\Picture;
use App\Models\Program;
use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mews\Purifier\Facades\Purifier;

class ChannelsController extends EntityController
{

    protected $entity_class = Channel::class;
    protected $permissions = [
        'create' => 'channelsown',
        'approve' => 'contentapprove'
    ];
    protected $redirect_after_delete = '/video';

    public function show($url)
    {
        $channel = Channel::where(['url' => $url])->first();
        if (!$channel) {
            $channel = Channel::where(['id' => $url])->first();
        }
        if (!$channel) {
            return redirect(route('index'));
        }

        $genres = Cache::remember('channel_programs_data_' . $channel->id, CacheTimes::PAGE, function () use ($channel) {
            $programs = $channel->programs()->withCount('records')->get();
            $additional = $channel->additionalPrograms()->withCount('records')->get();
            foreach ($additional as $program) {
                $additional_channel_data = AdditionalChannel::where(['program_id' => $program->id, 'channel_id' => $channel->id])->first();
                if ($additional_channel_data->title != '') {
                    $program->name = $additional_channel_data->title;
                }
            }
            $programs = $programs->merge($additional);
            $programs = $programs->filter(function ($program) {
                return !$program->pending || $program->can_edit;
            });

            $genre_ids = $programs->sortBy('order')->pluck('genre_id')->unique();
            $genres = Genre::whereIn('id', $genre_ids)->get();
            foreach ($genres as &$genre) {
                $genre->programs = $programs->filter(function ($program) use ($genre) {
                    return $program->genre_id == $genre->id;
                });
            }
            $no_genre_programs = $programs->filter(function ($program) {
                return $program->genre_id == null;
            });
            if (count($no_genre_programs) > 0) {
                $no_genre = (object)[
                    'id' => -1,
                    'url' => 'unspecified',
                    'name' => 'Другое',
                    'programs' => $no_genre_programs
                ];
                $genres->push($no_genre);
            }
            $popular_programs = $channel->unorderedPrograms()->withCount('records')->having('records_count', '>', 0)->orderBy('views', 'desc')->limit(25)->get();

            if (count($popular_programs) > 1) {
                $genres->prepend((object)[
                    'id' => -2,
                    'url' => 'popular',
                    'name' => 'Популярные',
                    'programs' => $popular_programs
                ]);
            }

            $show_genres = $programs->filter(function ($program) {
                return !!$program->genre_id;
            })->pluck('genre_id')->unique()->count() > 0;

            $show_load_more_button = count($programs) > 25;

            return [
                'genres' => $genres,
                'show_genres' => $show_genres,
                'show_load_more_button' => $show_load_more_button
            ];
        });

        $global_programs = Cache::remember('channel_global_' . $channel->id, CacheTimes::PAGE, function () use ($channel) {
            $global_programs = Program::whereNull('channel_id')->whereHas('records', function ($q) use ($channel) {
                $q->where(['channel_id' => $channel->id]);
            })->get();
            foreach ($global_programs as $program) {
                $channel_records = $program->records()->where(['channel_id' => $channel->id])->inRandomOrder();
                $program->cover_url = $channel_records->first()->cover_url;
                $program->records_count = $channel_records->count();
            }
            $unknown = $channel->records()->where(['is_interprogram' => false, 'is_advertising' => false])->whereNull('program_id');
            $unknown_count = $unknown->clone()->count();

            if ($unknown_count > 0) {
                $unknown_random_cover = $unknown->inRandomOrder()->first()->cover;
                $global_programs->add((object)[
                    'pending' => false,
                    'name' => 'Прочее / неопознанные передачи',
                    'full_url' => typed_route('[CHANNEL].programs.unknown', $channel->is_radio, [$channel->url ?? $channel->id]),
                    'cover_url' => $unknown_random_cover,
                    'records_count' => $unknown_count,
                    'channels_history' => [],
                ]);
            }
            return $global_programs;
        });

        $interprogram_packages = Cache::remember('channel_interprogram_' . $channel->id, CacheTimes::PAGE, function () use ($channel) {
            $interprogram_packages = $channel->interprogramPackages;

//        foreach ($interprogram_packages as $interprogram_package) {
//            //$interprogram_package->records = $interprogram_package->records->shuffle();
//        }
            $random_records = Record::where(['channel_id' => $channel->id])->whereNull('program_id')->whereNull('interprogram_package_id')->where(function ($q) {
                $q->whereNotIn('interprogram_type', [11, 22]);
                $q->orWhereNull('interprogram_type');
            })->inRandomOrder()->get();
            $random_record = $random_records->filter(function ($record) {
                return $record->cover && $record->cover != '';
            })->first();
            if (count($channel->interprogramRecords) > 0) {
                $interprogram_packages->push(new DesignPackage([
                    'name' => 'Прочее',
                    'pictures' => [],
                    'years_range' => '',
                    'channel_id' => $channel->id,
                    'url' => 'other',
                    'coverPicture' => new Picture([
                        'url' => $random_record ? $random_record->cover : ''
                    ]),
                    'records' => collect([])
                ]));
            }
            return $interprogram_packages;
        });

        $articles = Cache::remember('channel_articles_' . $channel->id, CacheTimes::PAGE, function () use ($channel) {
            $count = count($channel->articles);
            $list = $channel->articles()->limit(10)->get();
            return [
                'count' => $count,
                'list' => $list
            ];
        });

        ViewsHelper::increment($channel, 'channels');
        return view("pages.channels.show", [
            'channel' => $channel,
            'articles' => $articles,
            'programs' => $genres,
            'global_programs' => $global_programs,
            'interprogram_packages' => $interprogram_packages,
            'records_conditions' => ['show_years' => true, 'channel_id' => $channel->id, 'is_advertising' => false, 'is_radio' => $channel->is_radio],
            'records_conditions_interprogram' => ['channel_id' => $channel->id, 'is_interprogram' => true, 'is_radio' => $channel->is_radio]
        ]);
    }

    public function add()
    {
        if (!PermissionsHelper::allows('channelsown') && !PermissionsHelper::allows('channels')) {
            return redirect('/');
        }
        $is_radio = !!request()->input('is_radio', false);
        return view("pages.channels.form", [
            'channel' => null,
            'is_radio' => $is_radio,
        ]);
    }

    public function edit($id)
    {
        $channel = Channel::find($id);
        if (!$channel) {
            return redirect(typed_route('records.[RECORD].index', false));
        }
        if (!$channel->can_edit) {
            return redirect('/');
        }
        $is_radio = $channel->is_radio;
        $all_channels = Channel::where(['is_radio' => $channel->is_radio])->where('id', '!=', $id)->get();
        return view("pages.channels.form", [
            'channel' => $channel,
            'all_channels' => $all_channels,
            'is_radio' => $is_radio,
        ]);
    }

    public function unknownPrograms($url)
    {
        $channel = Channel::where(['url' => $url])->first();
        if (!$channel) {
            $channel = Channel::where(['id' => $url])->first();
        }
        if (!$channel) {
            return redirect(route('index'));
        }

        $conditions = ['show_years' => true, 'new_titles' => false, 'channel_id' => $channel->id, 'is_advertising' => false, 'is_radio' => $channel->is_radio, 'program_id' => null, 'is_interprogram' => false];

        $program = new Program([
            'name' => 'Прочее / неопознанные передачи',
            'description' => $channel->is_radio ?
                'Здесь представлены все неотсортированные материалы и фрагменты эфира радиостанции <strong>' . $channel->name . '</strong>' :
                'Здесь представлены все неотсортированные материалы канала <strong>' . $channel->name . '</strong> и передачи, конкретную принадлежность которых установить, увы, не удалось (мы будем признательны Вам, если Вы поможете нам опознать их!)'
        ]);

        return view("pages.programs.show", [
            'program' => $program,
            'related_programs' => [],
            'channel' => $channel,
            'records_conditions' => $conditions,
            'unknown' => true
        ]);
    }

    protected function fillData($channel)
    {
        $data = request()->validate([
            'name' => 'required',
            'description' => 'sometimes',
            'background' => 'sometimes',
            'logo_id' => 'sometimes',
            'is_regional' => 'sometimes',
            'is_federal' => 'sometimes',
            'is_abroad' => 'sometimes',
            'country' => 'sometimes',
            'city' => 'sometimes',
            'is_radio' => 'sometimes',
            'url' => 'sometimes'
        ]);

        foreach (['is_regional', 'is_abroad', 'is_federal'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = ($data[$key] === "true" || $data[$key] === true) ? 1 : 0;
            }
        }
        if (request()->has('url') && request()->input('url') != '') {
            $same_url_channel = Channel::where(['url' => request()->input('url')])->first();
            if ($same_url_channel && $same_url_channel->id != $channel->id) {
                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'url' => ['Канал с таким URL уже существует'],
                ]);
                throw $error;
            }
        }

        if (isset($data['description'])) {
            $data['description'] = Purifier::clean($data['description']);
        }

        $this->clearCache($channel);

        $channel->fill($data);
        $this->saveEntity($channel);

        if (request()->has('channel_names')) {
            $names = request()->input('channel_names');
            $names = json_decode($names);
            $ids = [];
            foreach ($names as $name) {
                $start = Carbon::parse($name->date_start);
                $end = Carbon::parse($name->date_end);
                $alternatives = isset($name->alternatives) ? array_values(array_map(function ($alternative) {
                    return is_object($alternative) ? $alternative->text : $alternative;
                }, $name->alternatives)) : [];
                $name_data = [
                    'channel_id' => $channel->id,
                    'name' => $name->name,
                    'alternatives' => $alternatives,
                    'logo_id' => $name->logo_id,
                    'date_start' => !$start->isToday() ? $start : null,
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
            'redirect_to' => $channel->full_url
        ];
    }

    public function merge()
    {
        $original = Channel::find(request()->input('original_id'));
        if (!$original) {
            return [
                'status' => 0,
                'text' => 'Канал не найден'
            ];
        }
        if (!$original->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        if (request()->input('is_advertising')) {
            Record::where(['channel_id' => $original->id])->update(['is_advertising' => true]);
        } else {
            $merged = Channel::find(request()->input('merged_id'));
            if (!$merged) {
                return [
                    'status' => 0,
                    'text' => 'Канал для объединения не найден'
                ];
            }
            Record::where(['channel_id' => $original->id])->update(['channel_id' => $merged->id]);
            Program::where(['channel_id' => $original->id])->update(['channel_id' => $merged->id]);
            ChannelName::where(['channel_id' => $original->id])->update(['channel_id' => $merged->id]);

            ActionsLogHelper::create($merged, Actions::Merge, ['name' => [$original->name]]);
        }

        ActionsLogHelper::create($original, Actions::Delete);

        return [
            'status' => 1,
            'text' => $original->is_radio ? 'Радио объединено' : 'Канал объединен',
            'redirect_to' => typed_route('records.[RECORD].index', $original->is_radio)
        ];
    }

    public function ajaxList()
    {
        $channels = Channel::selectDefault()
            ->with(['names', 'logo'])->orderBy('is_federal', 'desc')->orderBy('order', 'asc')->get();
        return [
            'status' => 1,
            'data' => [
                'channels' => $channels
            ]
        ];
    }

    public function programs($id)
    {
        $channel = Channel::find($id);
        if (!$channel) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $programs = Program::where(['channel_id' => $channel->id])->with('coverPicture')->get();
        $programs = $programs->merge($channel->additionalPrograms);
        $global_programs = Program::whereNull('channel_id')->get();
        $programs = $programs->merge($global_programs);
        return [
            'status' => 1,
            'data' => [
                'programs' => $programs
            ]
        ];
    }


    public function autocomplete()
    {
        $count = 30;
        $channels = Channel::select('id', 'name', 'is_radio')->orderBy('id', 'asc');
        if (request()->has('term')) {
            $channels = $channels->where('name', 'LIKE', '%' . request()->input('term') . '%');
        }
        $total = $channels->count();
        $page = request()->input('page', 1);
        $channels = $channels->limit($count)->offset($count * ($page - 1))->get();
        return [
            'status' => 1,
            'data' => [
                'total' => $total,
                'channels' => $channels
            ]
        ];
    }

    private function clearCache($channel)
    {
        Cache::forget('channel_programs_' . $channel->id);
        Cache::forget('channel_global_' . $channel->id);
        Cache::forget('channel_interprogram_' . $channel->id);
        Cache::forget('channel_logo_' . $channel->id);
        Cache::forget('channel_random_logo_' . $channel->id);
    }

    protected function afterDelete(Model $entity)
    {
        $this->clearCache($entity);
        return parent::afterDelete($entity);
    }

}
