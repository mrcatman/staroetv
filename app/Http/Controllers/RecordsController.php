<?php

namespace App\Http\Controllers;

use App\Constants\Actions;
use App\Constants\CacheTimes;
use App\Constants\Geography;
use App\Constants\MaterialTypes;
use App\Constants\Periods;
use App\Constants\RecordComplaintTypes;
use App\Constants\Records;
use App\Helpers\ActionsLogHelper;
use App\Helpers\DatesHelper;
use App\Helpers\ExternalServicesHelper;
use App\Helpers\MediaHelper;
use App\Helpers\PermissionsHelper;
use App\Helpers\RecordsHelper;
use App\Helpers\ViewsHelper;
use App\Http\Requests\RecordsSearchRequest;
use App\Jobs\DownloadExternalVideo;
use App\Models\AdditionalChannel;
use App\Models\Channel;
use App\Models\ChannelName;
use App\Models\Genre;
use App\Models\DesignPackage;
use App\Models\Picture;
use App\Models\Program;
use App\Models\Record;
use App\Models\RecordComplaint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Mews\Purifier\Facades\Purifier;


class RecordsController extends EntityController
{
    protected $entity_class = Record::class;
    protected $permissions = [
        'approve' => 'viapprove'
    ];

    public function buildChannelsList($params)
    {
        $hash = md5(json_encode($params));
        return Cache::remember('channels_list_' . $hash, CacheTimes::PAGE, function () use ($params) {
            $federal = Channel::where(['is_federal' => true])->where($params)->orderBy('order', 'ASC')->get();

            $regions = Geography::REGIONS;
            $regions_by_city = [];
            foreach ($regions as $region => $cities) {
                foreach ($cities as $city) {
                    $regions_by_city[$city] = $region;
                }
            }
            $channels_by_region = [];
            $regional = Channel::where(['is_regional' => true, 'is_abroad' => false])->where($params)->orderBy('order', 'ASC')->get();
            foreach ($regional as $channel) {
                $channel_data = [
                    'id' => $channel->id,
                    'name' => $channel->name,
                    'logo' => $channel->logo,
                    'url' => $channel->full_url,
                ];
                if (isset($regions_by_city[$channel->city])) {
                    if (!isset($channels_by_region[$regions_by_city[$channel->city]])) {
                        $channels_by_region[$regions_by_city[$channel->city]] = ['cities' => [], 'channels' => [], 'count' => 0];
                    }
                    if (!isset($channels_by_region[$regions_by_city[$channel->city]]['cities'][$channel->city])) {
                        $channels_by_region[$regions_by_city[$channel->city]]['cities'][$channel->city] = [];
                    }
                    $channels_by_region[$regions_by_city[$channel->city]]['count']++;
                    $channels_by_region[$regions_by_city[$channel->city]]['cities'][$channel->city][] = $channel_data;
                } elseif (isset($regions[$channel->city])) {
                    if (!isset($channels_by_region[$channel->city])) {
                        $channels_by_region[$channel->city] = ['cities' => [], 'channels' => [], 'count' => 0];
                    }
                    $channels_by_region[$channel->city]['count']++;
                    $channels_by_region[$channel->city]['channels'][] = $channel_data;
                }
            }
            foreach ($channels_by_region as $region => &$data) {
                $capital = Geography::CAPITALS[$region];
                uksort($data['cities'], function ($a, $b) use ($capital) {
                    return $a === $capital ? -1 : ($b === $capital ? 1 : strcmp($a, $b));
                });
            }

            ksort($channels_by_region);

//            $main_regions = ['Московская область', 'Ленинградская область', 'Новосибирская область', 'Свердловская область', 'Татарстан'];
//            uksort($channels_by_region, function ($a, $b) use ($main_regions) {
//                $a_pos = array_search($a, $main_regions);
//                $b_pos = array_search($b, $main_regions);
//                return $a_pos !== false ? ($b_pos !== false ? $a_pos - $b_pos : -1) : ($b_pos !== false ? 1 : strcmp($a, $b));
//            });

            $abroad = Channel::where(['is_abroad' => true])->whereNotNull('country')->where($params)->orderBy('order', 'ASC')->get();
            $abroad_by_country = [];
            foreach ($abroad as $channel) {
                $channel_data = [
                    'id' => $channel->id,
                    'name' => $channel->name,
                    'logo' => $channel->logo,
                    'url' => $channel->full_url,
                ];
                if (!isset($abroad_by_country[$channel->country])) {
                    $abroad_by_country[$channel->country] = ['cities' => [], 'channels' => []];
                }
                if (!$channel->city) {
                    $abroad_by_country[$channel->country]['channels'][] = $channel_data;
                } else {
                    if (!isset($abroad_by_country[$channel->country]['cities'][$channel->city])) {
                        $abroad_by_country[$channel->country]['cities'][$channel->city] = [];
                    }
                    $abroad_by_country[$channel->country]['cities'][$channel->city][] = $channel_data;
                }
            }
            ksort($abroad_by_country);
            $other = Channel::where(['is_federal' => false, 'is_regional' => false, 'is_abroad' => false])->where($params)->orderBy('order', 'ASC')->get();
            return [
                'federal' => $federal,
                'regional' => $channels_by_region,
                'abroad' => $abroad_by_country,
                'other' => $other
            ];
       });
    }

    public function buildOtherCategoriesList()
    {
        return Cache::remember('other_categories', CacheTimes::PAGE, function () {
            $global_programs = Program::withCount('records')->whereNull('channel_id')->get();
            $other_categories = Genre::withCount('records')->where(['type' => 'videos_other'])->get();
            foreach ($other_categories as $other_category) {
                $other_category->full_url = typed_route('records.[RECORD].other.category', false, $other_category->url);
                $other_category_record = Record::where(['other_category_id' => $other_category->id])->whereNotNull('cover_id')->inRandomOrder()->limit(1)->first();
                if ($other_category_record) {
                    $other_category->cover_url = $other_category_record->coverPicture->url;
                }
            }
            $other_categories = $global_programs->merge($other_categories);
            $unknown = Record::where(['is_interprogram' => false, 'is_advertising' => false, 'is_radio' => false])->doesntHave('program')->whereNull('other_category_id');
            $random_unknown = $unknown->clone()->inRandomOrder()->limit(1)->first();
            $other_categories->add((object)[
                'records_count' => $unknown->count(),
                'pending' => false,
                'name' => 'Прочее / неопознанные передачи',
                'full_url' => typed_route('records.[RECORD].other.category', false, 'unknown'),
                'cover_url' => $random_unknown->cover,
                'channels_history' => [],
            ]);
            return $other_categories;
        });
    }

