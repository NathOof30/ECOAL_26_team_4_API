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
        // Enforce maximum of 1 collection per user
        if ($request->user()->collection) {
            return response()->json(['message' => 'User already has a collection.'], 403);
        }

        // Validate incoming data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Attach the authenticated user's ID
        $validated['user_id'] = $request->user()->id;

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
        // Enforce ownership: only the owner can update the collection
        if ($collection->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized. You can only update your own collection.'], 403);
        }

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
    public function destroy(Request $request, Collection $collection)
    {
        // Enforce ownership: only the owner can delete the collection
        if ($collection->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized. You can only delete your own collection.'], 403);
        }

        $collection->delete();
        return response()->json(null, 204);
    }
}
