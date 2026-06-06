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
            'planId' => 'required',
            'feature_name' => 'required'
        ]);

        $feature = UserPlanDetails::create([
            'planId' => $request->planId,
            'plan_name' => $request->plan_name,
            'fid' => $this->generateFeatureId(),
            'feature_name' => $request->feature_name,
            'recordStatus' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feature added successfully.',
            'data' => $feature
        ]);
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
    public function update(Request $request, $id)
    {
        $feature = UserPlanDetails::findOrFail($id);

        $feature->update([
            'feature_name' => $request->feature_name,
            'recordStatus' => $request->recordStatus
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feature updated successfully.'
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
