<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ItemCriteria;
use App\Models\Item;
use App\Models\Criteria;
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
    public function store(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'id_item' => 'required|exists:items,id',
            'id_criteria' => 'required|exists:criteria,id_criteria',
            'value' => 'required|integer|in:0,1,2',
        ]);

        $item = Item::findOrFail($validated['id_item']);

        // Enforce ownership: only the owner of the item can add a score to it
        if ($item->collection->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized. You can only score items in your own collection.'], 403);
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
    public function update(Request $request, Item $item, Criteria $criterion)
    {
        // Enforce ownership: only the owner of the item can update its score
        if ($item->collection->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized. You can only update scores for items in your own collection.'], 403);
        }

        // Validate the new value
        $validated = $request->validate([
            'value' => 'required|integer|in:0,1,2',
        ]);

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
        // Enforce ownership: only the owner of the item can delete its score
        if ($item->collection->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized. You can only delete scores from items in your own collection.'], 403);
        }

        ItemCriteria::where('id_item', $item->id)
                    ->where('id_criteria', $criterion->id_criteria)
                    ->delete();

        return response()->json(null, 204);
    }
}
