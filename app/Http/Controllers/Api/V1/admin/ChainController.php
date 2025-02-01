<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Model\Chain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ChainController extends Controller
{
    /**
     * Display a listing of the chains.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $chains = Chain::all();
        return response()->json($chains);
    }

    /**
     * Show the form for creating a new chain.
     *
     * @return JsonResponse
     */
    public function create()
    {
        // Return a view for creating a new chain if needed
        return response()->json(['message' => 'Create new chain view']);
    }

    /**
     * Store a newly created chain in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
//        dd(5);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'network_type' => 'nullable|string|max:55',
            'chain_links' => 'nullable',
            'chain_id' => 'nullable|string|max:255',
            'previous_block_count' => 'nullable|integer',
            'gas_limit' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        $validatedData['chain_links']= json_encode($validatedData['chain_links']);

        $chain = Chain::create($validatedData);
        return response()->json($chain, ResponseAlias::HTTP_CREATED);
    }

    /**
     * Display the specified chain.
     *
     * @param Chain $chain
     * @return JsonResponse
     */
    public function show(Chain $chain)
    {
        return response()->json($chain);
    }

    /**
     * Show the form for editing the specified chain.
     *
     * @param Chain $chain
     * @return JsonResponse
     */
    public function edit(Chain $chain)
    {
        return response()->json($chain);
    }

    /**
     * Update the specified chain in storage.
     *
     * @param Request $request
     * @param Chain $chain
     * @return JsonResponse
     */
    public function update(Request $request, Chain $chain)
    {
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'network_type' => 'nullable|string|max:55',
            'chain_links' => 'nullable|json',
            'chain_id' => 'nullable|string|max:255',
            'previous_block_count' => 'nullable|integer',
            'gas_limit' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        $chain->update($validatedData);
        return response()->json($chain);
    }

    /**
     * Remove the specified chain from storage.
     *
     * @param Chain $chain
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Chain $chain)
    {
        $chain->delete();
        return response()->json(null, ResponseAlias::HTTP_NO_CONTENT);
    }
}