    public function index($params)
    {
        if (!PermissionsHelper::allows('contentapprove')) {
            $params['pending'] = false;
        }
        $data = $this->buildChannelsList($params);
        $last_records = Record::approved()->where($params)->orderBy('original_added_at', 'desc')->paginate($params['is_radio'] ? 20 : 45);
        $data['params'] = $params;
        $data['last_records'] = $last_records;
        $data['events'] = [];

        $other_categories = !$params['is_radio'] ? $this->buildOtherCategoriesList() : [];
        $data['other_categories'] = $other_categories;
        // $data['events'] = HistoryEvent::approved()->orderBy('id', 'desc')->limit(3)->get();
        return view("pages.records.index", $data);
    }


    public function show($id)
    {
        $id = explode('-', (string)$id)[0];

        $data = Cache::remember('record_' . $id, CacheTimes::PAGE, function () use ($id) {
            $record = Record::approved()->where(['id' => $id])->firstOrFail();
            $playlist = null;
            $related_interprogram_packages = null;
            if ($record->interprogram_package_id && $record->interprogram_type != 22) {
                $package = $record->interprogramPackage;
                $records = $package->records->map(function ($record) {
                    return [
                        'order' => $record->internal_order,
                        'is_annotation' => false,
                        'data' => $record
                    ];
                });
                $annotations = $package->annotations->map(function ($annotation) {
                    return [
                        'order' => $annotation->order,
                        'is_annotation' => true,
                        'data' => $annotation
                    ];
                });
                $types_to_hide = [22];
                $records = $records->filter(function ($record) use ($types_to_hide) {
                    return !in_array($record['data']->interprogram_type, $types_to_hide);
                });
                $playlist = $records->merge($annotations)->sortBy('order');
                //$index = $playlist->search(function($playlist_record) use ($record) {
                //    return !$playlist_record['is_annotation'] && $playlist_record['data']->id === $record->id;
                //});

                if ($record->channel) {
                    $related_interprogram_packages = DesignPackage::where(['channel_id' => $record->channel->id])->where('id', '!=', $package->id)->inRandomOrder()->limit(5)->get();
                }
            }

            $related = [];


            if (!$playlist) {
                $related_interprogram = [];
                if ($record->is_interprogram && $record->interprogram_package_id > 0) {

                    if ($record->is_selected) {
                        $related_interprogram = Record::approved()->where(['interprogram_package_id' => $record->interprogram_package_id, 'is_selected' => true])->where('id', '!=', $record->id)->inRandomOrder()->limit(5)->get();
                    }
                    if (!$record->is_selected || count($related_interprogram) == 0) {
                        $related_interprogram = Record::approved()->where(['interprogram_package_id' => $record->interprogram_package_id])->where('id', '!=', $record->id)->inRandomOrder()->limit(5)->get();
                    }

                    $related[] = [
                        'heading' => 'Еще записи этого оформления',
                        'entity_name' => null,
                        'url' => null,
                        'items' => $related_interprogram
                    ];
                }
                $related_program = [];
                if ($record->program) {
                    $related_program = Record::approved()->where(['program_id' => $record->program_id])->where('id', '!=', $record->id)->inRandomOrder()->limit(5)->get();
                    $related[] = [
                        'heading' => 'Другие выпуски программы',
                        'entity_name' => $record->program->name,
                        'url' => $record->program->full_url,
                        'items' => $related_program
                    ];
                }

                $related_channel = [];
                if ($record->channel && !$record->is_advertising) {
                    $limit = (!$record->program || (count($related_program) === 0)) && !$record->interprogram_package_id ? 10 : 5;
                    $related_channel = Record::approved()->where(['channel_id' => $record->channel_id, 'is_advertising' => false])->where('id', '!=', $record->id)->inRandomOrder()->limit($limit)->get();
                    $related[] = [
                        'heading' => $record->is_radio ? "Ещё записи с радиостанции" : "Ещё записи с канала",
                        'entity_name' => $record->channel_name,
                        'url' => $record->channel->full_url,
                        'items' => $related_channel
                    ];
                }

                $related_advertising = [];

                if ($record->is_advertising) {
                    $related_advertising = Record::approved()->where(['is_radio' => $record->is_radio, 'is_advertising' => true, 'advertising_brand' => $record->advertising_brand])->where('id', '!=', $record->id)->inRandomOrder()->limit(10)->get();
                    if (count($related_advertising) == 0 && $record->year) {
                        $related_advertising = Record::approved()->where(['is_radio' => $record->is_radio, 'is_advertising' => true, 'year' => $record->year])->where('id', '!=', $record->id)->inRandomOrder()->limit(10)->get();
                    }

                    $related[] = [
                        'heading' => 'Ещё реклама',
                        'entity_name' => null,
                        'url' => null,
                        'items' => $related_advertising
                    ];
                }

                if ((count($related_interprogram) === 0) && (!$related_program || count($related_program) === 0) && (count($related_channel) === 0) && (count($related_advertising) === 0)) {
                    $related[] = [
                        'heading' => ' Другие записи',
                        'entity_name' => null,
                        'url' => null,
                        'items' => Record::approved()->where(['is_radio' => $record->is_radio])->where('id', '!=', $record->id)->inRandomOrder()->limit(10)->get()
                    ];
                }
            }
            $changed_name = false;
            if ($record->program && $record->program->channel_id != $record->channel_id) {
                $additional_channel_data = AdditionalChannel::where(['program_id' => $record->program->id, 'channel_id' => $record->channel_id])->first();
                if ($additional_channel_data) {
                    $changed_name = true;
                    if ($additional_channel_data->title != "") {
                        $record->program->name = $additional_channel_data->title;
                    }
                }
            }

            return [
                'record' => $record,
                'playlist' => $playlist,
                'related' => $related,
                'related_interprogram_packages' => $related_interprogram_packages,
                'changed_name' => $changed_name,
            ];
        });
        ViewsHelper::increment($data['record'], 'records');
        return view("pages.records.show", $data);
    }

    public function ucozRedirect($ucoz_id)
    {
        $record = Record::where(['ucoz_id' => $ucoz_id])->firstOrFail();
        return redirect($record->url);
    }

    public function other($start_params, $category_url = null)
    {
        $params = ['program_unknown' => true, 'is_advertising' => false];

        $category = null;
        if ($category_url) {
            if ($category_url == 'unknown') {
                $params['other_category_id'] = null;
                $params['is_interprogram'] = false;
                $params['is_radio'] = false;

            } else {
                $category = Genre::where(['url' => $category_url])->first();

                if (!$category) {
                    return redirect(route('index'));
                }
                $params['other_category_id'] = $category->id;
            }
        }
        $records_conditions = array_merge($start_params, $params);
        $categories = $this->buildOtherCategoriesList();

        return view("pages.records.other", [
            'category' => $category,
            'categories' => $categories,
            'is_radio' => $start_params['is_radio'],
            'records_conditions' => $records_conditions,
        ]);
    }


