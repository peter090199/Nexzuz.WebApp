<?php

namespace App\Models\DAL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class SearchHistoryDAL
{
    protected string $table = 'user_activity';

    /**
     * Save a new user activity using DB facade
     *
     * @param array $data
     * @return bool
     */
    public function saveSearchHistory(array $data): bool
    {
        // Prevent DB insert if data is empty or missing critical fields
        if (empty($data['viewer_code']) || empty($data['activity_type'])) {
            return false;
        }

        return DB::table($this->table)->insert([
            'viewer_code'   => $data['viewer_code'],
            'viewed_code'   => $data['viewed_code'] ?? null,
            'activity_type' => $data['activity_type'],
            'timestamp'     => Carbon::now(),
        ]);
    }

}
