<?php

namespace App\Models\GeneratesTransNo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControllNumbers extends Model
{
    use HasFactory;
    protected $table = 'control_numbers';

    protected $fillable = [
        'module',
        'prefix',
        'last_number'
    ];
}
