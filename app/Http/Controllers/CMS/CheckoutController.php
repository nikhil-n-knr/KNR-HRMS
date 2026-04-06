<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected function siteById(int $siteId): ?object
    {
        return DB::table('cms_sites')->where('id', $siteId)->first();
    }

    /**
     * Step 1: Create a Razorpay order and return the order_id to frontend.
     * Frontend opens Razorpay modal with this order_id.
     */
    public function createOrder(Request $request)
    {
        $data = $request->validate([
            'site_id'          => 'required|integer',
            'items'            => 'required|array',
            'shipping_address' => 'nullable|array',
            'billing_address'  => 'nullable|array',
            'coupon_code'      => 'nullable|string',
            'guest_email'      => 'nullable|email',
            'guest_phone'      => 'nullable|string',
        ]);

        $site = $this->siteById($data['site_id']);
        if (!$site || !$site->razorpay_key_id) {
            return response()->json(['error' => 'Payment gateway not configured.'], 422);
        }

        // Calculate order total
        $subtotal  = 0;
        $itemsSnap = [];
        foreach ($data['items'] as $item) {
            $product = DB::table('cms_products')->where('id', $item['product_id'])->first();
            if (!$product) continue;
            $qty        = max(1, (int) $item['qty']);
            $unitPrice  = (float) $product->price;
            $lineTotal  = $unitPrice * $qty;
            $subtotal  += $lineTotal;
            $itemsSnap[] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'sku'        => $product->sku,
                'qty'        => $qty,
                'unit_price' => $unitPrice,
                'total'      => $lineTotal,
                'image'      => json_decode($product->images ?? '[]')[0]->url ?? null,
            ];
        }

        // Coupon discount
        $discount   = 0;
        $couponCode = null;
        if (!empty($data['coupon_code'])) {
            $coupon = DB::table('cms_coupons')
                ->where('site_id', $data['site_id'])
                ->where('code', strtoupper($data['coupon_code']))
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();
            if ($coupon) {
                $discount   = $coupon->type === 'percent'
                    ? min($subtotal * $coupon->value / 100, $coupon->max_discount ?? PHP_INT_MAX)
                    : min($coupon->value, $subtotal);
                $couponCode = $coupon->code;
            }
        }

        $shipping = 49; // Base shipping — free above site threshold
        $settings = json_decode($site->settings ?? '{}', true);
        if (($settings['free_shipping_threshold'] ?? 499) <= $subtotal) $shipping = 0;

        $tax   = round($subtotal * 0.18, 2); // Default GST 18% — refine per product tax_class
        $total = round($subtotal + $tax + $shipping - $discount, 2);

        // Create Razorpay order via API
        $rzpOrderId = null;
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://api.razorpay.com/v1/orders', [
                'auth' => [$site->razorpay_key_id, $site->razorpay_key_secret],
                'json' => [
                    'amount'          => (int) round($total * 100), // paise
                    'currency'        => $site->currency ?? 'INR',
                    'receipt'         => 'order_' . Str::random(8),
                    'payment_capture' => 1,
                ],
            ]);
            $rzpOrderId = json_decode($response->getBody(), true)['id'];
        } catch (\Exception $e) {
            \Log::error('Razorpay create order failed: ' . $e->getMessage());
            return response()->json(['error' => 'Payment gateway error. Please try again.'], 500);
        }

        // Save pending CMS order
        $orderNumber = 'ORD-' . strtoupper(Str::random(8));
        $cmsOrderId  = DB::table('cms_orders')->insertGetId([
            'tenant_id'          => $site->tenant_id ?? 0,
            'site_id'            => $data['site_id'],
            'order_number'       => $orderNumber,
            'guest_email'        => $data['guest_email'] ?? null,
            'guest_phone'        => $data['guest_phone'] ?? null,
            'user_id'            => auth()->check() ? auth()->id() : null,
            'items'              => json_encode($itemsSnap),
            'subtotal'           => $subtotal,
            'tax'                => $tax,
            'shipping'           => $shipping,
            'discount'           => $discount,
            'total'              => $total,
            'coupon_code'        => $couponCode,
            'coupon_discount'    => $discount,
            'status'             => 'pending',
            'payment_status'     => 'pending',
            'payment_method'     => 'razorpay',
            'razorpay_order_id'  => $rzpOrderId,
            'shipping_address'   => json_encode($data['shipping_address'] ?? []),
            'billing_address'    => json_encode($data['billing_address'] ?? []),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return response()->json([
            'cms_order_id'    => $cmsOrderId,
            'razorpay_order_id' => $rzpOrderId,
            'razorpay_key_id' => $site->razorpay_key_id,
            'amount'          => (int) round($total * 100),
            'currency'        => $site->currency ?? 'INR',
            'order_number'    => $orderNumber,
            'summary'         => [
                'subtotal' => $subtotal,
                'tax'      => $tax,
                'shipping' => $shipping,
                'discount' => $discount,
                'total'    => $total,
            ],
        ]);
    }

    /**
     * Step 2: Verify Razorpay payment signature after frontend completes payment.
     */
    public function verifyPayment(Request $request)
    {
        $data = $request->validate([
            'cms_order_id'        => 'required|integer',
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $order = DB::table('cms_orders')->where('id', $data['cms_order_id'])->first();
        abort_if(!$order, 404, 'Order not found.');

        $site = $this->siteById($order->site_id);

        // Verify HMAC signature
        $expectedSig = hash_hmac(
            'sha256',
            $data['razorpay_order_id'] . '|' . $data['razorpay_payment_id'],
            $site->razorpay_key_secret
        );

        if (!hash_equals($expectedSig, $data['razorpay_signature'])) {
            // Log tamper attempt
            \Log::warning('Razorpay signature mismatch for order #' . $order->order_number);
            DB::table('cms_orders')->where('id', $order->id)
                ->update(['payment_status' => 'failed', 'status' => 'cancelled', 'updated_at' => now()]);
            return response()->json(['verified' => false, 'error' => 'Payment verification failed.'], 422);
        }

        // Payment is genuine — update order
        DB::table('cms_orders')->where('id', $order->id)->update([
            'payment_status'      => 'paid',
            'status'              => 'confirmed',
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'razorpay_signature'  => $data['razorpay_signature'],
            'confirmed_at'        => now(),
            'updated_at'          => now(),
        ]);

        // Deduct stock
        $items = json_decode($order->items, true) ?? [];
        foreach ($items as $item) {
            DB::table('cms_products')
                ->where('id', $item['product_id'])
                ->where('track_inventory', true)
                ->decrement('stock_qty', $item['qty']);
        }

        // Increment coupon usage
        if ($order->coupon_code) {
            DB::table('cms_coupons')
                ->where('code', $order->coupon_code)
                ->where('site_id', $order->site_id)
                ->increment('used_count');
        }

        // Create CRM Deal
        $this->createCrmDeal($order, $data['razorpay_payment_id']);

        return response()->json([
            'verified'     => true,
            'order_number' => $order->order_number,
            'total'        => $order->total,
        ]);
    }

    /**
     * Razorpay Webhook — handles payment.captured, refund.created etc.
     * This route is public (no auth middleware).
     */
    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $siteId    = $request->get('site_id');

        // We'll validate once we have site context; log for now
        \Log::info('Razorpay Webhook: ' . $payload);

        $event = $request->input('event');

        if ($event === 'payment.captured') {
            $rzpOrderId = $request->input('payload.payment.entity.order_id');
            DB::table('cms_orders')
                ->where('razorpay_order_id', $rzpOrderId)
                ->where('payment_status', 'pending')
                ->update(['payment_status' => 'paid', 'status' => 'confirmed', 'confirmed_at' => now(), 'updated_at' => now()]);
        }

        if ($event === 'refund.created') {
            $rzpPaymentId = $request->input('payload.refund.entity.payment_id');
            DB::table('cms_orders')
                ->where('razorpay_payment_id', $rzpPaymentId)
                ->update(['payment_status' => 'refunded', 'status' => 'refunded', 'updated_at' => now()]);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function createCrmDeal(object $order, string $paymentId): void
    {
        try {
            $items   = json_decode($order->items, true) ?? [];
            $title   = 'Store Order #' . $order->order_number;
            $contact = $order->guest_email ?? 'Unknown';

            $dealId = DB::table('crm_deals')->insertGetId([
                'tenant_id'  => $order->tenant_id,
                'title'      => $title,
                'value'      => $order->total,
                'stage'      => 'won',
                'source'     => 'cms_store',
                'notes'      => "Razorpay Payment #$paymentId\nItems: " . count($items) . " products",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Link back so order status updates sync to deal
            DB::table('cms_orders')->where('id', $order->id)->update(['crm_deal_id' => $dealId]);
        } catch (\Exception $e) {
            \Log::warning('CMS order→CRM deal creation failed: ' . $e->getMessage());
        }
    }
}