    public function add($params)
    {
        if (PermissionsHelper::isBanned()) {
            return redirect('/');
        }
        return view("pages.records.form", [
            'can_edit_all' => false,
            'data' => $params,
            'record' => null,
            'channels' => $this->getChannelsForForm($params)
        ]);
    }

    public function edit($id)
    {
        if (PermissionsHelper::isBanned()) {
            return redirect('/');
        }
        if (!auth()->user()) {
            return redirect(route('index'));
        }
        $record = Record::with('channel', 'program', 'program.coverPicture')->find($id);
        if (!$record) {
            return redirect(route('index'));
        }
        $record->append('source_hls');

        if (!$record->can_edit) {
            return redirect('/');
        }
        $can_edit_all = PermissionsHelper::allows('viedit');

        return view("pages.records.form", [
            'data' => [
                'is_radio' => request()->has('is_radio') ? !!request()->input('is_radio') : $record->is_radio
            ],
            'can_edit_all' => $can_edit_all,
            'record' => $record,
            'channels' => $this->getChannelsForForm([])
        ]);
    }


    public function getInfo()
    {
        $data = request()->validate([
            'record_id' => 'sometimes',
            'video_id' => 'required',
            'video_type' => 'required|in:youtube,vk'
        ]);

        $existing_records = Record::where(function($q) use($data) {
            $q->where('embed_code', 'LIKE', '%' . $data['video_id'] . '%');
            $q->orWhere(['external_id' => $data['video_id']]);
        });

        if (isset($data['record_id'])) {
            $existing_records = $existing_records->where('id', '!=', $data['record_id']);
        }
        $existing_records = $existing_records->get();

        if (count($existing_records)) {
            return [
                'status' => 0,
                'text' => 'Эта запись уже загружена на сайт',
                'list' => $existing_records
            ];
        }


        if ($data['video_type'] == 'youtube') {
            $response = (ExternalServicesHelper::youtubeVideo($data['video_id']));
            if (!isset($response->items[0])) {
                return [
                    'status' => 0,
                    'text' => 'Видео не найдено',
                ];
            }

            $video = $response->items[0];
            $duration = ExternalServicesHelper::youtubeVideoDuration($data['video_id']);
            $info = [
                'id' => $video->id,
                'title' => $video->snippet->title,
                'description' => $video->snippet->description,
                'player' => 'https://youtube.com/embed/' . $video->id,
                'code' => str_replace('URL', 'https://youtube.com/embed/' . $video->id, Records::IFRAME_CODE),
                'thumbnails' => array_map(function ($thumb) use ($video) {
                    return "https://img.youtube.com/vi/" . $video->id . "/" . $thumb . ".jpg";
                }, ['0', '1', '2', '3', 'hqdefault']),
                'duration' => $duration
            ];
        } else {
            $response = (ExternalServicesHelper::vkVideo($data['video_id']));
            if (!isset($response->response->items[0])) {
                return [
                    'status' => 0,
                    'text' => 'Видео не найдено',
                ];
            }

            $video = $response->response->items[0];
            $info = [
                'id' => $video->owner_id . ' ' . $video->id,
                'title' => $video->title,
                'description' => $video->description,
                'player' => $video->player,
                'code' => str_replace('URL', $video->player, Records::IFRAME_CODE),
                'thumbnails' => [
                    $video->image[count($video->image) - 1]->url
                ],
                'duration' => $video->duration
            ];
        }

        return [
            'status' => 1,
            'data' => $info
        ];
    }

