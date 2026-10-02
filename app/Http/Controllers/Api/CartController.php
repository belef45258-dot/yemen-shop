<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $cart->load('items.product.images');

        return response()->json([
            'success' => true,
            'data' => new CartResource($cart),
            'totals' => $this->calculateTotals($cart),
        ]);
    }

    public function addItem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:100',
        ]);

        return DB::transaction(function () use ($request, $data) {
            $cart = $this->resolveCart($request);
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            if (! $product->is_active || ! $product->is_in_stock) {
                throw ValidationException::withMessages([
                    'product' => 'هذا المنتج غير متوفر حاليًا.',
                ]);
            }

            $existing = $cart->items()->where('product_id', $product->id)->first();
            $newQty = ($existing?->quantity ?? 0) + $data['quantity'];

            if ($newQty > $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "الكمية المطلوبة غير متوفرة. المتاح: {$product->stock}",
                ]);
            }

            if ($existing) {
                $existing->update(['quantity' => $newQty]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $data['quantity'],
                    'unit_price' => $product->final_price,
                ]);
            }

            $cart->refresh()->load('items.product.images');

            return response()->json([
                'success' => true,
                'message' => 'تمت إضافة المنتج للسلة.',
                'data'    => new CartResource($cart),
                'totals'  => $this->calculateTotals($cart),
            ]);
        });
    }

    public function updateItem(Request $request, int $itemId): JsonResponse
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:0|max:100',
        ]);

        return DB::transaction(function () use ($request, $itemId, $data) {
            $cart = $this->resolveCart($request);
            $item = $cart->items()->with('product')->findOrFail($itemId);

            if ($data['quantity'] === 0) {
                $item->delete();
                $msg = 'تم حذف المنتج من السلة.';
            } elseif ($data['quantity'] > $item->product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "المتاح فقط: {$item->product->stock}",
                ]);
            } else {
                $item->update(['quantity' => $data['quantity']]);
                $msg = 'تم تحديث الكمية.';
            }

            $cart->refresh()->load('items.product.images');

            return response()->json([
                'success' => true,
                'message' => $msg,
                'data'    => new CartResource($cart),
                'totals'  => $this->calculateTotals($cart),
            ]);
        });
    }

    public function removeItem(Request $request, int $itemId): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $cart->items()->findOrFail($itemId)->delete();
        $cart->load('items.product.images');

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنتج.',
            'data'    => new CartResource($cart),
            'totals'  => $this->calculateTotals($cart),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $cart->items()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم إفراغ السلة.',
        ]);
    }

    private function resolveCart(Request $request): Cart
    {
        $userId = $request->user()?->id;
        $sessionId = $request->header('X-Session-ID');

        if ($userId) {
            return Cart::firstOrCreate(['user_id' => $userId], ['currency' => 'YER']);
        }

        $sessionId ??= 'guest_' . uniqid('', true);

        return Cart::firstOrCreate(['session_id' => $sessionId], ['currency' => 'YER']);
    }

    private function calculateTotals(Cart $cart): array
    {
        $subtotal = $cart->subtotal;
        $tax = 0;
        $shipping = $subtotal > 50000 ? 0 : 2000;
        $total = $subtotal + $tax + $shipping;

        return [
            'subtotal' => round($subtotal, 2),
            'tax'      => $tax,
            'shipping' => $shipping,
            'total'    => round($total, 2),
            'currency' => $cart->currency,
        ];
    }
}
