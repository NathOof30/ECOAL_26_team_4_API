<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Criteria;
use Illuminate\Http\Request;

class CriteriaController extends Controller
{
    /**
     * Display a listing of all criteria.
     */
    public function index()
    {
        $criteria = Criteria::all();
        return response()->json($criteria);
    }

    /**
     * Store a newly created criterion.
     */
    public function store(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:criteria,name',
        ]);

        $criterion = Criteria::create($validated);
        return response()->json($criterion, 201);
    }

    /**
     * Display the specified criterion.
     */
    public function show(Criteria $criterion)
    {
        return response()->json($criterion);
    }

    /**
     * Update the specified criterion.
     */
    public function update(Request $request, Criteria $criterion)
    {
        // Validate incoming data
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:criteria,name,' . $criterion->id_criteria . ',id_criteria',
        ]);

        $criterion->update($validated);
        return response()->json($criterion);
    }

    /**
     * Remove the specified criterion.
     */
    public function destroy(Criteria $criterion)
    {
        $criterion->delete();
        return response()->json(null, 204);
    }
}
