<?php

namespace App\Models\AccountPlan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscriptionDetails extends Model
{
    use HasFactory;

    protected $table = 'usersubscription_details';

    protected $fillable = [
        'transNo',
        'code',
        'planId',
        'plan_name',
        'button_name',
        'fid',
        'feature_code',
        'feature_name',
        'feature_value',
        'is_active',
    ];

    public $timestamps = true;
}