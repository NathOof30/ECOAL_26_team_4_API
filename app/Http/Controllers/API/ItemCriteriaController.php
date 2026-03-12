<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemCriteria\StoreItemCriteriaRequest;
use App\Http\Requests\ItemCriteria\UpdateItemCriteriaRequest;
use App\Http\Resources\ItemCriteriaResource;
use App\Models\ItemCriteria;
use App\Models\Item;
use App\Models\Criteria;
use App\Support\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ItemCriteriaController extends Controller
{
    /**
     * Display all criteria scores for all items.
     */
    public function index()
    {
        $query = ItemCriteria::with(['item', 'criteria']);

        if (request()->filled('id_item')) {
            $query->where('id_item', request('id_item'));
        }

        if (request()->filled('id_criteria')) {
            $query->where('id_criteria', request('id_criteria'));
        }

        if (request()->filled('value')) {
            $query->where('value', request('value'));
        }

        $sort = in_array(request('sort'), ['id_item', 'id_criteria', 'value'], true) ? request('sort') : 'id_item';
        $direction = request('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = min((int) request('per_page', 15), 100);

        $scores = $query->orderBy($sort, $direction)->paginate($perPage)->appends(request()->query());

        return ItemCriteriaResource::collection($scores);
    }

    /**
     * Store a new criteria score for an item.
     */
    public function store(StoreItemCriteriaRequest $request)
    {
        $validated = $request->validated();

        Item::findOrFail($validated['id_item']);

        try {
            $score = ItemCriteria::create($validated);
        } catch (QueryException $exception) {
            if ($this->isDuplicateScoreException($exception)) {
                return ApiResponse::error('A score already exists for this item and criterion.', 409);
            }

            throw $exception;
        }

        return (new ItemCriteriaResource($score->load(['item', 'criteria'])))->response()->setStatusCode(201);
    }

    /**
     * Display criteria scores for a specific item.
     */
    public function show(Item $item)
    {
        // Return all criteria scores for this item
        $scores = ItemCriteria::where('id_item', $item->id)
                    ->with('criteria')
                    ->orderBy('id_criteria')
                    ->paginate(min((int) request('per_page', 15), 100))
                    ->appends(request()->query());
        return ItemCriteriaResource::collection($scores);
    }

    /**
     * Update a criteria score for an item.
     */
    public function update(UpdateItemCriteriaRequest $request, Item $item, Criteria $criterion)
    {
        $validated = $request->validated();

        // Find and update the specific score
        $score = ItemCriteria::where('id_item', $item->id)
                    ->where('id_criteria', $criterion->id_criteria)
                    ->firstOrFail();

        $score->update($validated);
        return new ItemCriteriaResource($score->load(['item', 'criteria']));
    }

    /**
     * Remove a criteria score for an item.
     */
    public function destroy(Request $request, Item $item, Criteria $criterion)
    {
        $this->authorize('score', $item);

        $deleted = ItemCriteria::where('id_item', $item->id)
            ->where('id_criteria', $criterion->id_criteria)
            ->delete();

        if ($deleted === 0) {
            return ApiResponse::error('Score not found for this item and criterion.', 404);
        }

        return response()->json(null, 204);
    }

    private function isDuplicateScoreException(QueryException $exception): bool
    {
        $message = mb_strtolower($exception->getMessage());

        return str_contains($message, 'unique')
            || str_contains($message, 'duplicate')
            || str_contains($message, 'item_criteria');
    }
}
