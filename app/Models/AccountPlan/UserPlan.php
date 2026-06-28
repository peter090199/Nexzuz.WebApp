<?php

namespace App\Models\AccountPlan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{
    use HasFactory;

    protected $table = 'userplanheader';

    // Primary Key
    protected $primaryKey = 'planId';

    // Set these based on your column type
    public $incrementing = false;   // true if planId is AUTO_INCREMENT
    protected $keyType = 'string';  // change to 'int' if planId is integer

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
        'recordStatus',
        'plan_level'
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