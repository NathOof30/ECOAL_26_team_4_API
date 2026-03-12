<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Items\StoreItemRequest;
use App\Http\Requests\Items\UpdateItemRequest;
use App\Http\Resources\ItemResource;
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
        $query = Item::with(['collection', 'category1', 'category2', 'criteria']);

        if (request()->filled('collection_id')) {
            $query->where('collection_id', request('collection_id'));
        }

        if (request()->filled('category1_id')) {
            $query->where('category1_id', request('category1_id'));
        }

        if (request()->filled('category2_id')) {
            $query->where('category2_id', request('category2_id'));
        }

        if (request()->filled('status')) {
            $query->where('status', filter_var(request('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        }

        if (request()->filled('title')) {
            $query->where('title', 'like', '%'.request('title').'%');
        }

        $sort = in_array(request('sort'), ['id', 'title', 'collection_id', 'created_at'], true) ? request('sort') : 'id';
        $direction = request('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = min((int) request('per_page', 15), 100);

        $items = $query->orderBy($sort, $direction)->paginate($perPage)->appends(request()->query());

        return ItemResource::collection($items);
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
        return (new ItemResource($item->load(['collection', 'category1', 'category2', 'criteria'])))->response()->setStatusCode(201);
    }

    /**
     * Display the specified item.
     */
    public function show(Item $item)
    {
        // Load all relationships for the item
        $item->load(['collection', 'category1', 'category2', 'criteria']);
        return new ItemResource($item);
    }

    /**
     * Update the specified item.
     */
    public function update(UpdateItemRequest $request, Item $item)
    {
        $validated = $request->validated();

        $item->update($validated);
        return new ItemResource($item->load(['collection', 'category1', 'category2', 'criteria']));
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
