<?php

namespace App\Models\DAL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SearchHistoryDAL extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

     protected $fillable = [
        'viewer_code',
        'viewed_code',
        'activity_type',
        'timestamp',
    ];

    public function saveSearchHistory($data)
    {
        $data->validate([
            'viewer_code' => 'required|string',
            'activity_type' => 'required|string',
            'viewed_code' => 'nullable|string',
        ]);

        $activity = user_activity::create([
            'viewer_code' => $data->viewer_code,
            'viewed_code' => $data->viewed_code,
            'activity_type' => $data->activity_type,
            'timestamp' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Activity recorded.',
            'data' => $activity
        ]);
    }

}
