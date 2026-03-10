<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Collection;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    /**
     * Display a listing of all items.
     */
    public function index()
    {
        // Return all items with their relationships
        $items = Item::with(['collection', 'category1', 'category2', 'criteria'])->get();
        return response()->json($items);
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {
        // Check if the user has a collection to add items to
        $collection = Collection::where('user_id', $request->user()->id)->first();

        if (!$collection) {
            return response()->json(['message' => 'You must create a collection first before adding items.'], 403);
        }

        // Validate incoming data (collection_id is no longer needed in the request)
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
            'category1_id' => 'required|exists:category,id',
            'category2_id' => 'nullable|exists:category,id',
        ]);

        // Automatically assign the user's collection
        $validated['collection_id'] = $collection->id;

        $item = Item::create($validated);
        return response()->json($item->load(['category1', 'category2']), 201);
    }

    /**
     * Display the specified item.
     */
    public function show(Item $item)
    {
        // Load all relationships for the item
        $item->load(['collection', 'category1', 'category2', 'criteria']);
        return response()->json($item);
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, Item $item)
    {
        // Enforce ownership: only the owner of the collection can update the item
        if ($item->collection->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized. You can only update items in your own collection.'], 403);
        }

        // Validate incoming data
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:255',
            'status' => 'sometimes|boolean',
            'category1_id' => 'sometimes|exists:category,id',
            'category2_id' => 'nullable|exists:category,id',
        ]);

        $item->update($validated);
        return response()->json($item->load(['category1', 'category2']));
    }

    /**
     * Remove the specified item.
     */
    public function destroy(Request $request, Item $item)
    {
        // Enforce ownership: only the owner of the collection can delete the item
        if ($item->collection->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized. You can only delete items from your own collection.'], 403);
        }

        $item->delete();
        return response()->json(null, 204);
    }
}
