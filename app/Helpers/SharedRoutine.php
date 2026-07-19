<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Services\PlanService;

class SharedRoutine
{
    /**
     * Check if user can create another connection.
     */
    public static function canAddConnection(string $code): array
    {
        // Default Free Plan limit
        $limit = 2;

        try {
            $planService = app(PlanService::class);

            $planLimit = (int) $planService->getFeatureValue(
                $code,
                'CONNECTION_LIMIT'
            );

            if ($planLimit > 0) {
                $limit = $planLimit;
            }
        } catch (\Throwable $e) {
            // Keep default limit (15)
        }

        $currentConnections = DB::table('follows')
            ->where('follower_code', $code)
            ->count();

        if ($currentConnections >= $limit) {
            return [
                'status' => false,
                'message' => "Your plan allows only {$limit} connections. Please upgrade your subscription.",
                'current' => $currentConnections,
                'limit' => $limit
            ];
        }

        return [
            'status' => true,
            'message' => 'Connection available.',
            'current' => $currentConnections,
            'limit' => $limit
        ];
    }
}