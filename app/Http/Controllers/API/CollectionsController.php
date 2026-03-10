<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Collections\StoreCollectionRequest;
use App\Http\Requests\Collections\UpdateCollectionRequest;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CollectionsController extends Controller
{
    /**
     * Display a listing of all collections.
     */
    public function index()
    {
        $collections = Collection::with(['user', 'items'])->get();
        return CollectionResource::collection($collections);
    }

    /**
     * Store a newly created collection.
     */
    public function store(StoreCollectionRequest $request)
    {
        // Enforce maximum of 1 collection per user
        if ($request->user()->collection) {
            return ApiResponse::error('User already has a collection.', 403);
        }

        $validated = $request->validated();

        // Attach the authenticated user's ID
        $validated['user_id'] = $request->user()->id;

        $collection = Collection::create($validated);
        return (new CollectionResource($collection))->response()->setStatusCode(201);
    }

    /**
     * Display the specified collection.
     */
    public function show(Collection $collection)
    {
        // Load the collection's user and items
        $collection->load(['user', 'items']);
        return new CollectionResource($collection);
    }

    /**
     * Update the specified collection.
     */
    public function update(UpdateCollectionRequest $request, Collection $collection)
    {
        $validated = $request->validated();

        $collection->update($validated);
        return new CollectionResource($collection);
    }

    /**
     * Remove the specified collection.
     */
    public function destroy(Request $request, Collection $collection)
    {
        $this->authorize('delete', $collection);

        $collection->delete();
        return response()->json(null, 204);
    }
}
