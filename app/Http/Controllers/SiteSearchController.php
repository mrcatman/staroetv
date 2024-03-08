<?php

namespace App\Http\Controllers;

use App\AdditionalChannel;
use App\Article;
use App\Channel;
use App\ChannelName;
use App\Helpers\PermissionsHelper;
use App\Program;
use App\Questionnaire;
use App\QuestionnaireAnswer;
use App\QuestionnaireVariant;
use App\Record;

class SiteSearchController extends Controller {

    public function search() {
        $search = request()->input('search');
        if (mb_strlen($search, "UTF-8") < 3) {
            return [
                'status' => 0,
                'text' => 'Задан слишком короткий поисковый запрос'
            ];
        }
        $limit = 5;
        $other_limit = 500;

        $results = [];

        $channels_types = [
            'channels' => ['is_radio' => false, 'title' => 'Телеканалы'],
            'radio' => ['is_radio' => true, 'title' => 'Радиостанции'],
        ];
        $radio_ids = Channel::where(['is_radio' => true])->pluck('id');
        foreach ($channels_types as $channels_type => $channels_type_data) {
            $channels = Channel::approved()->where(['is_radio' => $channels_type_data['is_radio']])->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%'.$search.'%');
            })->orderBy('id', 'desc');
            $list_count = $channels->count();
            $channels = $channels->limit($other_limit)->get();

