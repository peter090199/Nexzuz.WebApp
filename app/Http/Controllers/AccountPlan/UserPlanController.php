<?php

namespace App\Http\Controllers\AccountPlan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountPlan\UserPlan;
use App\Services\PlanService;
use Illuminate\Support\Facades\Auth;

class UserPlanController extends Controller
{
    public function myFeatures(Request $request)
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

    /**
     * READ ALL
     */
    public function index()
    {
        $plans = UserPlan::where('recordStatus', 'active')
            ->orderBy('sort_number', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * READ SINGLE
     */
    public function show($id)
    {
        $plan = UserPlan::find($id);

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
}
