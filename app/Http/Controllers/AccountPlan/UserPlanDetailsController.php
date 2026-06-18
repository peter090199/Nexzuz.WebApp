<?php

namespace App\Http\Controllers\AccountPlan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountPlan\UserPlanDetails;

class UserPlanDetailsController extends Controller
{
    private function generateFeatureId()
    {
        $last = UserPlanDetails::latest('id')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'FID' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * SAVE
     */
    public function save(Request $request)
    {
        $request->validate([
            'plan_id'       => 'required',
            'feature_name'  => 'required',
            'feature_code'  => 'required',
            'feature_value' => 'required'
        ]);

        $feature = UserPlanDetails::create([
            'fid'           => $this->generateFeatureId(),
            'planId'        => $request->plan_id,
            'plan_name'     => $request->plan_name,
            'feature_name'  => $request->feature_name,
            'feature_code'  => strtoupper($request->feature_code),
            'feature_value' => $request->feature_value,
            'recordStatus'  => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feature added successfully.',
            'data' => $feature
        ], 201);
    }


    /**
     * GET BY PLAN
     */
    public function getByPlan($planId)
    {
        $data = UserPlanDetails::where('planId', $planId)
            ->orderBy('id')
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No features available for this plan.',
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $fid)
    {
        $feature = UserPlanDetails::where('fid', $fid)->firstOrFail();

        $feature->feature_name = $request->feature_name;
        $feature->feature_code = strtoupper($request->feature_code);
        $feature->feature_value = $request->feature_value;

        if ($request->has('recordStatus')) {
            $feature->recordStatus = $request->recordStatus;
        }

        $feature->save(); // <-- Missing

        return response()->json([
            'success' => true,
            'message' => 'Feature updated successfully',
            'data' => $feature
        ]);
    }

    /**
     * DELETE
     */
    public function delete($id)
    {
        $feature = UserPlanDetails::findOrFail($id);

        $feature->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feature deleted successfully.'
        ]);
    }
}
