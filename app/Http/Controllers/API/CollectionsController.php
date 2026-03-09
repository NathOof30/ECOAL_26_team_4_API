<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionsController extends Controller
{
    /**
     * Display a listing of all collections.
     */
    public function index()
    {
        // Return all collections with their user and items
        $collections = Collection::with(['user', 'items'])->get();
        return response()->json($collections);
    }

    /**
     * Store a newly created collection.
     */
    public function store(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id|unique:collections,user_id',
        ]);

        $collection = Collection::create($validated);
        return response()->json($collection, 201);
    }

    /**
     * Display the specified collection.
     */
    public function show(Collection $collection)
    {
        // Load the collection's user and items
        $collection->load(['user', 'items']);
        return response()->json($collection);
    }

    /**
     * Update the specified collection.
     */
    public function update(Request $request, Collection $collection)
    {
        // Validate incoming data
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $collection->update($validated);
        return response()->json($collection);
    }

    /**
     * Remove the specified collection.
     */
    public function destroy(Collection $collection)
    {
        $collection->delete();
        return response()->json(null, 204);
    }
}
