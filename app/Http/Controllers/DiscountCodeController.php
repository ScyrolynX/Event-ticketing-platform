<?php

namespace App\Http\Controllers;

use App\Http\Controllers\DiscountCodeController;

use App\Models\DiscountCode;
use Illuminate\Http\Request;

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
}
