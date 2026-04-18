<?php

namespace App\Http\Controllers;

use App\Constants\Actions;
use App\Constants\Periods;
use App\Helpers\ActionsLogHelper;
use App\Helpers\PermissionsHelper;
use App\Helpers\ViewsHelper;
use App\Models\AdditionalChannel;
use App\Models\Channel;
use App\Models\Genre;
use App\Models\Program;
use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Mews\Purifier\Facades\Purifier;

class ProgramsController extends EntityController {

    protected $entity_class = Program::class;
    protected $permissions = [
        'approve' => 'contentapprove'
    ];
    protected $redirect_after_delete = '/video';


    public function index($params) {
        $is_radio = $params['is_radio'];

        $category = Genre::where(['url' => request()->input('category')])->first();

        $channel_ids  = Channel::where(['is_radio' => $is_radio])->where(['is_regional' => false])->where(['is_abroad' => false])->pluck('id');
        $programs = Program::where(['pending' => false])->withCount('records')->whereIn('channel_id', $channel_ids);

        $page_title = "Передачи";
        if ($category) {
            $programs = $programs->where(['genre_id' => $category->id]);
            $page_title = $category->name;
        }

        $programs = $programs->orderBy('views', 'desc');

        $period = Periods::find(request()->input('period'));
        if ($period) {
            $programs = $programs->whereHas('records', function ($query) use ($period) {
                $query->whereBetween('date', Periods::getDatesInterval($period));
            });
        }

        $program_ids = $programs->pluck('id');

        $show_load_more_button = false;
        if (!$period || !$category) {
            $show_load_more_button = true;
            $programs = $programs->limit(20);
        }

        $programs = $programs->get();
        $records_conditions = [
            'show_years' => true,
            'is_radio' => $is_radio,
            'is_interprogram' => false,
            'program_id_in' => $program_ids
        ];
        if ($period) {
            $records_conditions['period'] = $period;
        }

        $categories = Cache::remember('program_categories_'.($is_radio ? 'radio' : 'video'), 3600, function() use ($is_radio) {
            return Genre::where(['type' => 'programs'])->whereHas('programs', function ($query) use ($is_radio) {
                $query->whereHas('channel', function ($query) use ($is_radio) {
                    $query->where(['is_radio' => $is_radio]);
                });
            })->get();
        });

        return view('pages.programs.index', [
            'is_radio' => $is_radio,
            'period' => $period,
            'params' => $params,
            'show_load_more_button' => $show_load_more_button,
            'page_title' => $page_title,
            'records_conditions' => $records_conditions,
            'programs' => $programs,
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    public function showMore($params) {
        $is_radio = $params['is_radio'];
        $channel_ids  = Channel::where(['is_radio' => $is_radio])->where(['is_regional' => false])->where(['is_abroad' => false])->pluck('id');
        $programs = Program::where(['pending' => false])->withCount('records')->whereIn('channel_id', $channel_ids);

        if (request()->has('period')) {
            $period = Periods::find(request()->input('period'));
            if ($period) {
                $programs = $programs->whereHas('records', function ($query) use ($period) {
                    $query->whereBetween('date', Periods::getDatesInterval($period));
                });
            }
        }

        if (request()->has('category')) {
            $category = Genre::where(['url' => request()->input('category')])->first();
            if ($category) {
                $programs = $programs->where(['genre_id' => $category->id]);
            }
        }

        if (request()->has('channel_id')) {
            $channel = Channel::find(request()->input('channel_id'));
            if ($channel) {
                $programs = $programs->where(['channel_id' => $channel->id])->having('records_count', '>', 0);
            }
        }

        $limit = (int)request()->input('limit', 20);
        $page = (int)request()->input('page', 1);

        $has_next_page = count($programs->clone()->limit($limit)->offset(($page) * $limit)->get()) > 0;

        $programs = $programs->limit($limit)->offset(($page - 1) * $limit)->orderBy('views', 'desc')->get();

        $html_replacements = [
            [
                'append_to' => '.programs-list--with-show-more',
                'html' => view("blocks.programs.list", ['programs' => $programs, 'is_radio' => $params['is_radio']])->render()
            ],
        ];

        if (!$has_next_page) {
            $html_replacements[] = ['replace' => '.programs-list__show-more', 'html' => ''];
        }

        return [
            'status' => 1,
            'data' => [
                'html' => $html_replacements
            ]
        ];
    }


    public function show($id) {
        if ($id == 'unknown-program') {
            return redirect(route('records.video.other.category', 'unknown'));
        }

        $program = Program::where(['url' => $id])->first();
        if (!$program) {
            $program = Program::find($id);
        }
        if (!$program) {
            return redirect(route('index'));
        }
        ViewsHelper::increment($program, 'programs');

        $conditions = [ 'show_years' => true, 'new_titles' => !$program->show_full_titles, 'program_id' => $program->id, 'is_interprogram' => false];
        if ($program->channel) {
            $conditions['is_radio'] = $program->channel->is_radio;
        }

        $program->original_name = $program->name;
        $cover = $program->cover_url;

        $channel = $program->channel;
        if (request()->has('from')) {
            $from_channel_id = request()->input('from');
            $channel = Channel::findOrFail($from_channel_id);
            $additional_channel_data = AdditionalChannel::where(['program_id' => $program->id, 'channel_id' => $from_channel_id])->first();
            if ($additional_channel_data && $additional_channel_data->channel) {
                $channel = $additional_channel_data->channel;
                if ($additional_channel_data->title != "") {
                    $program->name = $additional_channel_data->title;
                }
            }
            if (!$program->channel_id) {
                $random_record = $program->records()->where(['channel_id' => $channel->id])->inRandomOrder()->first();
                if ($random_record) {
                    $cover = $random_record->cover;
                }
                $program->channel = $channel;
                $conditions['channel_id'] = $channel->id;
            }
        }

        $related_programs = [];
        if ($program->genre_id && $channel) {
            $related_programs = Program::where(['channel_id' => $channel->id, 'genre_id' => $program->genre_id])->where('id', '!=', $program->id)->inRandomOrder()->limit(10)->get();
        }

        return view("pages.programs.show", [
            'program' => $program,
            'cover' => $cover,
            'related_programs' => $related_programs,
            'channel' => $channel,
            'records_conditions' => $conditions,
            'unknown' => false
        ]);
    }

    public function add() {
        if (!PermissionsHelper::allows('programsown') && !PermissionsHelper::allows('programs')) {
            return redirect('/');
        }

        $channel_id = request()->input('channel_id');
        $channel = Channel::findByIdOrUrl($channel_id);
        if (!$channel || !$channel->can_edit) {
            return redirect('/');
        }
        return view("pages.programs.form", [
            'program' => null,
            'channel' => $channel,
        ]);
    }

    public function edit($id) {
        $all_channels = null;
        $program = Program::find($id);
        if (!$program) {
            return redirect(route('index'));
        }
        if (!$program->can_edit || ($program->channel && !$program->channel->can_edit)) {
            return redirect('/');
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
        return view("pages.programs.form", [
            'program' => $program,
            'channel' => $program->channel,
            'all_channels' => $all_channels,
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

        $channel_id = request()->input('channel_id');
        $channel = Channel::findByIdOrUrl($channel_id);

        if (!$channel ) {
            return [
                'status' => 0,
                'text' => 'Ошибка: канал не найден'
            ];
        }

        if (!$channel->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        $program = new Program();
        $program->channel_id = $channel->id;
        return $this->fillData($program);
    }



    protected function fillData($program) {
        $data = request()->validate([
            'name' => 'required|min:1',
            'description' => 'sometimes',
            'date_of_start' => 'sometimes',
            'date_of_closedown' => 'sometimes',
            'genre_id' => 'sometimes',
            'cover_id' => 'sometimes',
            'url' => 'sometimes',
            'channel_id' => 'sometimes',
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

        $data['date_of_start'] = isset($data['date_of_start']) ? Carbon::parse($data['date_of_start']) : null;
        $data['date_of_closedown'] = isset($data['date_of_closedown']) ? Carbon::parse($data['date_of_closedown']) : null;

        if (isset($data['description'])) {
            $data['description'] = Purifier::clean($data['description']);
        }

        $data['show_full_titles'] = !!request()->input('show_full_titles', false);
        if (!$program->author_id) {
            $data['author_id'] = auth()->user()->id;
        }

        $program->fill($data);
        parent::saveEntity($program);

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

        if ($program->channel_id) {
            $program->records()->doesntHave('channel')->update(['channel_id' => $program->channel_id]);
        }

        $this->clearCache($program);
        return [
            'status' => 1,
            'text' => 'Информация о программе обновлена',
            'redirect_to' => route('programs.edit', $program->id)
        ];
    }

    private function clearCache($program) {
        Cache::forget('program_random_pictures_'.$program->id);
        Cache::forget('program_cover_'.$program->id);
        Cache::forget('programs_channels_names_'.$program->id);
        Cache::forget('other_categories');
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
            ActionsLogHelper::create($merged, Actions::Merge, ['name' => [$original->name]]);
        }

        ActionsLogHelper::create($original, Actions::Delete);

        return [
            'status' => 1,
            'text' => 'Программа объединена',
            'redirect_to' => $merged ? route('programs.edit', $original->id) : typed_route('records.[RECORD].index', $original->is_radio)
        ];
    }

    public function editList($channel_id) {
        if (!PermissionsHelper::allows('programs')) {
            return redirect(route('index'));
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
        return view('pages.programs.list-form', [
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

    protected function afterDelete($program)
    {
        Record::where(['program_id' => $program->id])->update(['program_id' => -1]);
        return [
            'status' => 1,
            'text' => 'Программа удалена',
            'redirect_to' => $program->channel ? $program->channel->full_url : route('records.video.index')
        ];
    }
}
