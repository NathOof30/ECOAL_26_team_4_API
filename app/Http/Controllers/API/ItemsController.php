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
        $query = Item::with(['collections', 'categories', 'criteria']);

        if (request()->filled('collection_id')) {
            $query->whereHas('collections', function ($collectionQuery) {
                $collectionQuery->where('collections.id', request('collection_id'));
            });
        }

        if (request()->filled('category1_id')) {
            $query->whereHas('categories', function ($categoryQuery) {
                $categoryQuery->where('category.id', request('category1_id'));
            });
        }

        if (request()->filled('category2_id')) {
            $query->whereHas('categories', function ($categoryQuery) {
                $categoryQuery->where('category.id', request('category2_id'));
            });
        }

        if (request()->filled('status')) {
            $query->where('status', filter_var(request('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        }

        if (request()->filled('title')) {
            $query->where('title', 'like', '%'.request('title').'%');
        }

        $sort = in_array(request('sort'), ['id', 'title', 'created_at'], true) ? request('sort') : 'id';
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

        $validated = $request->validated();
        $item = Item::create($validated);
        $item->collections()->attach($collection->id);
        $this->syncItemCategories($item, $validated);

        return (new ItemResource($item->load(['collections', 'categories', 'criteria'])))->response()->setStatusCode(201);
    }

    /**
     * Display the specified item.
     */
    public function show(Item $item)
    {
        // Load all relationships for the item
        $item->load(['collections', 'categories', 'criteria']);
        return new ItemResource($item);
    }

    /**
     * Update the specified item.
     */
    public function update(UpdateItemRequest $request, Item $item)
    {
        $validated = $request->validated();

        $item->update($validated);
        $this->syncItemCategories($item, $validated);

        return new ItemResource($item->load(['collections', 'categories', 'criteria']));
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

    private function syncItemCategories(Item $item, array $validated): void
    {
        if (! array_key_exists('category1_id', $validated) && ! array_key_exists('category2_id', $validated)) {
            return;
        }

        $categoryIds = [];

        if (! empty($validated['category1_id'])) {
            $categoryIds[] = (int) $validated['category1_id'];
        }

        if (! empty($validated['category2_id'])) {
            $categoryIds[] = (int) $validated['category2_id'];
        }

        $item->categories()->sync(array_values(array_unique($categoryIds)));
    }
}
