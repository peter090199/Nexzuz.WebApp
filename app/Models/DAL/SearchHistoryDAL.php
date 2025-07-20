<?php

namespace App\Models\DAL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SearchHistoryDAL extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    public function saveSearchHistory(array $data)
    {
        return user_activity::create([
            'viewer_code' => $data['viewer_code'],
            'viewed_code' => $data['viewed_code'] ?? null,
            'activity_type' => $data['activity_type'],
            'timestamp' => Carbon::now(),
        ]);
    }

}
