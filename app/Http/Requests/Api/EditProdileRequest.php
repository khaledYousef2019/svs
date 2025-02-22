<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class EditProdileRequest extends FormRequest
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
        $rules = [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'gender' => 'required'
        ];

        return $rules;
    }

    public function messages()
    {
        return  [
            'first_name.required' => __('First name can not be empty'),
            'last_name.required' => __('Last name can not be empty'),
            'gender.required' => __('gender can not be empty')
        ];
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
