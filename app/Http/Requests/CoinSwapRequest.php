<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CoinSwapRequest extends FormRequest
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
        return [
            'from_coin_id' => 'required|exists:wallets,id',
            'to_coin_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|gt:0',
        ];
    }
    public function messages()
    {
        return [
            'from_coin_id.required' => __("From wallet id is required"),
            'to_coin_id.required' => __("To wallet id is required"),
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
        $json = ['success'=>false,
            'data'=>[],
            'message' => $errors[0],
        ];
        $response = new JsonResponse($json, 200);

        throw new ValidationException($validator, $response);
    }
}
