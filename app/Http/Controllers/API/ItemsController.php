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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        $validated = $this->applyImageInput($request, $validated);

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
        $validated = $this->applyImageInput($request, $validated, $item);

        $item->update($validated);
        return new ItemResource($item->load(['collection', 'category1', 'category2', 'criteria']));
    }

    /**
     * Remove the specified item.
     */
    public function destroy(Request $request, Item $item)
    {
        $this->authorize('delete', $item);

        $this->deleteManagedImage($item->image_url);
        $item->delete();
        return response()->json(null, 204);
    }

    private function applyImageInput(Request $request, array $validated, ?Item $item = null): array
    {
        $currentImageUrl = $item?->image_url;
        $hasNewImageInput = $request->hasFile('image') || $request->filled('image_base64');

        if ($request->hasFile('image')) {
            $validated['image_url'] = $this->storeUploadedImage($request);

            if ($currentImageUrl !== null) {
                $this->deleteManagedImage($currentImageUrl);
            }
        } elseif ($request->filled('image_base64')) {
            $validated['image_url'] = $this->storeBase64Image($request, (string) $request->input('image_base64'));

            if ($currentImageUrl !== null) {
                $this->deleteManagedImage($currentImageUrl);
            }
        } elseif (array_key_exists('image_url', $validated) && $validated['image_url'] !== $currentImageUrl) {
            if ($currentImageUrl !== null) {
                $this->deleteManagedImage($currentImageUrl);
            }
        } elseif (! array_key_exists('image_url', $validated) && ! $hasNewImageInput) {
            unset($validated['image_url']);
        }

        unset($validated['image'], $validated['image_base64']);

        return $validated;
    }

    private function storeUploadedImage(Request $request): string
    {
        $path = $request->file('image')->store('items', 'public');

        return $this->buildPublicStorageUrl($request, $path);
    }

    private function storeBase64Image(Request $request, string $base64Image): string
    {
        $normalized = preg_replace('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', '', $base64Image);
        $binary = base64_decode((string) $normalized, true);

        if ($binary === false) {
            throw ValidationException::withMessages([
                'image_base64' => ['The image_base64 field must contain valid base64 image data.'],
            ]);
        }

        $imageInfo = @getimagesizefromstring($binary);

        if ($imageInfo === false || ! isset($imageInfo['mime'])) {
            throw ValidationException::withMessages([
                'image_base64' => ['The image_base64 field must contain a valid image.'],
            ]);
        }

        $extension = match ($imageInfo['mime']) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => null,
        };

        if ($extension === null) {
            throw ValidationException::withMessages([
                'image_base64' => ['Only jpeg, png, gif and webp images are supported.'],
            ]);
        }

        $path = 'items/'.Str::uuid()->toString().'.'.$extension;
        Storage::disk('public')->put($path, $binary);

        return $this->buildPublicStorageUrl($request, $path);
    }

    private function buildPublicStorageUrl(Request $request, string $path): string
    {
        return rtrim($request->getSchemeAndHttpHost(), '/').'/storage/'.ltrim($path, '/');
    }

    private function deleteManagedImage(?string $imageUrl): void
    {
        if ($imageUrl === null) {
            return;
        }

        $path = parse_url($imageUrl, PHP_URL_PATH);

        if (! is_string($path) || ! str_starts_with($path, '/storage/items/')) {
            return;
        }

        Storage::disk('public')->delete(Str::after($path, '/storage/'));
    }
}
