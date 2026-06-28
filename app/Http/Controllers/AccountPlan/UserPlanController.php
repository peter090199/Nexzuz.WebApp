<?php

namespace App\Http\Controllers\AccountPlan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountPlan\UserPlan;
use App\Services\PlanService;
use Illuminate\Support\Facades\Auth;
use App\Models\AccountPlan\UserSubscriptions;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\AccountPlan\UserPlanDetails;
use App\Models\AccountPlan\UserSubscriptionDetails;

class UserPlanController extends Controller
{
    public function myFeatures()
    {
        if (!Auth::check()) {
            return response("Unauthenticated", 401);
        }
        $code = Auth::user()->code;
        $subscription = UserSubscriptions::where('code', $code)
            ->where('is_active', 'active')
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found.'
            ], 404);
        }

        return response()->json([
            'success'   => true,
            'plan_code' => $code,
            'plan_id'  => $subscription->planId,
            'CurrentPlan' => $subscription->plan_name,
            'features'  => PlanService::getAllFeatures($code)
        ]);
    }

    public function myFeaturesx()
    {
        if (!Auth::check()) {
            return response("authenticated", 401);
        }
        $code = Auth::user()->code;
        return response()->json([
            'success' => true,
            'plan_code' => $code,
            'features' => PlanService::getAllFeatures($code)
        ]);
    }

    private function generatePlanId()
    {
        $lastPlan = UserPlan::latest('id')->first();
        $nextNumber = $lastPlan ? $lastPlan->id + 1 : 1;
        return 'PLN' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function save(Request $request)
    {
        $request->validate([
            'plan_name' => 'required',
            'button_name' => 'required',
            'price' => 'required|numeric',
            'sort_number' => 'required|numeric',
            'tag' => 'required'
        ]);

        // ✅ CHECK DUPLICATE TAG
        $exists = UserPlan::where('tag', $request->tag)->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'exists' => true,
                'message' => 'Plan tag already exists.'
            ], 409);
        }

        $plan = UserPlan::create([
            'planId' => $this->generatePlanId(),
            'plan_name' => $request->plan_name,
            'button_name' => $request->button_name,
            'sort_number' => $request->sort_number,
            'price' => $request->price,
            'tagmonthYear' => $request->tagmonthYear,
            'tag' => $request->tag,
            'button_color' => $request->button_color,
            'description' => $request->description,
            'recordStatus' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan created successfully.',
            'data' => $plan
        ]);
    }

    public function save1(Request $request)
    {
        $request->validate([
            'plan_name' => 'required',
            'button_name' => 'required',
            'price' => 'required|numeric',
            'sort_number' => 'required|numeric',
        ]);

        $exists = UserPlan::where('tag', $request->tag)->first();
        if ($exists) {
            return response()->json([
                'exists' => false,
                'message' => 'Plan tag already exists.'
            ], 409);
        }


        $plan = UserPlan::create([
            'planId' => $this->generatePlanId(),
            'plan_name' => $request->plan_name,
            'button_name' => $request->button_name,
            'sort_number' => $request->sort_number,
            'price' => $request->price,
            'tagmonthYear' => $request->tagmonthYear,
            'tag' => $request->tag,
            'button_color' => $request->button_color,
            'description' => $request->description,
            'recordStatus' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan created successfully.',
            'data' => $plan
        ]);
    }

    public function index()
    {
        $plans = UserPlan::where('recordStatus', 'active')
            ->orderBy('sort_number', 'asc')
            ->get();

        if (Auth::check()) {

            $currentSubscription = UserSubscriptions::where('code', Auth::user()->code)
                ->where('is_active', 'active')
                ->first();

            if ($currentSubscription) {

                $currentPlan = UserPlan::where('planId', $currentSubscription->planId)
                    ->first();

                if ($currentPlan) {

                    foreach ($plans as $plan) {

                        // Current Plan
                        if ($plan->planId == $currentPlan->planId) {
                            $plan->button_name = 'Current Plan';
                            $plan->disabled = true;
                        }
                        // Downgrade
                        elseif ($plan->plan_level < $currentPlan->plan_level) {
                            $plan->button_name = 'Downgrade';
                            $plan->disabled = true;
                        }
                        // Upgrade
                        else {
                            $plan->disabled = false;
                        }
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }


    public function indexx()
    {
        $plans = UserPlan::where('recordStatus', 'active')
            ->orderBy('sort_number', 'asc')
            ->get();
        if (Auth::check()) {
            $currentSubscription = UserSubscriptions::where('code', Auth::user()->code)
                ->where('is_active', 'active')
                ->first();

            if ($currentSubscription) {

                foreach ($plans as $plan) {

                    if ($plan->planId == $currentSubscription->planId) {
                        $plan->button_name = 'Current Plan';
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

   

    public function show($planId)
    {
        $plan = UserPlan::find($planId);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $plan
        ]);
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        $plan = UserPlan::find($id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found.'
            ], 404);
        }

        $request->validate([
            'plan_name' => 'required',
            'button_name' => 'required',
            'price' => 'required|numeric'
        ]);

        $plan->update([
            'plan_name' => $request->plan_name,
            'button_name' => $request->button_name,
            'sort_number' => $request->sort_number,
            'price' => $request->price,
            'tagmonthYear' => $request->tagmonthYear,
            'tag' => $request->tag,
            'button_color' => $request->button_color,
            'description' => $request->description,
            'recordStatus' => $request->recordStatus ?? $plan->recordStatus
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan updated successfully.',
            'data' => $plan
        ]);
    }

    /**
     * DELETE
     */
    public function destroy($id)
    {
        $plan = UserPlan::find($id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found.'
            ], 404);
        }

        $plan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Plan deleted successfully.'
        ]);
    }

    /**
     * SOFT DELETE / DEACTIVATE
     */
    public function deactivate($id)
    {
        $plan = UserPlan::find($id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found.'
            ], 404);
        }

        $plan->update([
            'recordStatus' => 'inactive'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan deactivated successfully.'
        ]);
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'planId' => 'required|exists:userplanheader,planId',
            'payment_method' => 'required|string|max:50',
        ]);

        DB::beginTransaction();

        try {

            $user = Auth::user();
            $now = Carbon::now();

            // Lock selected plan
            $plan = UserPlan::where('planId', $request->planId)
                ->lockForUpdate()
                ->firstOrFail();

            // Check if already subscribed
            $activeSubscription = UserSubscriptions::where('code', $user->code)
                ->where('is_active', 'active')
                ->lockForUpdate()
                ->first();

            if ($activeSubscription && $activeSubscription->planId === $plan->planId) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'You are already subscribed to this plan.'
                ], 409);
            }

            $transNo = PlanService::generateControlNumber('USER_SUBSCRIPTION');

            // Expire previous subscription
            UserSubscriptions::where('code', $user->code)
                ->where('is_active', 'active')
                ->update([
                    'is_active' => 'expired',
                    'updated_at' => $now
                ]);

            // Expire previous features
            UserSubscriptionDetails::where('code', $user->code)
                ->where('is_active', 'active')
                ->update([
                    'is_active' => 'expired',
                    'updated_at' => $now
                ]);

            // Create subscription
            $subscription = UserSubscriptions::create([
                'transNo'        => $transNo,
                'code'           => $user->code,
                'planId'         => $plan->planId,
                'plan_name'      => $plan->plan_name,
                'amount'         => $plan->price,
                'payment_method' => $request->payment_method,
                'start_date'     => $now,
                'end_date'       => $now->copy()->addMonth(),
                'is_active'      => 'active'
            ]);

            // Get inherited plan IDs
            $planIds = UserPlan::where('plan_level', '<=', $plan->plan_level)
                ->pluck('planId');

            // Get active features
            $features = UserPlanDetails::whereIn('planId', $planIds)
                ->where('recordStatus', 'active')
                ->whereNotNull('feature_code')
                ->get();

            $details = [];

            foreach ($features as $feature) {

                $details[] = [
                    'transNo'       => $transNo,
                    'code'          => $user->code,
                    'planId'        => $feature->planId,
                    'plan_name'     => $feature->plan_name,
                    'fid'           => $feature->fid,
                    'feature_code'  => $feature->feature_code,
                    'feature_name'  => $feature->feature_name,
                    'feature_value' => $feature->feature_value,
                    'is_active'     => 'active',
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }

            if (!empty($details)) {
                UserSubscriptionDetails::insert($details);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subscription upgraded successfully.',
                'data' => [
                    'transNo' => $subscription->transNo,
                    'planId' => $subscription->planId,
                    'plan_name' => $subscription->plan_name,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date
                ]
            ], 200);
        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to process your subscription. Please try again.'
            ], 500);
        }
    }

    // public function upgrade(Request $request)
    // {
    //     $request->validate([
    //         'planId' => 'required'
    //     ]);
    //     DB::beginTransaction();
    //     try {
    //         $userCode = Auth::user()->code;
    //         $transNo = PlanService::generatePlanId();

    //         $plan = UserPlan::where('planId', $request->planId)->firstOrFail();
    //         // Expire old subscription
    //         UserSubscriptions::where('code', $userCode)
    //             ->where('is_active', 'active')
    //             ->update([
    //                 'is_active' => 'expired'
    //             ]);

    //         UserSubscriptionDetails::where('code', $userCode)
    //             ->where('is_active', 'active')
    //             ->update([
    //                 'is_active' => 'expired'
    //             ]);

    //         // Create new subscription
    //         $subscription = UserSubscriptions::create([
    //             'transNo' => $transNo,
    //             'code' => $userCode,
    //             'planId' => $plan->planId,
    //             'amount' => $plan->price,
    //             'payment_method' => $request->payment_method,
    //             'transaction_id' => $request->transaction_id,
    //             'start_date' => Carbon::now(),
    //             'end_date' => Carbon::now()->addMonth(),
    //             'is_active' => 'active'
    //         ]);

    //         // Get inherited plan IDs
    //         $planIds = UserPlan::where('plan_level', '<=', $plan->plan_level)
    //             ->pluck('planId');

    //         // Get all inherited features
    //         $features = UserPlanDetails::whereIn('planId', $planIds)
    //             ->where('recordStatus', 'active')
    //             ->get();

    //         // Save snapshot
    //         foreach ($features as $feature) {
    //             UserSubscriptionDetails::create([
    //                 'transNo' => $transNo,
    //                 'code'          => $userCode,
    //                 'planId'        => $feature->planId,
    //                 'plan_name'     => $feature->plan_name,
    //                 'fid'           => $feature->fid,
    //                 'feature_code'  => $feature->feature_code,
    //                 'feature_name'  => $feature->feature_name,
    //                 'feature_value' => $feature->feature_value,
    //                 'is_active'  => 'active'
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Subscription upgraded successfully.',
    //             'subscription' => $subscription
    //         ]);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function upgradexx(Request $request)
    {
        $request->validate([
            'planId' => 'required'
        ]);

        $userCode = Auth::user()->code;
        $plan = UserPlan::where('planId', $request->planId)->firstOrFail();
        UserSubscriptions::where('code', $userCode)
            ->where('is_active', 'active')
            ->update([
                'is_active' => 'expired'
            ]);

        $subscription = UserSubscriptions::create([
            'code' => $userCode,
            'planId' => $plan->planId,
            'amount' => $plan->price,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonth(),
            'is_active' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription upgraded successfully.',
            'subscription' => $subscription
        ]);
    }
}
