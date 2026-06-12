<?php

namespace App\Models\AccountPlan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{
    use HasFactory;

    protected $table = 'userplanheader';

    protected $fillable = [
        'planId',
        'button_name',
        'sort_number',
        'plan_name',
        'price',
        'tagmonthYear',
        'tag',
        'button_color',
        'description',
        'recordStatus'
    ];

    public function features()
    {
        return $this->hasMany(
            UserPlanDetails::class,
            'planId',
            'planId'
        );
    }
}
