<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PlanSaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rule = [
            'plan_name' => 'required|max:255',
            'duration' => 'required|integer|min:1',
            'amount' => 'required|numeric',
            'bonus_type' => 'required|integer',
            'bonus' => 'required',
            'bonus_coin_type' => 'required',
            'status' => 'required|integer',
        ];
        if ($this->bonus_type == DISCOUNT_TYPE_PERCENTAGE) {
            $rule['bonus'] = 'numeric|min:0|max:99';
        } else {
            $rule['bonus'] = 'numeric';
        }
        if ($this->image) {
//            $rule['image'] = 'image|mimes:jpg,jpeg,png|max:2000';
        }

        return $rule;
    }
    protected function failedValidation(Validator $validator)
    {
            $errors = [];
            if ($validator->fails()) {
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
            }
            $json = [
                'success'=>false,
                'message' => $errors[0],
            ];
            $response = new JsonResponse($json, 200);

        throw new ValidationException($validator, $response);
    }
}
