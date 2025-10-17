<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class PasswordResetController extends Controller
{
  protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
    }

    // Step 1: Send verification code via SMS
    public function sendCode(Request $request)
    {
        $request->validate(['phone' => 'required']);

        try {
            $verification = $this->twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
                ->verifications
                ->create($request->phone, 'sms');

            return response()->json([
                'message' => 'Verification code sent',
                'sid' => $verification->sid
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Step 2: Verify code
    public function verifyCode(Request $request)
    {
        $request->validate(['phone' => 'required', 'code' => 'required']);

        try {
            $verification_check = $this->twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
                ->verificationChecks
                ->create([
                    'to' => $request->phone,
                    'code' => $request->code
                ]);

            if ($verification_check->status === 'approved') {
                return response()->json(['message' => 'Phone verified successfully']);
            } else {
                return response()->json(['message' => 'Invalid code'], 422);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
