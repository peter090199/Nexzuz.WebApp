<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\PlanService;

class CheckPlanFeature
{
    public function handle($request,Closure $next,$featureCode)
    {
        if (
            !PlanService::hasFeature(
                auth()->id(),
                $featureCode
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Upgrade your plan.'
            ], 403);
        }

        return $next($request);
    }
}