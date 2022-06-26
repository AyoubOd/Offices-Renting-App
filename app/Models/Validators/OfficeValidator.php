<?php

namespace App\Models\Validators;

use App\Models\Office;
use Illuminate\Validation\Rule;


/**
 * OfficeValidator
 * 
 * to validate the office create|update...
 * 
 */
class OfficeValidator
{
    public function validate(Office $office, array $attributes)
    {
        return validator(
            $attributes,
            [
                'title' => [Rule::when($office->exists, 'sometimes'), 'required', 'string'],
                'description' => [Rule::when($office->exists, 'sometimes'), 'required', 'string'],
                'lat' => [Rule::when($office->exists, 'sometimes'), 'required', 'numeric'],
                'lng' => [Rule::when($office->exists, 'sometimes'), 'required', 'numeric'],
                'address_line1' => [Rule::when($office->exists, 'sometimes'), 'required', 'string'],
                'hidden' => [Rule::when($office->exists, 'sometimes'), 'required', 'boolean'],
                'price_per_day' => [Rule::when($office->exists, 'sometimes'), 'required', 'numeric', 'min:100'],

                'address_line2' => ['string'],
                'monthly_discount' => 'min:0',
                'tags' => 'array',
                'tags.*' => ['integer', Rule::exists('tags', 'id')]
            ]
        )->validate();
    }
}
