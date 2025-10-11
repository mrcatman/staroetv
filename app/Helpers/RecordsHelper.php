<?php
namespace App\Helpers;

use App\Constants\Periods;
use App\Models\Channel;
use App\Models\Record;
use Illuminate\Support\Facades\DB;

class RecordsHelper {

    public static function getQuery($conditions) {
        $records = Record::approved();
        unset($conditions['show_years']);
        unset($conditions['new_titles']);
        if (isset($conditions['channel_id_in'])) {
            $records = $records->whereIn('channel_id', $conditions['channel_id_in']);
            unset($conditions['channel_id_in']);
        }
        if (isset($conditions['program_id_in'])) {
            $records = $records->where(function($q) use ($conditions) {
                $q->whereIn('program_id', $conditions['program_id_in']);
                if (
                    (is_array($conditions['program_id_in']) && in_array(null, $conditions['program_id_in'])) ||
                    (!is_array($conditions['program_id_in']) && $conditions['program_id_in']->contains(null)))
                {
                    $q->orWhereNull('program_id');
                }
            });

            unset($conditions['program_id_in']);
        }
        if (isset($conditions['period'])) {
            $records = $records->whereBetween('date', Periods::getDatesInterval($conditions['period']));
            unset($conditions['period']);
        }
        if (isset($conditions['interprogram_type_in'])) {
            $records = $records->whereIn('interprogram_type', $conditions['interprogram_type_in']);
            unset($conditions['interprogram_type_in']);
        }
        if (isset($conditions['interprogram_type_not_in'])) {
            $records = $records->where(function($q) use ($conditions) {
                $q = $q->whereNotIn('interprogram_type', $conditions['interprogram_type_not_in']);
                $q->orWhereNull('interprogram_type');
            });
            unset($conditions['interprogram_type_not_in']);
        }
        if (isset($conditions['channel_unknown']) && $conditions['channel_unknown'] === true) {
            $records = $records->where(function($q) {
                $channel_ids = Channel::pluck('id');
                $q->whereNotIn('channel_id', $channel_ids);
                $q->orWhereNull('channel_id');
            });
            unset($conditions['channel_unknown']);
        }
        if (isset($conditions['is_selected']) && $conditions['is_selected'] === false) {
            $records = $records->where(function($q) {
                $q->where(['is_selected' => false]);
                $q->orWhereNull('is_selected');
            });
            unset($conditions['is_selected']);
        }
        if (isset($conditions['year_start'])) {
            $records = $records->where('year', '>=', $conditions['year_start']);
            unset($conditions['year_start']);
        }
        if (isset($conditions['year_end'])) {
            $records = $records->where('year', '<=', $conditions['year_end']);
            unset($conditions['year_end']);
        }
        if (isset($conditions['normal_date'])) {
            $records = $records->whereDate('supposed_date', '>', '1950-01-01');
            unset($conditions['normal_date']);
        }

        $records = $records->where($conditions);
        return $records;
    }

    public static function get($conditions, $ajax = false)
    {
        $query_params = request()->except(['_pjax', 'block_title']);
        $base_link = $ajax ? parse_url(request()->headers->get('referer'), PHP_URL_PATH) : request()->url();

        $sort = "added";
        $sort_field = "original_added_at";
        $sort_order = "desc";
        if (request()->input('sort') == "older") {
            $sort = "older";
            $sort_field = "supposed_date";
            $sort_order = "asc";
        } elseif (request()->input('sort') == "newer") {
            $sort = "newer";
            $sort_field = "supposed_date";
            $sort_order = "desc";
        }
        $records = self::getQuery($conditions);
        $search = "";
        if (request()->has('search')) {
            $search = request()->input('search');
            $records = $records->where('title', 'LIKE', '%' . $search . '%');
        }

        $years = null;
        $months = null;
        $selected_year = null;
        $selected_month = null;

        if (isset($conditions['show_years']) && $conditions['show_years']) {
            $years = (clone $records)->where('year', '>', 0)->groupBy('year')->select('year', DB::raw('count(*) as count'))->pluck('count', 'year');
            $selected_year = request()->input('year');
            if ((int)$selected_year > 0) {
                $records->where('year', $selected_year);
                $months = (clone $records)->where('month', '>', 0)->where(['year' => $selected_year])->groupBy('month')->select('month', DB::raw('count(*) as count'))->pluck('count', 'month');
                $selected_month = request()->input('month');
                $months = $months->toArray();
                ksort($months);
                if ((int)$selected_month > 0) {
                    $records->where('month', $selected_month);
                }
            }
            $years = $years->toArray();
            ksort($years);
        }
        $records = $records->orderBy($sort_field, $sort_order);
        $count = $records->count();
        $list = $records->paginate(36);
        return [
            'query_params' => $query_params,
            'base_link' => $base_link,
            'search' => $search,
            'sort' => $sort,
            'count' => $count,
            'records' => $list,
            'years' => $years,
            'months' => $months,
            'selected_year' => $selected_year,
            'selected_month' => $selected_month,
        ];
    }

}
