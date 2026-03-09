<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
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
        // Validate incoming data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
            'collection_id' => 'required|exists:collections,id',
            'category1_id' => 'required|exists:category,id',
            'category2_id' => 'nullable|exists:category,id',
        ]);

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
        // Validate incoming data
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:255',
            'status' => 'sometimes|boolean',
            'collection_id' => 'sometimes|exists:collections,id',
            'category1_id' => 'sometimes|exists:category,id',
            'category2_id' => 'nullable|exists:category,id',
        ]);

        $item->update($validated);
        return response()->json($item->load(['category1', 'category2']));
    }

    /**
     * Remove the specified item.
     */
    public function destroy(Item $item)
    {
        $item->delete();
        return response()->json(null, 204);
    }
}
