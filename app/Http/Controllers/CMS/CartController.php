<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    private function sessionKey(Request $request, int $siteId): string
    {
        return 'cms_cart_' . $siteId . '_' . $request->session()->getId();
    }

    private function resolveCart(Request $request, int $siteId): object
    {
        $sessionId = $this->sessionKey($request, $siteId);

        $cart = DB::table('cms_cart_sessions')
            ->where('session_id', $sessionId)
            ->first();

        if (!$cart) {
            $id = DB::table('cms_cart_sessions')->insertGetId([
                'session_id' => $sessionId,
                'user_id'    => auth()->check() ? auth()->id() : null,
                'site_id'    => $siteId,
                'items'      => json_encode([]),
                'subtotal'   => 0,
                'total'      => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $cart = DB::table('cms_cart_sessions')->find($id);
        }

        return $cart;
    }

    /** GET /store/{siteId}/cart */
    public function index(Request $request, int $siteId)
    {
        $cart  = $this->resolveCart($request, $siteId);
        $items = json_decode($cart->items ?? '[]', true);
        return response()->json([
            'items'    => $items,
            'subtotal' => $cart->subtotal,
            'total'    => $cart->total,
            'coupon'   => $cart->coupon_code,
            'discount' => $cart->discount,
        ]);
    }

    /** POST /store/{siteId}/cart/add */
    public function add(Request $request, int $siteId)
    {
        $data = $request->validate([
            'product_id'  => 'required|integer',
            'qty'         => 'nullable|integer|min:1',
            'variant_key' => 'nullable|string',
        ]);

        $product = DB::table('cms_products')
            ->where('id', $data['product_id'])
            ->where('site_id', $siteId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$product, 404, 'Product not found.');
        abort_if($product->stock_qty <= 0, 422, 'Out of stock.');

        $cart  = $this->resolveCart($request, $siteId);
        $items = json_decode($cart->items ?? '[]', true);
        $key   = $data['product_id'] . ':' . ($data['variant_key'] ?? 'default');

        $images = json_decode($product->images ?? '[]', true);
        $primaryImg = $images[0]['url'] ?? null;

        $found = false;
        foreach ($items as &$item) {
            if ($item['key'] === $key) {
                $item['qty'] = min($item['qty'] + ($data['qty'] ?? 1), 10);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $items[] = [
                'key'        => $key,
                'product_id' => $product->id,
                'name'       => $product->name,
                'slug'       => $product->slug,
                'brand'      => $product->brand ?? null,
                'unit_price' => (float) $product->price,
                'mrp'        => (float) $product->mrp,
                'qty'        => $data['qty'] ?? 1,
                'image'      => $primaryImg,
                'sku'        => $product->sku,
                'variant'    => $data['variant_key'] ?? null,
            ];
        }

        $this->saveCart($cart->id, $items, $siteId, $cart->coupon_code, $cart->discount);
        return response()->json(['added' => true, 'cart_count' => array_sum(array_column($items, 'qty'))]);
    }

    /** PUT /store/{siteId}/cart/{key} */
    public function update(Request $request, int $siteId, string $key)
    {
        $qty  = max(0, (int) $request->input('qty', 1));
        $cart = $this->resolveCart($request, $siteId);
        $items = json_decode($cart->items ?? '[]', true);

        if ($qty === 0) {
            $items = array_filter($items, fn($i) => $i['key'] !== $key);
            $items = array_values($items);
        } else {
            foreach ($items as &$item) {
                if ($item['key'] === $key) { $item['qty'] = min($qty, 10); break; }
            }
        }

        $this->saveCart($cart->id, $items, $siteId, $cart->coupon_code, $cart->discount);
        return response()->json(['updated' => true]);
    }

    /** DELETE /store/{siteId}/cart/{key} */
    public function remove(Request $request, int $siteId, string $key)
    {
        $cart  = $this->resolveCart($request, $siteId);
        $items = array_values(array_filter(json_decode($cart->items ?? '[]', true), fn($i) => $i['key'] !== $key));
        $this->saveCart($cart->id, $items, $siteId, $cart->coupon_code, $cart->discount);
        return response()->json(['removed' => true]);
    }

    /** POST /store/{siteId}/cart/coupon */
    public function applyCoupon(Request $request, int $siteId)
    {
        $code = strtoupper(trim($request->input('code', '')));

        $coupon = DB::table('cms_coupons')
            ->where('site_id', $siteId)
            ->where('code', $code)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired coupon.']);
        }

        $cartTotal = (float) $request->input('cart_total', 0);

        if ($coupon->min_order_value > 0 && $cartTotal < $coupon->min_order_value) {
            return response()->json(['valid' => false, 'message' => "Minimum order ₹{$coupon->min_order_value} required."]);
        }

        $discount = match($coupon->type) {
            'percent'      => min($cartTotal * $coupon->value / 100, $coupon->max_discount ?? PHP_INT_MAX),
            'fixed'        => min($coupon->value, $cartTotal),
            'free_shipping'=> 0, // handled at order level
            default        => 0,
        };

        $cart = $this->resolveCart($request, $siteId);
        $this->saveCart($cart->id, json_decode($cart->items ?? '[]', true), $siteId, $code, $discount);

        return response()->json([
            'valid'    => true,
            'discount' => round($discount, 2),
            'message'  => "✅ Coupon applied! You save ₹" . number_format($discount, 0),
        ]);
    }

    private function saveCart(int $id, array $items, int $siteId, ?string $coupon, float $discount): void
    {
        $subtotal = array_sum(array_map(fn($i) => $i['unit_price'] * $i['qty'], $items));
        $site     = DB::table('cms_sites')->find($siteId);
        $settings = json_decode($site?->settings ?? '{}', true);
        $shipping  = ($subtotal >= ($settings['free_shipping_threshold'] ?? 499)) ? 0 : ($settings['flat_shipping_rate'] ?? 49);
        $tax       = round($subtotal * 0.18, 2);
        $total     = max(0, $subtotal + $tax + $shipping - $discount);

        DB::table('cms_cart_sessions')->where('id', $id)->update([
            'items'       => json_encode(array_values($items)),
            'coupon_code' => $coupon,
            'discount'    => $discount,
            'subtotal'    => $subtotal,
            'total'       => $total,
            'updated_at'  => now(),
        ]);
    }
}
