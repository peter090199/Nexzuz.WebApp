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

    protected $table = 'user_activity';

    protected $fillable = [
        'viewer_code',
        'viewed_code',
        'activity_type',
        'timestamp',
    ];

    public $timestamps = false;

    /**
     * Save search history data using DB facade.
     *
     * @param array $data
     * @return bool
     */
   public function saveSearchHistory(array $data): bool
    {
        
        // Check if a similar activity already exists
        $exists = DB::table($this->table)
            ->where('viewer_code', $data['viewer_code'])
            ->where('viewed_code', $data['viewed_code'] ?? null)
            ->where('activity_type', $data['activity_type'])
            ->exists();

     
        // Insert new activity
        return DB::table($this->table)->insert([
            'viewer_code'   => $data['viewer_code'],
            'viewed_code'   => $data['viewed_code'] ?? null,
            'activity_type' => $data['activity_type'],
            'timestamp'     => Carbon::now(),
        ]);
    }

}
