<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Items\StoreItemRequest;
use App\Http\Requests\Items\UpdateItemRequest;
use App\Models\Item;
use App\Models\Collection;
use App\Support\ApiResponse;
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
    public function store(StoreItemRequest $request)
    {
        // Check if the user has a collection to add items to
        $collection = Collection::where('user_id', $request->user()->id)->first();

        if (!$collection) {
            return ApiResponse::error('You must create a collection first before adding items.', 403);
        }

        // Validate incoming data (collection_id is no longer needed in the request)
        $validated = $request->validated();

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
    public function update(UpdateItemRequest $request, Item $item)
    {
        $validated = $request->validated();

        $item->update($validated);
        return response()->json($item->load(['category1', 'category2']));
    }

    /**
     * Remove the specified item.
     */
    public function destroy(Request $request, Item $item)
    {
        $this->authorize('delete', $item);

        $item->delete();
        return response()->json(null, 204);
    }
}
