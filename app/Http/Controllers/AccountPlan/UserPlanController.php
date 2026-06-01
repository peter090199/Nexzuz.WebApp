<?php

namespace App\Http\Controllers\AccountPlan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  App\Models\AccountPlan\UserPlan;

class UserPlanController extends Controller
{
    private function generatePlanId()
    {
        $lastPlan = UserPlan::latest('id')->first();
        $nextNumber = $lastPlan ? $lastPlan->id + 1 : 1;
        return 'PLN-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function save(Request $request)
    {
        $request->validate([
            'plan_name' => 'required',
            'price' => 'required|numeric'
        ]);
     
        $planId = $this->generatePlanId();
        $plan = UserPlan::create([
            'planId' => $planId,
            'plan_name' => $request->plan_name,
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
}
