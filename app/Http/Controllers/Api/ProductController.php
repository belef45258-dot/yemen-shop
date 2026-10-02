<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'category_id', 'min_price', 'max_price',
            'in_stock', 'featured', 'sort', 'per_page',
        ]);

        $query = Product::active()->with(['images', 'category'])->filter($filters);

        $query = match ($filters['sort'] ?? 'newest') {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'popular'    => $query->withCount('reviews')->orderByDesc('reviews_count'),
            default      => $query->latest(),
        };

        $perPage = min((int) ($filters['per_page'] ?? 12), 50);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::active()
            ->where('slug', $slug)
            ->with(['images', 'category'])
            ->withCount('approvedReviews')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    public function featured(): JsonResponse
    {
        $products = Product::active()
            ->featured()
            ->inStock()
            ->with(['images', 'category'])
            ->latest()
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
        ]);
    }
}
