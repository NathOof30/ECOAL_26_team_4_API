<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemCriteria\StoreItemCriteriaRequest;
use App\Http\Requests\ItemCriteria\UpdateItemCriteriaRequest;
use App\Models\ItemCriteria;
use App\Models\Item;
use App\Models\Criteria;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ItemCriteriaController extends Controller
{
    /**
     * Display all criteria scores for all items.
     */
    public function index()
    {
        $scores = ItemCriteria::with(['item', 'criteria'])->get();
        return response()->json($scores);
    }

    /**
     * Store a new criteria score for an item.
     */
    public function store(StoreItemCriteriaRequest $request)
    {
        $validated = $request->validated();

        $item = Item::findOrFail($validated['id_item']);

        $alreadyExists = ItemCriteria::where('id_item', $validated['id_item'])
            ->where('id_criteria', $validated['id_criteria'])
            ->exists();

        if ($alreadyExists) {
            return ApiResponse::error('A score already exists for this item and criterion.', 409);
        }

        $score = ItemCriteria::create($validated);
        return response()->json($score->load(['item', 'criteria']), 201);
    }

    /**
     * Display criteria scores for a specific item.
     */
    public function show(Item $item)
    {
        // Return all criteria scores for this item
        $scores = ItemCriteria::where('id_item', $item->id)
                    ->with('criteria')
                    ->get();
        return response()->json($scores);
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
        return response()->json($score);
    }

    /**
     * Remove a criteria score for an item.
     */
    public function destroy(Request $request, Item $item, Criteria $criterion)
    {
        $this->authorize('score', $item);

        ItemCriteria::where('id_item', $item->id)
                    ->where('id_criteria', $criterion->id_criteria)
                    ->delete();

        return response()->json(null, 204);
    }
}
