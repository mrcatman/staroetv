<?php

namespace App\Http\Controllers;

use App\Constants\Teletexts;
use App\Helpers\PermissionsHelper;
use App\Helpers\ViewsHelper;
use App\Jobs\ProcessTeletext;
use App\Models\Channel;
use App\Models\Teletext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TeletextController extends EntityController
{

    protected $entity_class = Teletext::class;
    protected $permissions = [
        'approve' => 'contentapprove',
    ];

    public function __construct()
    {
        $this->redirect_after_delete = route('teletext.index');
    }



    private function findResults($query, $text)
    {
        $channels = [];
        foreach ($text['channels'] as $channel_params) {

            $channel = Channel::where(['url' => $channel_params['url']])->first();
            if (isset($channel_params['date_start'])) {
                $date_start = Carbon::createFromDate($channel_params['date_start'][2], $channel_params['date_start'][1], $channel_params['date_start'][0]);
                $date_end = Carbon::createFromDate($channel_params['date_end'][2], $channel_params['date_end'][1], $channel_params['date_end'][0]);
                $query = $query->orWhere(function ($q) use ($channel, $date_start, $date_end) {
                    $q->where(['channel_id' => $channel->id]);
                    return $q->whereBetween('date', [$date_start, $date_end]);
                });

                $names = $channel->names->filter(function ($name) use ($date_start) {
                    return (!$name->date_end || $name->date_end->gt($date_start));
                })->unique('name');
                foreach ($names as $name) {
                    $channels[] = [
                        'id' => $channel->id,
                        'url' => $channel->full_url,
                        'name' => $name->name,
                        'logo' => $name->logo ? $name->logo->url : null
                    ];
                }
            } else {
                $query = $query->orWhere(function ($q) use ($channel) {
                    $q->where(['channel_id' => $channel->id]);
                });
                $channels[] = [
                    'id' => $channel->id,
                    'url' => $channel->full_url,
                    'name' => $channel->name,
                    'logo' => $channel->logo ? $channel->logo->url : null
                ];
            }
        }
        return [$query, $channels];
    }

    private function getBreadcrumb(Teletext $teletext)
    {
        if (!$teletext->channel) {
            return null;
        }
        $texts = array_values(array_filter(Teletexts::LIST, function ($text) use ($teletext) {
            foreach ($text['channels'] as $channel_params) {
                if ($channel_params['url'] == $teletext->channel->url) {
                    if (!isset($channel_params['date_start'])) {
                        return true;
                    }
                    $date_start = Carbon::createFromDate($channel_params['date_start'][2], $channel_params['date_start'][1], $channel_params['date_start'][0]);
                    $date_end = Carbon::createFromDate($channel_params['date_end'][2], $channel_params['date_end'][1], $channel_params['date_end'][0]);
                    if ($teletext->date->gt($date_start) && $teletext->date->lt($date_end)) {
                        return true;
                    }

                }
            }
            return false;
        }));
        if (count($texts) > 0) {
            return [
                'name' => $texts[0]['name'],
                'url' => route('teletext.channel', $texts[0]['url'])
            ];
        }
        return [
            'name' => $teletext->channel_name,
            'url' => route('teletext.channel', $teletext->channel->url ?? $teletext->channel->id)
        ];
    }

    public function index()
    {
        $params = [];
        if (!PermissionsHelper::allows('contentapprove')) {
            $params['pending'] = false;
        }

        $used_channel_ids = [];
        $sections = [];
        foreach (Teletexts::LIST as $text) {
            $section = [
                'name' => $text['name'],
                'url' => $text['url'],
            ];

            $query = Teletext::inRandomOrder()->limit(4);
            [$items, $channels] = $this->findResults($query, $text);

            $section['items'] = $items->get();
            $section['channels'] = $channels;
            foreach ($channels as $channel) {
                $used_channel_ids[] = $channel['id'];
            }

            if (count($section['items']) > 0) {
                $sections[] = $section;
            }
        }

        $other_channel_ids = Teletext::whereNotIn('channel_id', $used_channel_ids)->pluck('channel_id')->unique();
        $channels = Channel::whereIn('id', $other_channel_ids)->get();
        foreach ($channels as $channel) {
            $section = [
                'name' => null, //$channel->name,
                'url' => $channel->url ?? $channel->id,
                'channels' => [
                    [
                        'url' => $channel->full_url,
                        'name' => $channel->name,
                        'logo' => $channel->logo ? $channel->logo->url : null
                    ]
                ],
            ];
            $section['items'] = Teletext::where(['channel_id' => $channel->id])->inRandomOrder()->limit(4)->get();

            $sections[] = $section;
        }

        $items = Teletext::where($params)->orderBy('created_at', 'desc');
        $years = (clone $items)->where('year', '>', 0)->groupBy('year')->select('year', DB::raw('count(*) as count'))->pluck('count', 'year')->sortKeys();
        $selected_year = request()->input('year');
        if ($selected_year) {
            $items = $items->where(['year' => $selected_year]);
        }
        $items = $items->paginate(24);

        return view('pages.teletext.index', [
            'items' => $items,
            'years' => $years,
            'selected_year' => $selected_year,
            'sections' => $sections
        ]);
    }

    public function channel($url)
    {
        $texts = array_values(array_filter(Teletexts::LIST, function ($text) use ($url) {
            return $text['url'] == $url;
        }));

        $query = Teletext::query()->orderBy('created_at', 'desc');
        if (count($texts) > 0) {
            $text = $texts[0];
            $title = $text['name'];

            [$items, $channels] = $this->findResults($query, $text);
        } else {
            $channel = Channel::findByIdOrUrl($url);
            if (!$channel) {
                return redirect(route('teletext.index'));
            }
            $title = $channel->name;
            $items = $query->where(['channel_id' => $channel->id]);
            $channels = [$channel];
        }

        $years = (clone $items)->where('year', '>', 0)->groupBy('year')->select('year', DB::raw('count(*) as count'))->pluck('count', 'year')->sortKeys();
        $selected_year = request()->input('year');
        if ($selected_year) {
            $items = $items->where(['year' => $selected_year]);
        }

        $items = $items->paginate(24);

        $related = Teletext::inRandomOrder()->limit(10)->get();

        return view('pages.teletext.channel', [
            'url' => $url,
            'title' => $title,
            'years' => $years,
            'selected_year' => $selected_year,
            'items' => $items,
            'channels' => $channels,
            'related' => $related,

        ]);
    }

    public function show($id)
    {
        $teletext = Teletext::find($id);
        if (!$teletext) {
            return redirect(route('index'));
        }

        ViewsHelper::increment($teletext, 'teletext');
        $breadcrumb = $this->getBreadcrumb($teletext);

        if (!$teletext->pages || !count($teletext->pages)) {
            $page = null;
            $content = '';
            $navigation = null;

        } else {
            $default_page = in_array('100', $teletext->pages) ? '100' : $teletext->pages[0];
            $page = request()->input('page', $default_page);
            if (!in_array($page, $teletext->pages)) {
               $page = $default_page;
            }

            $content = $teletext->getPageContent($page);
            if (request()->has('inline')) {
                return view("pages.teletext.inline", [
                    'content' => $content,
                ]);
            }

            if (request()->has('ajax')) {
                return $content;
            }

            $index = array_search($page, $teletext->pages);

            $navigation = [
                'prev' => $index > 0 ? $teletext->pages[$index - 1] : $teletext->pages[count($teletext->pages) - 1],
                'next' => $index < count($teletext->pages) - 1 ? $teletext->pages[$index + 1] : $teletext->pages[0],
            ];
        }

        $related = Teletext::where('id', '!=', $teletext->id)->inRandomOrder()->limit(10)->get();
        return view("pages.teletext.show", [
            'teletext' => $teletext,
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'content' => $content,
            'navigation' => $navigation,
            'related' => $related
        ]);
    }

    public function add()
    {
        if (!PermissionsHelper::allows('teletextown') && !PermissionsHelper::allows('teletext')) {
            return redirect('/');
        }
        return view("pages.teletext.form", [
            'teletext' => null,
            'channels' => $this->getChannels()
        ]);
    }

    public function edit($id)
    {
        $teletext = Teletext::find($id);
        if (!$teletext) {
            return redirect(route('index'));
        }
        if (!$teletext->can_edit) {
            return redirect('/');
        }
        return view("pages.teletext.form", [
            'teletext' => $teletext,
            'channels' => $this->getChannels()
        ]);
    }

    public function save()
    {
        if (!PermissionsHelper::allows('teletextown') && !PermissionsHelper::allows('teletext')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        $teletext = new Teletext();
        return $this->fillData($teletext);
    }

    protected function fillData($teletext)
    {
        if (request()->has('cover_id')) {
            $data = request()->validate([
                'cover_id' => 'required|exists:pictures,id',
            ]);
            $teletext->fill($data);
            $teletext->save();
            Cache::forget('teletext_cover_' . $teletext->id);

            return [
                'status' => 1,
            ];
        }

        $data = request()->validate([
            'channel_id' => 'required|exists:channels,id',
            'quality' => 'numeric|min:1|max:10',
            'description' => 'sometimes',
            'date.year' => 'sometimes',
            'date.month' => 'sometimes',
            'date.day' => 'sometimes',
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

        $data['year'] = $data['date']['year'] && $data['date']['year'] > 0 ? (int)$data['date']['year'] : null;
        $data['month'] = $data['date']['month'] && $data['date']['month'] > 0 ? (int)$data['date']['month'] : null;
        $data['day'] = $data['date']['day'] && $data['date']['day'] > 0 ? (int)$data['date']['day'] : null;
        unset($data['date']);

        $teletext->fill($data);

        if ($is_new) {
            $teletext->author_id = auth()->user()->id;
            $teletext->pending = !PermissionsHelper::allows('contentapprove');
        }


        $teletext->setSupposedDate();

        if ($file) {
            $teletext->pages = [];
            Storage::disk('temp')->putFileAs('teletext', $file, 'temp_'.$teletext->id.'.t42');
            ProcessTeletext::dispatch($teletext);
        }

        Cache::forget('teletext_cover_' . $teletext->id);

        return [
            'status' => 1,
            'text' => $is_new ? 'Телетекст добавлен' : 'Телетекст обновлён',
            'redirect_to' => route('teletext.show', $teletext)// . (!$teletext->cover_id ? '?update_cover=1' : '')
        ];
    }


    private function getChannels()
    {
        return Channel::selectDefault()->with('logo', 'names')->orderBy('order')->where(['is_radio' => false])->get();
    }

    public function afterDelete(Model $entity) {
        $dir = '/teletext-data/'.$entity->id;
        Storage::disk('public_data')->deleteDirectory($dir);

        return parent::afterDelete($entity);
    }
}
