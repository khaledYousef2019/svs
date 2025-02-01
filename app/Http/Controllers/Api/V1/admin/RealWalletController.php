<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Model\RealWallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RealWalletController extends Controller
{
    /**
     * Display a listing of the real wallets.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $wallets = RealWallet::all();
        return response()->json($wallets);
    }

    /**
     * Show the form for creating a new real wallet.
     *
     * @return JsonResponse
     */
    public function create()
    {
        // Optionally return a view to create a new wallet (if using Blade)
        return response()->json(['message' => 'Show form to create a new wallet.']);
    }

    /**
     * Store a newly created real wallet in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'chain_id' => 'required|numeric|max:255',
            'mnemonic' => 'nullable|string',
            'xpub' => 'nullable|string',
            'private_key' => 'required|string',
            'address' => 'required|string',
        ]);

        $wallet = RealWallet::create($request->all());
        return response()->json($wallet, 201);
    }

    /**
     * Display the specified real wallet.
     *
     * @param RealWallet $realWallet
     * @return JsonResponse
     */
    public function show(RealWallet $realWallet)
    {
        return response()->json($realWallet);
    }

    /**
     * Show the form for editing the specified real wallet.
     *
     * @param RealWallet $realWallet
     * @return JsonResponse
     */
    public function edit(RealWallet $realWallet)
    {
        // Optionally return a view to edit the wallet (if using Blade)
        return response()->json($realWallet);
    }

    /**
     * Update the specified real wallet in storage.
     *
     * @param Request $request
     * @param RealWallet $realWallet
     * @return JsonResponse
     */
    public function update(Request $request, RealWallet $realWallet)
    {
        $request->validate([
            'chain_id' => 'required|numeric|max:255',
            'mnemonic' => 'nullable|string',
            'xpub' => 'nullable|string',
            'private_key' => 'required|string',
            'address' => 'required|string',
        ]);

        $realWallet->update($request->all());
        return response()->json($realWallet);
    }

    /**
     * Remove the specified real wallet from storage.
     *
     * @param RealWallet $realWallet
     * @return JsonResponse
     */
    public function destroy(RealWallet $realWallet)
    {
        $realWallet->delete();
        return response()->json(null, 204);
    }
}
