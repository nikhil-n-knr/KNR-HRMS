<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    protected function tenantId(): int { return auth()->user()->tenant_id; }
    protected function siteId(Request $request): ?int {
        return $request->get('site_id') ?? DB::table('cms_sites')->where('tenant_id', $this->tenantId())->value('id');
    }

    public function index(Request $request)
    {
        return response()->json(
            DB::table('cms_coupons')
                ->where('tenant_id', $this->tenantId())
                ->where('site_id', $this->siteId($request))
                ->whereNull('deleted_at')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'             => 'required|string|max:50',
            'type'             => 'required|in:percent,fixed,buy_x_get_y,bundle,free_shipping',
            'value'            => 'required|numeric|min:0',
            'description'      => 'nullable|string',
            'min_order_value'  => 'nullable|numeric|min:0',
            'max_discount'     => 'nullable|numeric|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'per_user_limit'   => 'nullable|integer|min:1',
            'valid_from'       => 'nullable|date',
            'valid_until'      => 'nullable|date',
            'conditions'       => 'nullable|array',
            'is_active'        => 'nullable|boolean',
        ]);

        $data['tenant_id']   = $this->tenantId();
        $data['site_id']     = $this->siteId($request);
        $data['code']        = strtoupper($data['code']);
        $data['used_count']  = 0;
        $data['is_active'] ??= true;
        $data['created_at']  = now();
        $data['updated_at']  = now();

        if (isset($data['conditions'])) $data['conditions'] = json_encode($data['conditions']);

        $id = DB::table('cms_coupons')->insertGetId($data);
        return response()->json(DB::table('cms_coupons')->find($id), 201);
    }

    public function show(string $id)
    {
        $coupon = DB::table('cms_coupons')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$coupon, 404);
        return response()->json($coupon);
    }

    public function update(Request $request, string $id)
    {
        DB::table('cms_coupons')->where('tenant_id', $this->tenantId())->where('id', $id)
            ->update(array_merge($request->only([
                'code','type','value','description','min_order_value','max_discount',
                'usage_limit','per_user_limit','valid_from','valid_until','is_active',
            ]), ['updated_at' => now()]));
        return response()->json(DB::table('cms_coupons')->find($id));
    }

    public function destroy(string $id)
    {
        DB::table('cms_coupons')->where('tenant_id', $this->tenantId())->where('id', $id)
            ->update(['deleted_at' => now()]);
        return response()->json(['deleted' => true]);
    }

    /**
     * Validate a coupon code before checkout (called client-side).
     */
    public function validateCode(Request $request)
    {
        $data = $request->validate([
            'code'       => 'required|string',
            'cart_total' => 'required|numeric',
            'site_id'    => 'required|integer',
        ]);

        $coupon = DB::table('cms_coupons')
            ->where('site_id', $data['site_id'])
            ->where('code', strtoupper($data['code']))
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired coupon code.']);
        }

        if ($coupon->valid_until && now()->gt($coupon->valid_until)) {
            return response()->json(['valid' => false, 'message' => 'This coupon has expired.']);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['valid' => false, 'message' => 'This coupon has reached its usage limit.']);
        }

        if ($coupon->min_order_value && $data['cart_total'] < $coupon->min_order_value) {
            return response()->json([
                'valid'   => false,
                'message' => 'Minimum order of ₹' . number_format($coupon->min_order_value, 0) . ' required.',
            ]);
        }

        $discount = 0;
        if ($coupon->type === 'percent') {
            $discount = ($data['cart_total'] * $coupon->value / 100);
            if ($coupon->max_discount) $discount = min($discount, $coupon->max_discount);
        } elseif ($coupon->type === 'fixed') {
            $discount = min($coupon->value, $data['cart_total']);
        } elseif ($coupon->type === 'free_shipping') {
            $discount = 0; // Handled by checkout
        }

        return response()->json([
            'valid'        => true,
            'coupon'       => $coupon,
            'discount'     => round($discount, 2),
            'message'      => 'Coupon applied! You save ₹' . number_format($discount, 2),
        ]);
    }
}