    public function save()
    {
        if (!PermissionsHelper::allows('viadd') || PermissionsHelper::isBanned()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $user = auth()->user();
        $record = new Record([
            'ucoz_id' => Record::max('ucoz_id') + 1,
            'is_from_ucoz' => false,
            'original_added_at' => Carbon::now(),
            'author_username' => $user->username,
            'author_id' => $user->id,
            'description' => '',
            'short_contents' => '',
            'views' => 0
        ]);
        if (PermissionsHelper::allows('vipremod')) {
            $record->pending = true;
        }
        return $this->fillData($record, true);
    }

    public function update($id)
    {
        $record = Record::find($id);
        if (!$record) {
            return [
                'status' => 0,
                'text' => 'Видео не найдено'
            ];
        }
        if (!$record->can_edit || PermissionsHelper::isBanned()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        };
        return $this->fillData($record);
    }

    /** @var Record $record */
    protected function fillData($record)
    {
        $user = auth()->user();
        $is_radio = !!request()->input('is_radio', false);

        $errors = [];
        $type = request()->input('type');

        $record->is_interprogram = false;
        $record->is_advertising = false;
        $record->is_clip = false;

        switch ($type) {
            case 'programs':
                break;
            case 'interprogram':
            case 'program-design':
                $record->interprogram_package_id = request()->input('interprogram_package_id') > 0 ? request()->input('interprogram_package_id') : null;
                $record->interprogram_type = request()->input('interprogram_type') > 0 ? request()->input('interprogram_type') : null;
                // $errors['interprogram_type'] = "Выберите тип материала";

                $record->is_interprogram = true;
                break;
            case 'other':
                $record->other_category_id = request()->input('other.category_id', null);
                break;
            case 'advertising':
                $record->is_advertising = true;
                if (request()->input('advertising.brand') == '') {
                    $errors['advertising_brand'] = "Укажите рекламируемый бренд/товар";
                }
                $record->advertising_category = request()->input('advertising.category', '');
                $record->advertising_brand = request()->input('advertising.brand', '');
                $record->advertising_type = request()->input('advertising.type') > 0 ? request()->input('advertising.type') : null;
                $record->region = request()->input('advertising.region', '');
                $record->country = request()->input('advertising.country', '');

                break;
            case 'clips':
                $record->is_clip = true;
                break;
        }


        if ($type != 'other' && !$record->is_advertising) {
            if (!request()->input('channel.name') && !request()->input('channel_id') && !request()->input('channel.id') && request()->input('channel.unknown') !== true) {
                if ($is_radio) {
                    $errors['channel'] = "Выберите радиостанцию";
                } else {
                    if (!request()->input('is_advertising')) {
                        $errors['channel'] = "Выберите канал";
                    }
                }
            } elseif (request()->input('channel.unknown') !== true) {
                if (request()->input('channel.id') > 0) {
                    $record->channel_id = request()->input('channel.id');
                } elseif (request()->input('channel.name') != "") {
                    $channel = Channel::firstOrNew(['name' => request()->input('channel.name')]);
                    if (!$channel->exists) {
                        $channel->fill(['author_id' => $user->id, 'is_regional' => false, 'is_abroad' => false, 'pending' => !PermissionsHelper::allows('contentapprove')]);
                    }
                    $channel->save();
                    $record->channel_id = $channel->id;
                }
            }
        } else {
            $record->channel_id = null;
        }

        if ($type != 'other') {
            if (!request()->input('program.name') && !request()->input('program.id') && request()->input('program.unknown') !== true && (!$record->is_interprogram && !$record->is_clip && !$record->is_advertising)) {
                $errors['program'] = "Выберите программу";
            } else {
                if ($type == 'program-design' || (!$record->is_interprogram && !$record->is_advertising && !$record->is_clip)) {
                    if (request()->input('program.id') > 0) {
                        $record->program_id = request()->input('program.id');
                    } elseif (request()->input('program.name') != "") {
                        $program = Program::firstOrNew(['name' => request()->input('program.name'), 'channel_id' => $record->channel_id]);
                        if (!$program->exists) {
                            $program->fill(['author_id' => $user->id, 'cover' => '', 'channel_id' => $record->channel_id, 'pending' => !PermissionsHelper::allows('contentapprove')]);
                        }
                        $program->save();
                        $record->program_id = $program->id;
                    }
                } else {
                    $record->program_id = null;
                }
            }
        } else {
            $record->program_id = null;
        }
        if (request()->input('program.unknown') === true) {
            $record->program_id = null;
        }

        $upload = request()->input('record.upload', false);
        $has_uploaded_video = $upload && request()->has('record.uploaded_file_path');
        $storage = Storage::disk('media-storage');

        if ($has_uploaded_video) {
            $uploaded_file_path = request()->input('record.uploaded_file_path');
            if (!$storage->exists($uploaded_file_path)) {
                $errors['uploaded_file_path'] = 'Ошибка загрузки: файл не найден. Повторите загрузку ещё раз';
            } else {
                $record->use_own_player = true;
                $record->source_path = $uploaded_file_path;

                $thumbnail = !$is_radio ? MediaHelper::makeThumbnail($storage->path($uploaded_file_path)) : null;
                if ($thumbnail) {
                    $cover = Picture::firstOrNew([
                        'url' => $thumbnail
                    ]);
                    $cover->save();
                    $record->cover_id = $cover->id;
                }

                $duration = MediaHelper::getDuration($storage->path($uploaded_file_path));
                $record->length = $duration;
            }
        }

        if ($record->use_own_player && !$upload) {
            $record->use_own_player = false;
            $record->source_path = null;
        }

        if (!$record->use_own_player) {
            if (request()->input('record.code') == "") {
                $errors['url'] = "Укажите корректную ссылку";
            } else {
                $code = Purifier::clean(request()->input('record.code'), 'embed');
                $code = str_replace('&amp;', '&', $code);
                if (empty($code)) {
                    $errors['code'] = "Некорректный код для вставки видео";
                }
                $record->embed_code = $code;
                $record->external_id = ExternalServicesHelper::resolveId($record->embed_code);
            }
        }

        if (request()->has('record.duration') && request()->input('record.duration') > 0) {
            $record->length = (int)request()->input('record.duration');
        }

        $record->year = null;
        $record->month = null;
        $record->day = null;

        $record->year_start = null;
        $record->month_start = null;
        $record->day_start = null;
        $record->year_end = null;
        $record->month_end = null;
        $record->day_end = null;

        if (request()->input('date.range')) {
            $record->year_start = request()->input('date.year_start') > 0 ? request()->input('date.year_start') : null;
            $record->month_start = request()->input('date.month_start') > 0 ? request()->input('date.month_start') : ($record->year_start ? 1 : null);
            $record->day_start = request()->input('date.day_start') > 0 ? request()->input('date.day_start') : ($record->year_start ? 1 : null);
            $record->year_end = request()->input('date.year_end') > 0 ? request()->input('date.year_end') : null;
            $record->month_end = request()->input('date.month_end') > 0 ? request()->input('date.month_end') : ($record->year_end ? 12 : null);
            $record->day_end = request()->input('date.day_end') > 0 ? request()->input('date.day_end') : ($record->year_end ? 31 : null);

        } else {
            $record->year = request()->input('date.year') > 0 ? request()->input('date.year') : null;
            $record->month = request()->input('date.month') > 0 ? request()->input('date.month') : null;
            $record->day = request()->input('date.day') > 0 ? request()->input('date.day') : null;
            if ($record->year && $record->month && $record->day) {
                $record->date = Carbon::createFromDate($record->year, $record->month, $record->day);
            }
        }

        $record->short_description = request()->input('short_description', '');
        $record->description = strip_tags(request()->input('description', ''));
        $record->source = request()->input('source', '');

        if (request()->input('is_selected')) {
            $record->is_selected = !!request()->input('is_selected');
        }

        $cover_url = null;
        if (request()->input('record.thumbnail_id') > 0) {
            $record->cover_id = request()->input('record.thumbnail_id');
        } else {
            if (request()->input('record.thumbnail_url') != "") {
                $cover_url = request()->input('record.thumbnail_url');
            } elseif (request()->has('record.thumbnails') && is_array(request()->input('record.thumbnails')) && count(request()->input('record.thumbnails')) > 0) {
                $covers = request()->input('record.thumbnails');
                $cover_url = $covers[count($covers) - 1];
            }
            if ($cover_url) {
                $cover = Picture::where(['url' => $cover_url])->first();
                if (!$cover) {
                    $cover = new Picture();
                    $cover->loadFromURL($cover_url, sha1($cover_url), "imported/" . date("dmY"));
                    $cover->compress();

                    $cover->save();
                }
                $record->cover_id = $cover->id;
            }
        }

        if (request()->input("title", "") != "") {
            $record->title = request()->input('title');
        } else {
            $record->title = $record->generateTitle();
        }
        $record->title = strip_tags($record->title);

        if ($record->exists && PermissionsHelper::allows('viedit')) {
            if (request()->has('original_added_at')) {
                $record->original_added_at = Carbon::parse(request()->input('original_added_at'));
            }
            if (request()->has('author_id')) {
                $record->author_id = request()->input('author_id');
            }
        }

        if (count($errors) > 0) {
            return [
                'status' => 0,
                'text' => 'В форме есть ошибки',
                'errors' => $errors
            ];
        }

        if ($has_uploaded_video && str_starts_with($uploaded_file_path, 'temp-upload/')) {
            $new_file_path = str_replace('temp-upload/', 'videos/', $uploaded_file_path);

            $storage->move($uploaded_file_path, $new_file_path);

            $uploaded_file_path = '/' . $new_file_path;
            $record->source_path = $uploaded_file_path;
        }

        $record->is_radio = $is_radio;
        if ($record->channel && $record->channel->is_radio) {
            $record->is_radio = true;
        }
        $is_new = !$record->id;

        ActionsLogHelper::create($record, $is_new ? Actions::Create : Actions::Update);
        $record->setSupposedDate();

        if (!$record->use_own_player && request()->input('record.move_to_storage')) {
            DownloadExternalVideo::dispatch($record);
        }

        $record->clearCache();

        $text = $record->is_radio ? ($is_new ? 'Радиозапись добавлена' : 'Радиозапись обновлена') : ($is_new ? 'Видео добавлено' : 'Видео обновлено');
        $text .= '<a target=_blank href="' . $record->url . '">Перейти</a>';

        return [
            'status' => 1,
            'text' => $text,
            'data' => [
                'record' => $record
            ]
        ];
    }

    public function search(RecordsSearchRequest $request)
    {
        $data = $request->validated();
        $is_commercials_search = $request->isCommercialsSearch();

        $records = Record::approved()->where(['is_radio' => isset($data['is_radio']) ? $data['is_radio'] : false]);

        $show_programs = false;

        if ($is_commercials_search) {
            $data['type'] = 'advertising';
        }

        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'programs':
                    $records->where(function ($q) {
                        $q->where(['is_interprogram' => false]);
                        $q->where(['is_advertising' => false]);
                        $q->where(['is_clip' => false]);
                    });
                    break;
                case 'interprogram':
                    $records->where(function ($q) {
                        $q->where(['is_interprogram' => true]);
                        $q->whereNull('program_id');
                    });
                    break;
                case 'program-design':
                    $records->where(function ($q) {
                        $q->where(['is_interprogram' => true]);
                        $q->whereNotNull('program_id');
                    });
                    break;
                case 'advertising':
                    $records->where(['is_advertising' => true]);
                    break;
                case 'clips':
                    $records->where(['is_clip' => true]);
                    break;
                case 'other':
                    $records->where(function ($q) {
                        $q->whereNull('channel_id');
                        $q->where(['is_advertising' => false]);
                        $q->where(['is_clip' => false]);
                    });
                    break;
                default:
                    break;
            }
        }

