<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function store(Request $request, ExternalOrderService $orders): JsonResponse
    {
        $validated = $request->validate([
            'external_order_id' => 'required|string|max:120',
            'pos_order_id' => 'nullable|integer|min:1',
            'source' => 'nullable|string|max:80',
            'customer' => 'nullable|string|max:120',
            'payment_method' => 'nullable|string|max:40',
            'payment_reference' => 'nullable|string|max:120',
            'cashier_name' => 'nullable|string|max:120',
            'order_total' => 'nullable|integer|min:0',
            'amount_paid' => 'nullable|integer|min:0',
            'amount_change' => 'nullable|integer|min:0',
            'order_status' => 'nullable|string|max:40',
            'payment_status' => 'nullable|string|max:40',
            'ordered_at' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.menu_code' => 'required|string|max:40',
            'items.*.menu_name' => 'nullable|string|max:120',
            'items.*.menu_price' => 'nullable|integer|min:0',
            'items.*.subtotal' => 'nullable|integer|min:0',
            'items.*.status' => 'nullable|string|max:40',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variant' => 'nullable|string|max:80',
            'items.*.size' => 'nullable|string|max:80',
            'items.*.ice' => 'nullable|string|max:80',
            'items.*.sugar' => 'nullable|string|max:80',
        ]);

        $result = $orders->process($validated);

        $status = $result['status'] === 'duplicate' ? 200 : 201;

        return response()->json($result, $status);
    }
}
