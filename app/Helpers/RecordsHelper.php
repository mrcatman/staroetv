<?php
namespace App\Helpers;

use App\Record;

class RecordsHelper {

    public static function getQuery($conditions) {
        $records = Record::approved();
        if (isset($conditions['channel_id_in'])) {
            $records = $records->whereIn('channel_id', $conditions['channel_id_in']);
            unset($conditions['channel_id_in']);
        }
        if (isset($conditions['program_id_in'])) {
            $records = $records->whereIn('program_id', $conditions['program_id_in']);
            unset($conditions['program_id_in']);
        }
        if (isset($conditions['interprogram_type_in'])) {
            $records = $records->whereIn('interprogram_type', $conditions['interprogram_type_in']);
            unset($conditions['interprogram_type_in']);
        }
        if (isset($conditions['interprogram_type_not_in'])) {
            $records = $records->whereNotIn('interprogram_type', $conditions['interprogram_type_not_in']);
            unset($conditions['interprogram_type_not_in']);
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
        $sort_field = "id";
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