        if (isset($data['search'])) {
            $show_programs = !isset($data['type']) || $data['type'] == 'programs';

            $need_sort = !isset($data['sort']) || $data['sort'] == 'relevance';
            $records->search($data['search'], $need_sort);
            // $records->where(function ($q) use ($data) {
            //    $q->whereFullText(['title', 'short_description', 'description'], $data['search']);
//                $q->where('title', 'LIKE', '%' . $data['search'] . '%');
//                $q->orWhere('short_description', 'LIKE', '%' . $data['search'] . '%');
//                $q->orWhere('description', 'LIKE', '%' . $data['search'] . '%');
            //  $q->orWhere('advertising_brand', 'LIKE', '%' . $data['search'] . '%');
            // });
        }

        if (isset($data['exclude_ids']) && count($data['exclude_ids']) > 0) {
            $records = $records->whereNotin('id', $data['exclude_ids']);
        }
        if (isset($data['is_interprogram'])) {
            if ($data['is_interprogram']) {
                $show_programs = false;
            }
            $records = $records->where(['is_interprogram' => $data['is_interprogram']]);
        }

//        if (request()->has('interprogram_type')) {
//            $records = $records->where('interprogram_type', request()->input('interprogram_type'));
//        }
//        if (request()->has('interprogram_package_id')) {
//            $records = $records->where('interprogram_package_id', request()->input('interprogram_package_id'));
//        }

        $has_dates = false;

        if (
            (isset($data['date']['year']) && $data['date']['year'] > -1) ||
            (isset($data['date']['year_start']) && $data['date']['year_start'] > -1) ||
            (isset($data['date']['year_end']) && $data['date']['year_end'] > -1)
        ) {
            $has_dates = true;
            $records = $records->where(function ($query) use ($data) {
                $date_start = null;
                $date_end = null;
                if (isset($data['date']['range']) && $data['date']['range']) {
                    $date_start = Carbon::createFromDate(
                        isset($data['date']['year_start']) && $data['date']['year_start'] > -1 ? $data['date']['year_start'] : 1951,
                        isset($data['date']['month_start']) && $data['date']['month_start'] > -1 ? $data['date']['month_start'] : 1,
                        isset($data['date']['day_start']) && $data['date']['day_start'] > -1 ? $data['date']['day_start'] : 1,
                    )->startOfDay();
                    $date_end = Carbon::createFromDate(
                        isset($data['date']['year_end']) && $data['date']['year_end'] > -1 ? $data['date']['year_end'] : 2010,
                        isset($data['date']['month_end']) && $data['date']['month_end'] > -1 ? $data['date']['month_end'] : 12,
                        isset($data['date']['day_end']) && $data['date']['day_end'] > -1 ? $data['date']['day_end'] : 31,
                    )->endOfDay();
                } else {
                    if (isset($data['date']['month']) && $data['date']['month'] > -1) {
                        if (isset($data['date']['day']) && $data['date']['day'] > -1) {
                            $date = Carbon::createFromDate($data['date']['year'], $data['date']['month'], $data['date']['day']);
                            $date_start = $date->copy()->startOfDay();
                            $date_end = $date->copy()->endOfDay();
                        } else {
                            $date = Carbon::createFromDate($data['date']['year'], $data['date']['month'], 1);
                            $date_start = $date->copy()->startOfMonth();
                            $date_end = $date->copy()->endOfMonth();
                        }
                    } else {
                        $date = Carbon::createFromDate($data['date']['year'], 1, 1);
                        $date_start = $date->copy()->startOfYear();
                        $date_end = $date->copy()->endOfYear();
                    }
                }
                $query->whereBetween('supposed_date', [$date_start, $date_end]);
                if (isset($data['type']) && in_array($data['type'], ['interprogram', 'advertising'])) {
                    $query->orWhere(function ($q) use ($date_start, $date_end) {
                        $q->whereDate('supposed_date', '<=', $date_start);
                        $q->whereDate('supposed_date_end', '>=', $date_end);
                    });
                }
            });
        }

        if (isset($data['search']) && isset($data['sort']) && $data['sort'] == 'relevance') {

        }  elseif (isset($data['sort']) && $data['sort'] != 'relevance') {
            $records = $records->orderBy($data['sort'], isset($data['sort_order']) ? $data['sort_order'] : 'desc');
            $params['sort'] = $data['sort'];
        } else {
            $records = $records->orderBy('id', 'desc');
        }

        $counts = [
            'channels' => null,
            'programs' => null,
            'advertising_brands' => null,
            'advertising_categories' => null,
            'advertising_regions' => null,
            'advertising_countries' => null,
        ];

