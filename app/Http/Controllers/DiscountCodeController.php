<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiscountCodeController extends Controller
{
    public function index()
    {
        return response()->json(['discount_codes' => DiscountCode::latest()->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:discount_codes,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'event_id' => 'nullable|exists:events,id',
        ]);

        $discountCode = DiscountCode::create($validated);

        return response()->json(['discount_code' => $discountCode], 201);
    }

    /**
     * Edit a discount code. Safe to change freely — a code's discount
     * amount is baked into each order's unit_price at purchase time, so
     * editing it later never rewrites past orders.
     */
    public function update(Request $request, DiscountCode $discountCode)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', Rule::unique('discount_codes', 'code')->ignore($discountCode->id)],
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'event_id' => 'nullable|exists:events,id',
        ]);

        $discountCode->update($validated);

        return response()->json(['discount_code' => $discountCode]);
    }

    /**
     * Delete a discount code. Also safe unconditionally — nothing else
     * references it by foreign key, so no order or ticket is affected.
     */
    public function destroy(DiscountCode $discountCode)
    {
        $discountCode->delete();

        return response()->json(['message' => 'Discount code deleted.']);
    }
}
