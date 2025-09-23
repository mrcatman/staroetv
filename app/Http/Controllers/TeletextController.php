<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Helpers\TeletextHelper;
use App\Helpers\ViewsHelper;
use App\Jobs\ProcessTeletext;
use App\Models\Channel;
use App\Models\Program;
use App\Models\Record;
use App\Models\Teletext;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TeletextController extends Controller {

    private $texts = [
        [
            'name' => 'Центр-Инфо',
            'channels' => [
                ['url' => 'tv-center']
            ]
        ],
        [
            'name' => 'Блиц-Текст',
            'channels' => [
                ['url' => 'ntv']
            ]
        ],
        [
            'name' => 'Телеинф',
            'channels' => [
                ['url' => 'ort', 'date_start' => [1,1,1992], 'date_end' => [1,1,2002]],
            ]
        ]
    ];

    public function index() {
        $params = [];
        if (!PermissionsHelper::allows('contentapprove')) {
            $params['pending'] = false;
        }


        $sections = [];
        foreach ($this->texts as $text) {
            $section = [
                'name' => $text['name'],
                'channels' => [],
                'items' => []
            ];

            $items = Teletext::inRandomOrder()->limit(5);
            foreach ($text['channels'] as $channel_params) {
                if (!isset($channels[$channel_params['url']])) {
                    $channels[$channel_params['url']] = Channel::where(['url' => $channel_params['url']])->first();
                }

                $channel = $channels[$channel_params['url']];
                if (isset($channel_params['date_start'])) {
                    $date_start = Carbon::createFromDate($channel_params['date_start'][2], $channel_params['date_start'][1], $channel_params['date_start'][0]);
                    $date_end = Carbon::createFromDate($channel_params['date_end'][2], $channel_params['date_end'][1], $channel_params['date_end'][0]);
                    $items = $items->orWhere(function ($q) use ($channel, $date_start, $date_end) {
                        $q->where(['channel_id' => $channel->id]);
                        return $q->whereBetween('date', [$date_start, $date_end]);
                    });

                    $names = $channel->names->filter(function($name) use ($date_start, $date_end) {
                        return $name->date_start->gt($date_start) || $name->date_end->lt($date_end);
                    })->unique('name');
                    foreach ($names as $name) {
                        $section['channels'][] = [
                            'url' => $channel->full_url,
                            'name' => $name->name,
                            'logo' => $name->logo ? $name->logo->url : null
                        ];
                    }
                } else {
                    $items = $items->orWhere(function ($q) use ($channel) {
                        $q->where(['channel_id' => $channel->id]);
                    });
                    $section['channels'][] = [
                        'url' => $channel->full_url,
                        'name' => $channel->name,
                        'logo' => $channel->logo ? $channel->logo->url : null
                    ];
                }
            }

            $section['items'] = $items->get();

            $sections[] = $section;
        }

        $new = Teletext::where($params)->orderBy('created_at', 'desc')->paginate(24);
        return view('pages.teletext.index', [
            'new' => $new,
            'sections' => $sections
        ]);
    }

    public function show($id) {
        $teletext = Teletext::find($id);
        if (!$teletext || !count($teletext->pages)) {
            return redirect("/");
        }
        ViewsHelper::increment($teletext, 'teletext');

        $page = request()->input('page', $teletext->pages[0]);
        if (!in_array($page, $teletext->pages)) {
            $page = $teletext->pages[0];
        }

        $content = $teletext->getPageContent($page);
        if (request()->has('ajax')) {
            return $content;
        }

        $related = Teletext::where('id', '!=', $teletext->id)->inRandomOrder()->limit(10)->get();

        $index = array_search($page, $teletext->pages);

        $navigation = [
            'prev' => $index > 0 ? $teletext->pages[$index - 1] : $teletext->pages[count($teletext->pages) - 1],
            'next' => $index < count($teletext->pages) - 1 ? $teletext->pages[$index+1] : $teletext->pages[0],
        ];

        return view("pages.teletext.show", [
            'teletext' => $teletext,
            'page' => $page,
            'content' => $content,
            'navigation' => $navigation,
            'related' => $related
        ]);
    }

    public function add() {
        if (!PermissionsHelper::allows('teletextown') && !PermissionsHelper::allows('teletext')) {
            return view("pages.errors.403");
        }
        return view("pages.forms.teletext", [
            'teletext' => null,
            'channels' => $this->getChannels()
        ]);
    }

    public function edit($id) {
        $teletext = Teletext::find($id);
        if (!$teletext) {
            return redirect("/");
        }
        if (!$teletext->can_edit) {
            return view("pages.errors.403");
        }
        return view("pages.forms.teletext", [
            'teletext' => $teletext,
            'channels' => $this->getChannels()
        ]);
    }

    public function save() {
        if (!PermissionsHelper::allows('teletextown') && !PermissionsHelper::allows('teletext')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        $teletext = new Teletext();
        return $this->fillData($teletext);
    }

    public function update($id) {
        $teletext = Teletext::find($id);
        if (!$teletext) {
            return [
                'status' => 0,
                'text' => 'Телетекст не найден'
            ];
        }
        if (!$teletext->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        return $this->fillData($teletext);
    }

    private function fillData(Teletext $teletext) {
        if (request()->has('cover_id')) {
            $data = request()->validate([
                'cover_id' => 'required|exists:pictures,id',
            ]);
            $teletext->fill($data);
            $teletext->save();
            return [
                'status' => 1,
            ];
        } else {
            $data = request()->validate([
                'channel_id' => 'required|exists:channels,id',
                'quality' => 'numeric|min:1|max:10',
                'description' => 'sometimes',
                'year' => 'sometimes|numeric',
                'month' => 'sometimes|numeric',
                'day' => 'sometimes|numeric',
            ]);

            $is_new = !$teletext->id;
            $file = request()->file('file');
            if ($is_new && !$file) {
                throw ValidationException::withMessages([
                    'file' => ['Не загружен файл'],
                ]);
            }
            if ($file && $file->getClientOriginalExtension() != 't42') {
                throw ValidationException::withMessages([
                    'file' => ['Загрузите файл с расширением .t42'],
                ]);
            }
            $teletext->fill($data);

            if (!$is_new) {
                $teletext->author_id = auth()->user()->id;
                $teletext->pending = !PermissionsHelper::allows('contentapprove');
            }

            $teletext->setSupposedDate();


            if ($file) {
                TeletextHelper::process($teletext, $file);
            }

            return [
                'status' => 1,
                'text' => $is_new ? 'Телетекст добавлен' : 'Телетекст обновлён',
                'redirect_to' => '/teletext/' . $teletext->id . (!$teletext->cover_id ? '?update_cover=1' : '')
            ];
        }
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

    public function approve() {
        $teletext = Teletext::find(request()->input('id'));
        if (!$teletext) {
            return [
                'status' => 0,
                'text' => 'Телетекст не найден'
            ];
        }
        $can_approve = PermissionsHelper::allows('contentapprove');
        if ($can_approve) {
            $status = request()->input('status', !$teletext->pending);
            $teletext->pending = $status;
            $teletext->save();
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


    private function getChannels()
    {
        return Channel::select(['id', 'name', 'logo_id', 'order', 'is_federal', 'is_radio'])->with('logo', 'names')->orderBy('order')->where(['is_radio' => false])->get();
    }


}