        if (isset($data['search']) || $has_dates) {
            $counts['channels'] = $records->clone()->select('channel_id', \DB::raw('COUNT(*) as count'))->groupBy('channel_id')->get()->pluck('count', 'channel_id');
            if (isset($data['channels'])) {
                $counts['programs'] = $records->clone()->whereIn('channel_id', $data['channels'])->select('program_id', \DB::raw('COUNT(*) as count'))->groupBy('program_id')->get()->pluck('count', 'program_id');
            }
            if (isset($data['type']) && $data['type'] == 'advertising') {
                $counts['advertising_brands'] = $records->clone()->whereNotNull('advertising_brand')->select('advertising_brand', \DB::raw('COUNT(*) as count'))->groupBy('advertising_brand')->get()->pluck('count', 'advertising_brand')->sortDesc();
                $counts['advertising_categories'] = $records->clone()->whereNotNull('advertising_category')->select('advertising_category', \DB::raw('COUNT(*) as count'))->groupBy('advertising_category')->get()->pluck('count', 'advertising_category')->sortDesc();
                $counts['advertising_regions'] = $records->clone()->whereNotNull('region')->select('region', \DB::raw('COUNT(*) as count'))->groupBy('region')->get()->pluck('count', 'region')->sortDesc();
                $counts['advertising_countries'] = $records->clone()->whereNotNull('country')->select('country', \DB::raw('COUNT(*) as count'))->groupBy('country')->get()->pluck('count', 'country')->sortDesc();
            }
        }

        if (isset($data['type']) && $data['type'] == 'advertising') {
            if (isset($data['advertising_type'])) {
                if ($data['advertising_type'] == -1) {
                    $records->whereNull('advertising_type');
                } else {
                    $records->where(['advertising_type' => $data['advertising_type']]);
                }

            }
            if (isset($data['advertising_brands']) && count($data['advertising_brands']) > 0) {
                $records->whereIn('advertising_brand', $data['advertising_brands']);
            }
            if (isset($data['advertising_categories']) && count($data['advertising_categories']) > 0) {
                $records->whereIn('advertising_category', $data['advertising_categories']);
            }

            if (isset($data['advertising_countries']) && count($data['advertising_countries']) > 0) {
                $records->where(function ($q) use ($data) {
                    $countries = array_filter($data['advertising_countries']);
                    $q->whereIn('country', $countries);
                    if (count($countries) != count($data['advertising_countries'])) {
                        $q->orWhereNull('country');
                    }
                });
            }
            if (isset($data['advertising_regions']) && count($data['advertising_regions']) > 0) {
                $records->where(function ($q) use ($data) {
                    $regions = array_filter($data['advertising_regions']);
                    $q->whereIn('region', $regions);
                    if (count($regions) != count($data['advertising_regions'])) {
                        $q->orWhereNull('region');
                    }
                });
            }
        }
        if (isset($data['type']) && $data['type'] == 'interprogram' && isset($data['interprogram_type'])) {
            $records = $records->where(['interprogram_type' => $data['interprogram_type']]);
        }

        if (isset($data['channels']) && count($data['channels']) > 0) {
            $records = $records->whereIn('channel_id', $data['channels']);
        }
        if (isset($data['programs']) && count($data['programs']) > 0) {
            $show_programs = false;
            $records = $records->whereIn('program_id', $data['programs']);
        }

        $programs = null;

        if ($show_programs) {
            $programs = $records->clone()->whereNotNull('program_id')->select('program_id', \DB::raw('COUNT(*) as count'))->groupBy('program_id')->orderByDesc('count')->get()->sortByDesc('count')->take(6)->map(function ($record) {
                return $record->program;
            })->values();
            $programs->each->append('cover_url', 'full_url', 'channels_history');
        }

        $records = $records->paginate(30);
        $records->getCollection()->each->append('cover');
        if (isset($data['type']) && $data['type'] == 'advertising') {
            $records->getCollection()->each(function ($q) {
                //  dd($q->title); //todo advertising titles
            });
        }

        $params = array_filter($data);

        $periods = Periods::LIST;

