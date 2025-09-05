<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class PhoneValidationController extends Controller
{
    public function index()
    {
        // Fetch country codes and names from country.io
        $phones = file_get_contents("https://country.io/phone.json");
        $names  = file_get_contents("https://country.io/names.json");

        $phones = json_decode($phones, true);
        $names  = json_decode($names, true);

        $countryCodes = [];

        if ($phones && $names) {
            foreach ($phones as $code => $dial) {
                $countryCodes[] = [
                    'label' => "{$code} (+{$dial})",   // "BD (+880)"
                    'value' => "+{$dial}"              // "+880"
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $countryCodes
        ]);
    }

    public function validate_phone(Request $request)
    {
        // If country_code is missing or invalid → return 404
        if (!$request->has('country_code') || strlen($request->country_code) !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'Country code not found'
            ], 404);
        }

        // Custom validator
        $validator = Validator::make($request->all(), [
            'country_code' => 'required|string|size:2',
            'phone_number' => 'required|phone:' . $request->input('country_code'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number',
                'errors' => $validator->errors(),
            ], 404); // 👈 Force 404 for invalid phone numbers
        }

        return response()->json([
            'success' => true,
            'message' => 'Phone number is valid',
            'data' => $request->only(['country_code', 'phone_number']),
        ]);
    }

}
