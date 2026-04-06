<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected function tenantId(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $siteId = $request->get('site_id') ?? DB::table('cms_sites')
            ->where('tenant_id', $this->tenantId())->value('id');

        $orders = DB::table('cms_orders')
            ->where('tenant_id', $this->tenantId())
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->payment_status, fn($q) => $q->where('payment_status', $request->payment_status))
            ->orderByDesc('created_at')
            ->paginate(30);

        return response()->json($orders);
    }

    public function show(string $id)
    {
        $order = DB::table('cms_orders')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$order, 404);
        return response()->json($order);
    }

    public function update(Request $request, string $id)
    {
        $order = DB::table('cms_orders')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$order, 404);

        $data = $request->validate([
            'status'          => 'nullable|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
            'tracking_number' => 'nullable|string',
            'notes'           => 'nullable|string',
        ]);
        $data['updated_at'] = now();
        DB::table('cms_orders')->where('id', $id)->update($data);
        return response()->json(DB::table('cms_orders')->find($id));
    }

    public function destroy(string $id)
    {
        DB::table('cms_orders')->where('tenant_id', $this->tenantId())->where('id', $id)
            ->update(['deleted_at' => now()]);
        return response()->json(['message' => 'Order cancelled.']);
    }

    /**
     * Update only the order status — triggers CRM deal update and notifications.
     */
    public function updateStatus(Request $request, string $id)
    {
        $order = DB::table('cms_orders')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$order, 404);

        $data = $request->validate(['status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded']);

        $timestampMap = [
            'confirmed' => 'confirmed_at',
            'shipped'   => 'shipped_at',
            'delivered' => 'delivered_at',
        ];

        $update = ['status' => $data['status'], 'updated_at' => now()];
        if (isset($timestampMap[$data['status']])) {
            $update[$timestampMap[$data['status']]] = now();
        }

        DB::table('cms_orders')->where('id', $id)->update($update);

        // Update CRM deal stage if linked
        if ($order->crm_deal_id) {
            $stageMap = ['delivered' => 'won', 'cancelled' => 'lost', 'refunded' => 'lost'];
            if (isset($stageMap[$data['status']])) {
                DB::table('crm_deals')
                    ->where('id', $order->crm_deal_id)
                    ->update(['stage' => $stageMap[$data['status']], 'updated_at' => now()]);
            }
        }

        return response()->json(['status' => $data['status']]);
    }

    /**
     * Initiate a refund (marks order + updates payment status).
     */
    public function refund(Request $request, string $id)
    {
        $order = DB::table('cms_orders')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$order, 404);

        DB::table('cms_orders')->where('id', $id)->update([
            'status'         => 'refunded',
            'payment_status' => 'refunded',
            'updated_at'     => now(),
        ]);

        // TODO: Call Razorpay refund API with $order->razorpay_payment_id

        return response()->json(['refunded' => true]);
    }

    /**
     * Return order invoice data (for PDF generation on frontend).
     */
    public function invoice(string $id)
    {
        $order = DB::table('cms_orders')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$order, 404);

        return response()->json([
            'order'    => $order,
            'items'    => json_decode($order->items ?? '[]', true),
            'shipping' => json_decode($order->shipping_address ?? '{}', true),
            'billing'  => json_decode($order->billing_address ?? '{}', true),
        ]);
    }
}
