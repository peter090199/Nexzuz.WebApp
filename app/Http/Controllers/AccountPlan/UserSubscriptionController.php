<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\AccountPlan\UserPlan;
use App\Models\AccountPlan\UserSubscriptions;

class UserSubscriptionController extends Controller
{
    public function upgrade(Request $request)
    {
        $request->validate([
            'planId' => 'required',
            'payment_method' => 'required',
            'transaction_id' => 'nullable'
        ]);

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $user = Auth::user();

        $plan = UserPlan::where('planId', $request->planId)->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found.'
            ], 404);
        }

        // Expire existing active subscription
        UserSubscriptions::where('userId', $user->id)
            ->where('is_active', 'active')
            ->update([
                'is_active' => 'expired'
            ]);

        $subscription = UserSubscriptions::create([
            'userId'          => $user->id,      // REQUIRED
            'code'            => $user->code,
            'planId'          => $plan->planId,
            'amount'          => $plan->price,
            'payment_method'  => $request->payment_method,
            'transaction_id'  => $request->transaction_id,
            'start_date'      => Carbon::now(),
            'end_date'        => Carbon::now()->addMonth(),
            'is_active'       => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription upgraded successfully.',
            'data' => $subscription
        ]);
    }
}
