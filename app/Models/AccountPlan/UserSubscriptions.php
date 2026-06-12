<?php

namespace App\Models\AccountPlan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscriptions extends Model
{
    use HasFactory;
    protected $table = 'usersubscription';

    protected $fillable = [
        'code',
        'planId',
        'is_active'
    ];
}
