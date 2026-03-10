<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Collection;
use App\Models\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemsController extends Controller
{
    /**
     * Display a listing of all items with complete relationship data.
     */
    public function index()
    {
        $items = Item::with(['collection', 'category1', 'category2', 'criteria'])
            ->get()
            ->map(fn($item) => $this->formatItemResponse($item));
        return response()->json($items);
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {
        // Check if the user has a collection to add items to
        $collection = Collection::where('user_id', $request->user()->id)->first();

        if (!$collection) {
            return response()->json(['message' => 'You must create a collection first before adding items.'], 403);
        }

        // Validate incoming data (collection_id is no longer needed in the request)
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url|max:255',
            'status' => 'nullable|boolean',
            'category1_id' => 'required|exists:category,id',
            'category2_id' => 'nullable|exists:category,id|different:category1_id',
        ]);

        if (($validated['status'] ?? false) === true) {
            return response()->json([
                'message' => 'An item cannot be public at creation time. Add criteria scores first, then publish it.',
            ], 422);
        }

        // Automatically assign the user's collection
        $validated['collection_id'] = $collection->id;

        $item = Item::create($validated);
        return response()->json($this->formatItemResponse($item->load(['category1', 'category2', 'criteria'])), 201);
    }

    /**
     * Display the specified item with complete relationship data.
     */
    public function show(Item $item)
    {
        $item->load(['collection', 'category1', 'category2', 'criteria']);
        return response()->json($this->formatItemResponse($item));
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, Item $item)
    {
        // Enforce ownership: only the owner of the collection can update the item
        if ($item->collection->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized. You can only update items in your own collection.'], 403);
        }

        // Validate incoming data
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url|max:255',
            'status' => 'sometimes|boolean',
            'category1_id' => 'sometimes|exists:category,id',
            'category2_id' => 'nullable|exists:category,id|different:category1_id',
        ]);

        if (array_key_exists('status', $validated) && $validated['status'] === true) {
            $requiredCriteriaCount = Criteria::count();
            $itemCriteriaCount = $item->criteria()->count();

            if ($itemCriteriaCount < $requiredCriteriaCount) {
                return response()->json([
                    'message' => 'This item cannot be published yet. All criteria scores must be set first.',
                ], 422);
            }
        }

        $item->update($validated);
        return response()->json($this->formatItemResponse($item->load(['category1', 'category2', 'criteria'])));
    }

    /**
     * Remove the specified item.
     */
    public function destroy(Request $request, Item $item)
    {
        // Enforce ownership: only the owner of the collection can delete the item
        if ($item->collection->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized. You can only delete items from your own collection.'], 403);
        }

        $item->delete();
        return response()->json(null, 204);
    }

    /**
     * Upload and assign an image to an item owned by the authenticated user.
     */
    public function uploadImage(Request $request, Item $item)
    {
        if ($item->collection->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized. You can only upload images for items in your own collection.'], 403);
        }

        $validated = $request->validate([
            'image' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if (!empty($item->image_url)) {
            $oldPath = $this->storagePathFromPublicUrl($item->image_url);
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $path = $validated['image']->store('items', 'public');
        $url = $this->getAbsoluteStorageUrl($path);

        $item->update([
            'image_url' => $url,
        ]);

        return response()->json([
            'message' => 'Item image uploaded successfully.',
            'image_url' => $url,
        ]);
    }

    /**
     * Convert a public storage URL into a disk path.
     */
    private function storagePathFromPublicUrl(string $url): ?string
    {
        $prefix = '/storage/';
        if (str_starts_with($url, $prefix)) {
            return substr($url, strlen($prefix));
        }

        return null;
    }

    /**
     * Convert a relative storage path to an absolute backend URL.
     * Example: 'items/abc123.jpg' → 'http://127.0.0.1:8000/storage/items/abc123.jpg'
     */
    private function getAbsoluteStorageUrl(string $relativePath): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        return "{$baseUrl}/storage/{$relativePath}";
    }

    /**
     * Format item response with complete category and score data.
     * Ensures category names and all scores are included in a structured format.
     */
    private function formatItemResponse($item): array
    {
        $scores = $item->criteria->mapWithKeys(fn($criterion) => [
            $criterion->name => [
                'id' => $criterion->id_criteria,
                'value' => $criterion->pivot->value,
                'value_label' => $this->getScoreLabel($criterion->pivot->value),
            ],
        ])->all();

        $imageUrl = $item->image_url;
        if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
            $imageUrl = $this->getAbsoluteStorageUrl(str_replace('/storage/', '', $imageUrl));
        }

        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'image_url' => $imageUrl,
            'status' => $item->status,
            'created_at' => $item->created_at,
            'collection_id' => $item->collection_id,
            'collection' => $item->collection,
            'category1' => [
                'id' => $item->category1->id,
                'title' => $item->category1->title,
            ],
            'category2' => $item->category2 ? [
                'id' => $item->category2->id,
                'title' => $item->category2->title,
            ] : null,
            'criteria' => $scores,
        ];
    }

    /**
     * Convert numeric score to human-readable label.
     */
    private function getScoreLabel(int $value): string
    {
        return match($value) {
            0 => 'Low',
            1 => 'Medium',
            2 => 'High',
            default => 'Unknown',
        };
    }
}
