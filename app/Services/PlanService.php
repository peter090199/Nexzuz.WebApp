<?php

namespace App\Services;

use App\Models\AccountPlan\UserSubscriptions;
use App\Models\AccountPlan\UserPlanDetails;
use Illuminate\Support\Facades\Cache;
use App\Models\AccountPlan\UserSubscriptionDetails;
use Illuminate\Http\Request;
use App\Models\AccountPlan\UserPlan;
use App\Models\GeneratesTransNo\ControllNumbers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PlanService
{

    public static function activateFreePlan()
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $existing = UserSubscriptions::where('code', $user->code)
                ->where('planId', 'PLN000001')
                ->where('is_active', 'active')
                ->first();

            if ($existing) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Your Free Plan is already active.'
                ], 409);
            }
            $subscription = PlanService::subscribeUser(
                $user,
                'PLN000001',
                'Free'
            );
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Free plan activated successfully.',
                'redirect_url' => "/{$user->role}/subscription",
                'data' => $subscription
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public static function subscribeUser($user, $planId, $paymentMethod = 'Free')
    {
        $now = Carbon::now();
        // Prevent duplicate active subscription
        $existing = UserSubscriptions::where('code', $user->code)
            ->where('planId', $planId)
            ->where('is_active', 'active')
            ->first();

        if ($existing) {
            throw new \Exception('You already have an active subscription for free plan.');
        }

        $plan = UserPlan::where('planId', $planId)
            ->lockForUpdate()
            ->firstOrFail();

        $transNo = self::generateControlNumber('USER_SUBSCRIPTION');

        UserSubscriptions::where('code', $user->code)
            ->where('is_active', 'active')
            ->update([
                'is_active' => 'expired',
                'updated_at' => $now
            ]);

        UserSubscriptionDetails::where('code', $user->code)
            ->where('is_active', 'active')
            ->update([
                'is_active' => 'expired',
                'updated_at' => $now
            ]);

        $subscription = UserSubscriptions::create([
            'transNo'        => $transNo,
            'code'           => $user->code,
            'planId'         => $plan->planId,
            'plan_name'      => $plan->plan_name,
            'amount'         => $plan->price,
            'payment_method' => $paymentMethod,
            'start_date'     => $now,
            'end_date'       => $now->copy()->addMonth(),
            'is_active'      => 'active'
        ]);

        $planIds = UserPlan::where('plan_level', '<=', $plan->plan_level)
            ->pluck('planId');

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
        return $subscription;
    }

    public static function getCurrentPlan($code)
    {
        return UserSubscriptions::where('code', $code)
            ->where('is_active', 'active')
            ->first();
    }

    public static function getFeatureValue($code, $featureCode)
    {
        return Cache::remember(
            "feature_{$code}_{$featureCode}",
            now()->addHours(1),
            function () use ($code, $featureCode) {

                return UserSubscriptions::join(
                    'user_plan_details',
                    'user_subscriptions.planId',
                    '=',
                    'user_plan_details.planId'
                )
                    ->where('user_subscriptions.code', $code)
                    ->where('user_subscriptions.is_active', 'active')
                    ->where('user_plan_details.is_active', 'active')
                    ->where('user_plan_details.feature_code', $featureCode)
                    ->value('user_plan_details.feature_value');
            }
        );
    }
    /**
     * YES / NO FEATURES
     */
    public static function hasFeature($code, $featureCode)
    {
        $value = self::getFeatureValue($code, $featureCode);

        return strtoupper((string) $value) === 'YES';
    }

    /**
     * GET NUMERIC LIMIT (e.g. 15 connections)
     */
    public static function getLimit($code, $featureCode)
    {
        return (int) self::getFeatureValue($code, $featureCode);
    }

    /**
     * GET ACCESS TYPE (RESTRICTED / LIMITED / FULL)
     */
    public static function getAccessType($code, $featureCode)
    {
        return strtoupper((string) self::getFeatureValue($code, $featureCode));
    }

    /**
     * CONNECTION CHECK
     */
    public static function canConnect($code, $currentCount)
    {
        $limit = self::getLimit($code, 'CONNECTION_LIMIT');
        if (!$limit) {
            return false;
        }
        return $currentCount < $limit;
    }

    /**
     * SEARCH CHECK
     */
    public static function canSearch($code)
    {
        $access = self::getAccessType($code, 'SEARCH_ACCESS');
        return in_array($access, ['FULL', 'UNLIMITED', 'YES']);
    }

    /**
     * MESSAGING CHECK
     */
    public static function canMessage($code, $dailyCount = 0)
    {
        $access = self::getAccessType($code, 'MESSAGING_ACCESS');
        if (in_array($access, ['UNLIMITED', 'FULL', 'YES'])) {
            return true;
        }
        if ($access === 'LIMITED' && $dailyCount < 10) {
            return true;
        }
        return false;
    }

    public static function generatePlanId()
    {
        return self::generateControlNumber('TRANSNO');
    }
    public static function generateFeatureId()
    {
        return self::generateControlNumber('USER_PLAN');
    }
    public static function generateSubscriptionCode()
    {
        return self::generateControlNumber('USER_SUBSCRIPTION');
    }
    public static function generateControlNumber($module)
    {
        return DB::transaction(function () use ($module) {

            $control = ControllNumbers::where('module', $module)
                ->lockForUpdate()
                ->firstOrFail();

            $control->last_number += 1;
            $control->save();

            return $control->prefix .
                str_pad($control->last_number, 6, '0', STR_PAD_LEFT);
        });
    }

    public static function getAllFeatures($code)
    {
        $subscription = self::getCurrentPlan($code);
        if (!$subscription) {
            return collect();
        }
        return UserSubscriptionDetails::where('code', $subscription->code)
            ->where('is_active', 'active')
            ->get();
    }

    public static function clearCache($code)
    {
        Cache::forget("plan_feature_{$code}_CONNECTION_LIMIT");
        Cache::forget("plan_features_all_{$code}");
    }
}
