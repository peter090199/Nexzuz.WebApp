<?php

namespace App\Models\AccountPlan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscriptions extends Model
{
    use HasFactory;

    protected $table = 'usersubscription';

    protected $fillable = [
        'transNo',
        'code',
        'planId',
        'plan_name',    
        'amount',
        'payment_method',
        'transaction_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function plan()
    {
        return $this->belongsTo(UserPlan::class, 'planId', 'planId');
    }
}