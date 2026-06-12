<?php

namespace App\Services;

use App\Models\AccountPlan\UserSubscriptions;
use App\Models\AccountPlan\UserPlanDetails;
use Illuminate\Support\Facades\Cache;

class PlanService
{
    public static function getCurrentPlan($code)
    {
        return UserSubscriptions::where('code', $code)
            ->where('is_active', 'active')
            ->first();
    }

    public static function getFeatureValue($code, $featureCode)
    {
        return Cache::remember(
            "plan_feature_{$code}_{$featureCode}",
            60, // cache 60 minutes
            function () use ($code, $featureCode) {

                $subscription = self::getCurrentPlan($code);

                if (!$subscription) {
                    return null;
                }

                return UserPlanDetails::where('planId', $subscription->planId)
                    ->where('feature_code', $featureCode)
                    ->where('recordStatus', 'active')
                    ->value('feature_value');
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

    /**
     * GET ALL FEATURES (ARRAY)
     */
    public static function getAllFeatures($code)
    {
        return Cache::remember(
            "plan_features_all_{$code}",60,
            function () use ($code) {
                $subscription = self::getCurrentPlan($code);
                if (!$subscription) {
                    return collect();
                }
                return UserPlanDetails::where('planId', $subscription->planId)
                    ->where('recordStatus', 'active')
                    ->get();
            }
        );
    }

    /**
     * CLEAR CACHE (call after upgrade plan)
     */
    public static function clearCache($code)
    {
        Cache::forget("plan_feature_{$code}_CONNECTION_LIMIT");
        Cache::forget("plan_features_all_{$code}");
    }
}
