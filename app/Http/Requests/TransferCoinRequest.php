<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TransferCoinRequest extends FormRequest
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
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:'.settings('plan_minimum_amount').'|max:'.settings('plan_maximum_amount'),
        ];

        return $rule;
    }
    public function messages()
    {
        return  [
            'wallet_id.required' => __('Please select a wallet'),
            'wallet_id.exists' => __('Invalid wallet'),
            'amount.required' => __('Coin amount can not be empty')
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