            if (count($channels) < $limit) {
                $additional = ChannelName::whereNotIn('channel_id', $channels->pluck('id'))->where('name', 'LIKE', '%'.$search.'%');
                if ($channels_type_data['is_radio']) {
                    $additional = $additional->whereIn('channel_id', $radio_ids);
                } else {
                    $additional = $additional->whereNotIn('channel_id', $radio_ids);
                }
                $additional = $additional->orderBy('id', 'desc');
                $list_count += $additional->pluck('name')->unique()->count();
                $additional = $additional->limit($other_limit - count($channels))->groupBy('channel_id')->get();
                foreach ($additional as $additional_item) {
                    $channel = $additional_item->channel;
                    if ($channel) {
                        if ($additional_item->logo) {
                            $channel->logo = $additional_item->logo;
                        }
                        $channel->name = $additional_item->name;
                        $channels->push($channel);
                    }
                }
            }
            $lowercase_search = mb_strtolower($search, 'UTF-8');
            foreach ($channels as $index => $channel) {
                if(mb_strtolower($channel->name, 'UTF-8') == $lowercase_search) {
                    $channels->forget($index);
                    $channels->prepend($channel);
                }
            }
            if (count($channels) > 0) {
                $data = [
                    'count' => $list_count,
                    'name' => 'channels',
                    'title' => $channels_type_data['title'],
                    'list' => []
                ];
                foreach ($channels as $item) {
                    $data['list'][] = [
                        'url' => $item->full_url,
                        'title' => $item->name,
                        'picture' => $item->logo ? $item->logo->url : '/pictures/logo-grey.svg',
                        'description' => '',
                        'additional' => $item->is_regional ? $item->city : ($item->is_abroad ? $item->country : '')
                    ];
                }
                $results[] = $data;
            }
        }

        $programs = Program::withCount('records')->approved()->where(function($q) use ($search) {
            $q->where('name', 'LIKE', '%'.$search.'%');
        })->orderBy('records_count', 'desc');
        $list_count = $programs->count();
        $programs = $programs->limit($other_limit)->get();
        if (count($programs) < $other_limit) {
            $additional = AdditionalChannel::where('title', 'LIKE', '%'.$search.'%')->limit($other_limit - count($programs))->groupBy('program_id')->orderBy('id', 'desc');
            $list_count += $additional->pluck('program_id')->unique()->count();
            $additional = $additional->get();
            foreach ($additional as $additional_item) {
                $program = Program::find($additional_item->program_id);
                if ($program) {
                    $program->name = $additional_item->title;
                    $programs->push($program);
                }
            }
        }
        if (count($programs) > 0) {
            $data = [
                'count' => $list_count,
                'name' => 'programs',
                'title' => 'Передачи',
                'list' => []
            ];
            foreach ($programs as $item) {
                $data['list'][] = [
                    'url' => $item->full_url,
                    'title' => $item->name,
                    'picture' => $item->cover,
                    'additional' => $item->channels_names_list,
                    'description' => ''
                ];
            }
            $results[] = $data;
        }
        $sections = [
            'video' => [
                'model' => Record::approved()->where(['is_radio' => false]),
                'fields' => ['title', 'short_description', 'description', 'advertising_brand'],
                'description_default' => 'description',
                'description_fields' => ['short_description'],
                'title_field' => 'title',
                'picture_field' => 'cover',
                'title' => 'ТВ-записи'
            ],
            'radio' => [
                'model' => Record::approved()->where(['is_radio' => true]),
                'fields' => ['title', 'short_description', 'description', 'advertising_brand'],
                'description_default' => 'description',
                'description_fields' => ['short_description'],
                'title_field' => 'title',
                'picture_field' => '',
                'title' => 'Радиозаписи'
            ],
        ];
        foreach ($sections as $section_name => $section_data) {
            $list = $section_data['model']->where(function($q) use ($search, $section_data) {
                $first_property = array_shift( $section_data['fields']);
                $q->where($first_property, 'LIKE', '%'. $search .'%');
                foreach ($section_data['fields'] as $field) {
                    $q->orWhere($field, 'LIKE', '%'. $search .'%');
                }
            })->orderBy('id', 'desc');
            $list_count = $list->count();
            if ($list_count > 0) {
                $list = $list->limit($limit)->get();
                $data = [
                    'count' => $list_count,
                    'url' => '/'.$section_name."/search?search=".$search,
                    'name' => $section_name,
                    'title' => $section_data['title'],
                    'list' => []
                ];
                foreach ($list as $item) {
                    $property = $section_data['description_default'];
                    foreach ($section_data['description_fields'] as $description_field) {
                        if (mb_strpos($item->{$description_field}, $search) !== false) {
                            $property = $description_field;
                        }
                    }
                    $data['list'][] = [
                        'url' => $item->url,
                        'title' => $item->{$section_data['title_field']},
                        'picture' => $section_data['picture_field'] != '' ? $item->{$section_data['picture_field']} : '',
                        'description' => $item->{$property}
                    ];
                }
                $results[] = $data;
            }
        }
        $articles_types = [
            Article::TYPE_ARTICLES => ['url' => 'articles', 'title' => 'Статьи'],
            Article::TYPE_NEWS => ['url' => 'news', 'title' => 'Новости'],''
        ];
        foreach ($articles_types as $articles_type => $articles_data) {
            $articles = Article::approved()->where(['type_id' => $articles_type])->where(function($q) use ($search) {
                $q->where('title', 'LIKE', '%'.$search.'%');
                $q->orWhere('content', 'LIKE', '%'.$search.'%');
            })->orderBy('id', 'desc');
            $articles_count = $articles->count();
            $articles = $articles->limit($limit)->get();
            if (count($articles) > 0) {
                $data = [
                    'name' => $articles_data['url'],
                    'count' => $articles_count,
                    'url' => '/'.$articles_data['url']."?search=".$search,
                    'title' => $articles_data['title'],
                    'list' => []
                ];
                foreach ($articles as $item) {
                    $data['list'][] = [
                        'additional' => $item->created_at,
                        'url' => $item->url,
                        'title' => $item->title,
                        'picture' => $item->cover,
                        'description' => $item->searchContent($search)
                    ];
                }
                $results[] = $data;
            }
        }
        return [
            'status' => 1,
             'data' => [
                'dom' => [
                    [
                        'replace' => '.site-search__results',
                        'html' => view("blocks/search_results", ['search' => $search, 'ajax' => true, 'results' => $results])->render()
                    ]
                ]
            ]
        ];
    }
}
