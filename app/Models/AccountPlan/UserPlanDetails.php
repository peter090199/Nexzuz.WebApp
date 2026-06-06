<?php

namespace App\Models\AccountPlan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlanDetails extends Model
{
    use HasFactory;

    protected $table = 'userplandetails';
    protected $primaryKey = 'id';

    protected $fillable = [
        'planId',
        'plan_name',
        'fid',
        'feature_name',
        'recordStatus'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
