<?php

namespace App\Models\AccountPlan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AccountPlan\UserPlan;

class UserPlanDetails extends Model
{
    use HasFactory;

    protected $table = 'userplandetails';

    protected $fillable = [
        'planId',
        'plan_name',
        'fid',
        'feature_name',
        'feature_code',
        'feature_value',
        'recordStatus'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function plan()
    {
        return $this->belongsTo(
            UserPlan::class,
            'planId',
            'planId'
        );
    }

    public function feature()
    {
        return $this->belongsTo(
            UserPlan::class,
            'feature_id',
            'id'
        );
    }
}
