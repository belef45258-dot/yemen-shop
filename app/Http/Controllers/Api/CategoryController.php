<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::active()
            ->roots()
            ->with(['children' => fn($q) => $q->where('is_active', true)])
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::active()
            ->where('slug', $slug)
            ->with(['children', 'products' => fn($q) => $q->active()->latest()->limit(20)])
            ->withCount('products')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category),
        ]);
    }
}
