<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Criteria\StoreCriteriaRequest;
use App\Http\Requests\Criteria\UpdateCriteriaRequest;
use App\Http\Resources\CriteriaResource;
use App\Models\Criteria;

class CriteriaController extends Controller
{
    /**
     * Display a listing of all criteria.
     */
    public function index()
    {
        $criteria = Criteria::all();
        return CriteriaResource::collection($criteria);
    }

    /**
     * Store a newly created criterion.
     */
    public function store(StoreCriteriaRequest $request)
    {
        $validated = $request->validated();

        $criterion = Criteria::create($validated);
        return (new CriteriaResource($criterion))->response()->setStatusCode(201);
    }

    /**
     * Display the specified criterion.
     */
    public function show(Criteria $criterion)
    {
        return new CriteriaResource($criterion);
    }

    /**
     * Update the specified criterion.
     */
    public function update(UpdateCriteriaRequest $request, Criteria $criterion)
    {
        $validated = $request->validated();

        $criterion->update($validated);
        return new CriteriaResource($criterion);
    }

    /**
     * Remove the specified criterion.
     */
    public function destroy(Criteria $criterion)
    {
        $this->authorize('delete', $criterion);
        $criterion->delete();
        return response()->json(null, 204);
    }
}
