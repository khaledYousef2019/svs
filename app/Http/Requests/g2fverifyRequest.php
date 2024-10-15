<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class g2fverifyRequest extends FormRequest
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
            'code' => 'required'
        ];
       return $rules;
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
        $json = ['success'=>false,
            'data'=>[],
            'message' => $errors[0],
        ];
        $response = new JsonResponse($json, 200);

        throw new ValidationException($validator, $response);
    }
}
