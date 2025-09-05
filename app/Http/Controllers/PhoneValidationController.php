<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PhoneValidationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'country_code' => 'required|string|size:2',   // Example: "PH", "US", "IN"
            'phone_number' => ['required', "phone:{$request->country_code}"],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Phone number is valid',
            'data' => $request->only(['country_code', 'phone_number']),
        ]);
    }
}
