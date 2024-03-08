<?php
namespace App\Helpers;

use App\Channel;
use App\Record;

class RecordsHelper {

    public static function getQuery($conditions) {
        $records = Record::approved();
        if (isset($conditions['channel_id_in'])) {

            $records = $records->whereIn('channel_id', $conditions['channel_id_in']);
            unset($conditions['channel_id_in']);
        }
        if (isset($conditions['program_id_in'])) {
            $records = $records->where(function($q) use ($conditions) {
                $q->whereIn('program_id', $conditions['program_id_in']);
                if (in_array(null, (array)$conditions['program_id_in'])) {
                    $q->orWhereNull('program_id');
                }
            });

            unset($conditions['program_id_in']);
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

    public static function get($conditions) {
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
            $records = $records->where('title', 'LIKE', '%'.$search.'%');
        }


        $records = $records->orderBy($sort_field, $sort_order);
        $count = $records->count();
        $list = $records->paginate(36);
        return [
            'search' => $search,
            'sort' => $sort,
            'count' => $count,
            'records' => $list
        ];
    }




}
