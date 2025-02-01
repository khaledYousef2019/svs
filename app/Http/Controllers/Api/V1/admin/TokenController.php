<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Model\Token;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    /**
     * Display a listing of the tokens.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $tokens = Token::all();
        return response()->json($tokens);
    }

    /**
     * Show the form for creating a new token.
     *
     * @return JsonResponse
     */
    public function create()
    {
        return response()->json(['message' => 'Show form to create a new token.']);
    }

    /**
     * Store a newly created token in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:255',
            'chain_id' => 'required|numeric|max:255',
            'contract_address' => 'required|string',
            'holder_address' => 'nullable|string|max:255',
            'cap' => 'nullable|integer',
            'supply' => 'nullable|integer',
            'decimals' => 'nullable|integer',
            'price' => 'nullable|numeric',
            'base_pair' => 'nullable|string|max:255',
            'network' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
            'withdraw_fee' => 'nullable|integer',
            'withdraw_max' => 'nullable|numeric',
            'withdraw_min' => 'nullable|numeric',
            'coingecko_id' => 'nullable|string|max:50',
            'coincodex_id' => 'nullable|string|max:50',
        ]);

//        dd($request->all());
        $token = Token::create($request->all());
        return response()->json($token, 201);
    }

    /**
     * Display the specified token.
     *
     * @param Token $token
     * @return JsonResponse
     */
    public function show(Token $token)
    {
        return response()->json($token);
    }

    /**
     * Show the form for editing the specified token.
     *
     * @param Token $token
     * @return JsonResponse
     */
    public function edit(Token $token)
    {
        return response()->json($token);
    }

    /**
     * Update the specified token in storage.
     *
     * @param Request $request
     * @param Token $token
     * @return JsonResponse
     */
    public function update(Request $request, Token $token)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:255',
            'chain' => 'nullable|string|max:255',
            'contract_address' => 'required|string',
            'holder_address' => 'nullable|string|max:255',
            'cap' => 'nullable|integer',
            'supply' => 'nullable|integer',
            'decimals' => 'nullable|integer',
            'price' => 'nullable|numeric',
            'base_pair' => 'nullable|string|max:255',
            'network' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
            'withdraw_fee' => 'nullable|integer',
            'withdraw_max' => 'nullable|numeric',
            'withdraw_min' => 'nullable|numeric',
            'coingecko_id' => 'nullable|string|max:50',
            'coincodex_id' => 'nullable|string|max:50',
        ]);

        $token->update($request->all());
        return response()->json($token);
    }

    /**
     * Remove the specified token from storage.
     *
     * @param Token $token
     * @return JsonResponse
     */
    public function destroy(Token $token)
    {
        $token->delete();
        return response()->json(null, 204);
    }
}