        $response = [
            'programs' => $programs,
            'params' => $params,
            'results' => $records->appends(request()->except('page')),
            'counts' => $counts,
            'periods' => $periods,
            'is_commercials_search' => $is_commercials_search
        ];
        if (request()->isMethod('post')) {
            return ['status' => 1, 'data' => $response];
        }
        return view("pages.records.search", $response);
    }

    public function massEdit()
    {
        if (PermissionsHelper::allows('viedit')) {
            $ids = request()->input('ids', []);
            $params = request()->input('params', []);
            Record::whereIn('id', $ids)->update($params);
            return ['status' => 1, 'text' => 'Обновлено'];
        } else {
            return ['status' => 0, 'text' => 'Ошибка доступа'];
        }
    }

    protected function afterDelete($record)
    {
        $record->clearCache();
        if ($record->use_own_player && str_contains($record->source_path, "videos/")) {
            $source_path = public_path($record->source_path);
            $do_not_delete = Record::where(['source_path' => $source_path])->where('id', '!=', $record->id)->count() > 0;
            if (!$do_not_delete) {
                if (file_exists($source_path)) {
                    unlink($source_path);
                }
                if (strpos($record->original_cover, "video_covers/") !== false) {
                    $screenshot_path = public_path($record->original_cover);
                    if (file_exists($screenshot_path)) {
                        unlink($screenshot_path);
                    }
                }
            }
        }
        return [
            'status' => 1,
            'text' => 'Запись удалена',
            'redirect_to' => typed_route('records.[RECORD].index', $record->is_radio)
        ];
    }


    public function categories()
    {
        $categories = Genre::all();
        return [
            'status' => 1,
            'data' => [
                'categories' => $categories
            ]
        ];
    }



    public function ajax()
    {
        $conditions = request()->input('conditions');
        $records_data = RecordsHelper::get($conditions, true);
        $data = [
            'ajax' => true,
            'records_data' => $records_data,
            'conditions' => $conditions
        ];
        if (request()->has('search')) {
            $data['search'] = request()->input('search');
        }
        if (request()->has('block_title')) {
            $data['block_title'] = request()->input('block_title');
        }
        if (request()->has('title_param')) {
            $data['title_param'] = request()->input('title_param');
        }
        return [
            'status' => 1,
            'data' => [
                'html' => view('blocks.records.list', $data)->render()
            ]
        ];
    }


    public function setTelegramID()
    {
        $record = Record::find(request()->input('record_id'));
        if (!$record) {
            return [
                'status' => 0,
                'text' => 'Видео не найдено'
            ];
        }
        if (!$record->can_edit || PermissionsHelper::isBanned()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        };

        $record->clearCache();

        $record->use_own_player = true;
        $record->telegram_id = request()->input('telegram_id');
        $record->save();
        return [
            'status' => 1,
            'text' => 'Видео будет воспроизводиться из Telegram',
            'redirect_to' => $record->url
        ];
    }

    public function thumbnail()
    {
        $record = Record::find(request()->input('record_id'));
        if (!$record) {
            return [
                'status' => 0,
                'text' => 'Видео не найдено'
            ];
        }
        if (!$record->can_edit || PermissionsHelper::isBanned()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        };


        if (!$record->source_path) {
            $thumbnail = ExternalServicesHelper::getThumbnail($record);
            if (!$thumbnail) {
                return [
                    'status' => 0,
                    'text' => 'Не удалось обновить превью',
                ];
            }
        } else {
            $seconds = request()->input('seconds');
            if (!$seconds || $seconds == "") {
                $seconds = null;
            }

            $thumbnail = MediaHelper::makeThumbnail(Storage::disk('media-storage')->path($record->source_path), $seconds);
        }

        $cover = Picture::firstOrNew([
            'url' => $thumbnail
        ]);
        $cover->save();

        $record->cover_id = $cover->id;
        $record->save();
        return [
            'status' => 1,
            'text' => 'Превью обновлено',
            'redirect_to' => $record->url
        ];
    }

    public function embed($id)
    {
        $record = Record::findOrFail($id);
        return view('pages.embed', ['record' => $record]);
    }

    public function calendar($is_radio = false)
    {
        $years = Record::approved()->where(['is_radio' => $is_radio])->whereNotNull('year')->selectRaw('count(*) as count_year, year')->where('year', '>', 1950)->groupBy('year')->orderBy('year', 'asc')->get();
        return view('pages.records.calendar', ['years' => $years, 'is_radio' => $is_radio]);
    }

    public function calendarYear($year, $is_radio = false)
    {
        $year_records = Record::approved()->select(['month', 'day', 'year'])->where(['is_radio' => $is_radio, 'is_advertising' => false])->where(['year' => $year])->get();
        $records_by_month = [];

        $month_names = DatesHelper::monthNames();
        foreach ($year_records as $year_record) {
            $month = $year_record->month && $year_record->month > 0 ? $year_record->month : 0;
            if ($month > 12 || $month < 0) {
                continue;
            }

            if (!isset($records_by_month[$month])) {
                $month_name = isset($month_names[$year_record->month - 1]) ? $month_names[$year_record->month - 1] : "-";
                $records_by_month[$month] = ['name' => $month > 0 ? $month_name . " " . $year : "Неизвестно", 'count' => 0];
            }

            $records_by_month[$month]['count']++;
        }
        ksort($records_by_month);

        return view('pages.records.calendar-year', ['year' => $year, 'records_by_month' => $records_by_month, 'is_radio' => $is_radio]);
    }

    public function calendarMonth($year, $month, $is_radio = false)
    {
        $month_names = DatesHelper::monthNames();
        if ($year == "-") {
            $month_name = $month_names[$month - 1];
            $month_name_full = "Записи за " . mb_strtolower($month_names[$month - 1], "UTF-8");
            $month_name_parental_case = "";
            $month_records = Record::approved()->where(['is_radio' => $is_radio, 'is_advertising' => false])->where(['month' => $month])->get();
        } else {
            if ($month > 12 || $month < 0) {
                return redirect(route('records.' . ($is_radio ? 'radio' : 'video') . '.calendar.year', $year));
            }

            if ($month > 0) {
                $month_name = $month_names[$month - 1];
                $month_name_full = "Записи за " . mb_strtolower($month_names[$month - 1], "UTF-8") . " $year";
                $month_name_parental_case = DatesHelper::monthNamesParentalCase()[$month - 1];
                $month_records = Record::approved()->where(['is_radio' => $is_radio, 'is_advertising' => false])->where(['year' => $year, 'month' => $month])->get();
            } else {
                $month_name = "Неизвестный месяц";
                $month_name_full = "Записи за $year год с неизвестной датой";
                $month_name_parental_case = "";
                $month_records = Record::approved()->where(['is_radio' => $is_radio, 'is_advertising' => false])->where(['year' => $year, 'month' => null])->get();
            }
        }
        if ($year == "-") {
            $year = 2000;
        }
        $records_by_day = [];
        $channels = Channel::whereIn('id', $month_records->pluck('channel_id')->unique())->get();
        $channels_by_id = [];
        $date = Carbon::createFromDate($year, $month, 1);
        foreach ($channels as $channel) {
            $name = ChannelName::where(['channel_id' => $channel->id])->whereDate('date_start', '<=', $date)->whereDate('date_end', '>=', $date)->first();
            if (!$name) {
                $name = ChannelName::where(['channel_id' => $channel->id])->whereDate('date_start', '<=', $date)->where(['date_end' => null])->first();
            }
            if ($name) {
                if ($name->name != "") {
                    $channel->name = $name->name;
                }
                if ($name->logo) {
                    $channel->logo = $name->logo;
                }
            }
            $channel->logo_path = $channel->logo ? $channel->logo->url : '/pictures/logo-grey.svg';
            $channels_by_id[$channel->id] = $channel;
        }
        foreach ($month_records as $month_record) {
            $day = $month_record->day && $month_record->day > 0 ? $month_record->day : 0;
            if (!isset($records_by_day[$day])) {
                $records_by_day[$day] = [];
            }
            if (!isset($records_by_day[$day][$month_record->channel_id])) {
                $records_by_day[$day][$month_record->channel_id] = [];
            }
            $records_by_day[$day][$month_record->channel_id][] = $month_record;
        }
        ksort($records_by_day);
        if (isset($records_by_day[0])) {
            $unknown = $records_by_day[0];
            unset($records_by_day[0]);
            $records_by_day = $records_by_day + [0 => $unknown];
        }


        return view('pages.records.calendar-month', [
            'channels_by_id' => $channels_by_id,
            'month_name' => $month_name,
            'month_name_full' => $month_name_full,
            'month_name_parental_case' => $month_name_parental_case,
            'year' => $year,
            'month' => $month,
            'records_by_day' => $records_by_day,
            'is_radio' => $is_radio
        ]);
    }

    public function playlistAjax($id)
    {
        $record = Record::find($id);
        if (!$record || $record->pending) {
            return [
                'status' => 0,
                'text' => 'Запись не найдена',
            ];
        }
        ViewsHelper::increment($record, 'records');
        $player_code = trim(view('blocks.records.player', ['autoplay' => true, 'record' => $record])->render());

        $share_title = $record->title_without_tags;
        $share_url = typed_route('records.[RECORD].show', $record->is_radio, $record->id);

        $record_description = $record->description;
        $record_info = trim(view('blocks.records.info', ['record' => $record])->render())
            . trim(view('blocks.global.share', ['share_title' => $share_title, 'share_url' => $share_url])
                ->render());
        $record_comments = view("blocks.comments.list", ['ajax' => false, 'page' => 1, 'conditions' => ['material_type' => MaterialTypes::TYPE_RECORDS, 'material_id' => $record->id]])->render();

        return [
            'status' => 1,
            'data' => [
                'record' => [
                    'id' => $record->id,
                    'title' => $record->title_without_tags,
                    'url' => $record->url,
                ],

                'html' => [
                    [
                        'replace' => '#record_title',
                        'html' => $record->title_without_tags,
                    ],
                    [
                        'replace' => '.record-page__player',
                        'html' => $player_code,
                    ],
                    [
                        'replace' => '.box--comments',
                        'html' => $record_comments,
                    ],
                    [
                        'replace' => '.record-page__bottom',
                        'html' => $record_info,
                    ],
                    [
                        'replace' => '.record-page__description',
                        'html' => $record_description,
                    ],
                ]
            ]
        ];
    }

    public function similar()
    {
        $type = request()->input('type');
        $similar = Record::query();
        if (request()->input('id') > 0) {
            $similar = $similar->where('id', '!=', request()->input('id'));
        }
        $similar = $similar->where(function ($q) use ($type) {
            if (request()->has('title')) {
                $q->where('title', 'LIKE', '%' . request()->input('title') . '%');
            }
            switch ($type) {
                case 'programs':
                    $q->orWhere(function ($q) {
                        $q->where(['program_id' => request()->input('program.id')]);
                        $q->where(['channel_id' => request()->input('channel.id')]);
                        $q->where(['year' => request()->input('date.year')]);
                        $q->where(['month' => request()->input('date.month')]);
                        $q->where(['day' => request()->input('date.day')]);

                        $q->where(['is_interprogram' => false]);
                        $q->where(['is_advertising' => false]);
                        $q->where(['is_clip' => false]);
                    });
                    break;
                case 'advertising':
                    $q->orWhere(function ($q) {
                        $q->where(['is_advertising' => true]);
                        $q->where(function ($q) {
                            $q->where('advertising_brand', 'LIKE', '%' . request()->input('advertising.brand'). '%');
                            $q->orWhere('title', 'LIKE', '%' . request()->input('advertising.brand'). '%');
                        });
                        if (request()->has('date.year_start') || request()->has('date.year')) {
                            $q->where(function ($q) {
                                $year = request()->input('date.year_start', request()->input('date.year'));
                                $q->where(['year_start' => $year]);
                                $q->orWhere(['year' => $year]);
                            });
                        }
                    });
                    break;
                default:
                    break;
            }
        });
        $similar = $similar->get();
        $similar->each->append(['source_hls', 'source_telegram']);
        return [
            'status' => 1,
            'data' => $similar
        ];
    }

    public function complaint()
    {
        $rules = [
            'description' => 'sometimes',
            'record_id' => 'required|exists:records,id',
            'type' => Rule::enum(RecordComplaintTypes::class),
        ];
        if (!auth()->user() && request()->input('type') != RecordComplaintTypes::PlayerNotWorking->value) {
            $rules['contact'] = 'required';
        }
        $data = request()->validate($rules);
        $complaint_exists = RecordComplaint::where(['record_id' => $data['record_id'], 'type' => $data['type'], 'description' => isset($data['description']) ? $data['description'] : ''])->whereDate('created_at', '>=', Carbon::now()->subDays(1))->count() > 0;

        if ($complaint_exists) {
            return [
                'status' => 1,
                'text' => 'Жалоба уже на рассмотрении'
            ];
        }

        $complaint = new RecordComplaint($data);
        if (auth()->user()) {
            $complaint->user_id = auth()->user()->id;
        }
        $complaint->save();

        return [
            'status' => 1,
            'text' => 'Ваша жалоба отправлена, спасибо!'
        ];
    }


    private function getChannelsForForm($params)
    {
        return Channel::approved()->with(['logo', 'names'])->orderBy('order', 'asc')->where($params)->get();
    }

    public function getYoutubeVideoIds($author_id)
    {
        $video_ids = Record::where(['author_id' => $author_id])->where('embed_code', 'LIKE', '%youtu%')->pluck('embed_code')->map(function ($video_id) {
            $video_id = explode('embed/', $video_id)[1];
            $video_id = explode('"', $video_id)[0];
            return $video_id;
        });
        return [
            'status' => 1,
            'data' => [
                'video_ids' => $video_ids
            ]
        ];
    }

    public function getVideosForAuthor($author_id)
    {
        $videos = Record::where(['author_id' => $author_id])->select('title', 'id')->get();
        return [
            'status' => 1,
            'data' => [
                'videos' => $videos
            ]
        ];
    }

    public function apiSearch()
    {
        $search = request()->input('q') ? explode(';', request()->input('q')) : [];
        $exclude = request()->input('e') ? explode(';', request()->input('e')) : [];

        $category = request()->input('c');
        $genre = null;
        if ($category != '') {
            $genre = Genre::where(['url' => $category])->first();
        }

        $programs = Program::query()->where(function ($q) use ($genre, $search) {
            if (count($search) > 0) {
                $q->where('name', 'LIKE', '%' . $search[0] . '%');
                for ($i = 1; $i < count($search); $i++) {
                    $q = $q->orWhere('name', 'LIKE', '%' . $search[$i] . '%');
                }
            }
            if ($genre) {
                $q = $q->where(['genre_id' => $genre->id]);
            }
        });
        if (count($exclude) > 0) {
            $programs->where(function ($q) use ($exclude) {
                foreach ($exclude as $value) {
                    $q = $q->where('name', 'NOT LIKE', '%' . $value . '%');
                }
            });
        }

        $records = Record::query()->where(function ($q) use ($programs, $search) {
            if (count($search) > 0) {
                $q->where('title', 'LIKE', '%' . $search[0] . '%');
                for ($i = 1; $i < count($search); $i++) {
                    $q = $q->orWhere('title', 'LIKE', '%' . $search[$i] . '%');
                }
            }
            $q->orWhereIn('program_id', $programs->pluck('id'));
        });

        if (count($exclude) > 0) {
            $records->where(function ($q) use ($exclude) {
                foreach ($exclude as $value) {
                    $q = $q->where('title', 'NOT LIKE', '%' . $value . '%');
                }
            });
        }

        $records = $records->get();
        if (request()->input('return') == 'titles') {
            foreach ($records as $record) {
                $download_url = null;
                if ($record->telegram_id) {
                    $download_url = $record->all_telegram_sources[0];
                } else if ($record->download_url != '') {
                    $download_url = $record->download_url;
                } else {
                    preg_match('/iframe(.*?)src="(.*?)"/', $record->embed_code, $matches);
                    if (!isset($matches[2]) || $matches[2] == "") {
                        preg_match('/iframe(.*?)src=(.*?) (.*?)/', $record->embed_code, $matches);
                    }
                    $download_url = $matches[2];
                }
                if ($download_url != '' && strpos($download_url, 'youtu') === false && strpos($download_url, 'dailymotion') === false) {
                    echo $record->id . PHP_EOL . $record->title . PHP_EOL . $download_url . PHP_EOL . PHP_EOL;
                }
            }
            return;
        }
        return $records;
    }

}
