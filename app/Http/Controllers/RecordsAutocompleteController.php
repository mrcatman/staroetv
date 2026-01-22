<?php

namespace App\Http\Controllers;

use App\Models\Record;

class RecordsAutocompleteController extends Controller {

    const ITEMS_COUNT = 30;

    private function select(string $field, array $conditions = null)
    {
        $items = Record::approved();
        if ($conditions) {
            $items = $items->where($conditions);
        }
        $items = $items->groupBy($field)->select($field, \DB::raw('COUNT(*) as count'))->orderBy('count', 'desc');

        if (request()->has('is_radio')) {
            $items = $items->where(['is_radio' => !!request()->input('is_radio')]);
        }
        return $items;
    }

    private function filterByCommercialType($query)
    {
        if (request()->has('advertising_type') && request()->input('advertising_type') > 0) {
            $query = $query->where(['advertising_type' => request()->input('advertising_type')]);
        } else {
            $query = $query->whereNull('advertising_type');
        }
        return $query;
    }

    private function process($query, $field, $default_item_name = null)
    {
        $page = request()->input('page', 1);

        $items = $query->clone();
        if (request()->has('term')) {
            $items = $items->where($field, 'LIKE', '%' . request()->input('term') . '%');
        }
        $items = $items->clone()->whereNotNull($field)->where($field, '!=' ,'')->limit(self::ITEMS_COUNT)->offset(self::ITEMS_COUNT * ($page - 1))->get()->map(function ($item) use ($field) {
            return ['id' => $item->{$field},  'name' => $item->{$field}, 'count' => $item->count];
        });
        if (request()->input('for_search') && $default_item_name && $page == 1) {
            $default = $query->clone()->whereNull($field)->first();
            if ($default) {
                $items->prepend([
                    'id' => null,
                    'name' => $default_item_name,
                    'count' => $default->count
                ]);
            }
        }
        return [
            'status' => 1,
            'data' => $items
        ];
    }

    public function countries()
    {
        return $this->process($this->select('country', ['is_advertising' => true]), 'country', 'СССР/РФ');

    }

    public function regions()
    {
        $regions = $this->select('region', ['is_advertising' => true]);

        if (request()->input('country') != '') {
            $regions = $regions->addSelect('country')->where('country', 'LIKE', '%' . request()->input('country') . '%');
        }

        return $this->process($regions, 'region', 'Общероссийская реклама');
    }

    public function commercialsBrands()
    {
        $brands = $this->select('advertising_brand', ['is_advertising' => true]);
        $brands = $this->filterByCommercialType($brands);
        return $this->process($brands, 'advertising_brand');
    }

    public function commercialsCategories()
    {
        $categories = $this->select('advertising_category', ['is_advertising' => true]);
        $categories = $this->filterByCommercialType($categories);
        return $this->process($categories, 'advertising_category');
    }

}
