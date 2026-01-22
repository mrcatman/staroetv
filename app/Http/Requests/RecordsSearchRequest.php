<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordsSearchRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'date.range' => $this->boolean('date.range'),
        ]);
    }

    public function rules(): array
    {
        return [
            'is_radio' => 'sometimes|boolean',
            'channels' => 'nullable|array',
            'channels.*' => 'numeric',
            'programs' => 'nullable|array',
            'programs.*' => 'numeric',
            'search' => 'sometimes',
            'sort' => 'sometimes|in:supposed_date,created_at',
            'sort_order' => 'sometimes|in:asc,desc',
            'page' => 'sometimes|numeric',
            'exclude_ids' => 'sometimes|array',
            'exclude_ids.*' => 'numeric',
            'type' => 'nullable|in:programs,advertising,interprogram,program-design,other,clips',

            'advertising_type' => 'nullable',
            'advertising_brands' => 'nullable|array',
            'advertising_brands.*' => 'string',
            'advertising_categories' => 'nullable|array',
            'advertising_categories.*' => 'string',

            'advertising_countries' => 'nullable|array',
            'advertising_countries.*' => 'nullable|string',
            'advertising_regions' => 'nullable|array',
            'advertising_regions.*' => 'nullable|string',

            'date.range' => 'sometimes|boolean',
            'date.year' => 'sometimes|numeric',
            'date.month' => 'sometimes|numeric',
            'date.day' => 'sometimes|numeric',
            'date.year_start' => 'sometimes|numeric',
            'date.month_start' => 'sometimes|numeric',
            'date.day_start' => 'sometimes|numeric',
            'date.year_end' => 'sometimes|numeric',
            'date.month_end' => 'sometimes|numeric',
            'date.day_end' => 'sometimes|numeric',
        ];
    }

    public function isCommercialsSearch(): bool
    {
        return $this->route()->getName() == 'records.commercials';
    }

}
