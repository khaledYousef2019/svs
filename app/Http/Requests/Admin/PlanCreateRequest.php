<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PlanCreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'min_price' => 'required|numeric',
            'max_price' => 'required|numeric|gt:min_price',
            'expected_return' => 'nullable|numeric',
            'increment_interval' => 'required|string',
            'increment_amount' => 'required|numeric',
            'fees_type' => 'required|string',
            'fees' => 'nullable|numeric',
            'expiration' => 'required|string',
            'status' => 'required|numeric',
        ];
    }
} 