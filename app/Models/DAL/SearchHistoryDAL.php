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
        if (!isset($data['viewed_code'])) {
            return false; // or throw exception if needed
        }
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

    public function existsHistory(string $viewerCode, ?string $viewedCode): bool
    {
        return DB::table($this->table)
            ->where('viewer_code', $viewerCode)
            ->where('viewed_code', $viewedCode)
            ->exists();
    }

    public function getSearchHistory()
    {
        $currentUserCode = Auth::user()->code;

        $history = DB::table('user_activity')
            ->leftJoin('resources', 'user_activity.viewed_code', '=', 'resources.code')
            ->leftJoin('userprofiles', 'user_activity.viewed_code', '=', 'userprofiles.code')
            ->select(
                'user_activity.activity_type',
                'user_activity.timestamp',
                'user_activity.viewed_code',
                'resources.fullname',
                'resources.profession',
                'userprofiles.photo_pic'
            )
            ->where('user_activity.viewer_code', $currentUserCode)
            ->orderBy('user_activity.timestamp', 'desc')
            ->get()
            ->map(function ($item) {
                // Handle "search" activity differently
                if ($item->activity_type === 'search') {
                    return [
                        'type'       => 'search',
                        'keyword'    => $item->viewed_code,
                        'timestamp'  => $item->timestamp,
                    ];
                }

                // For "view" activity, show user profile info
                return [
                    'type'       => 'view',
                    'code'       => $item->viewed_code,
                    'fullname'   => $item->fullname,
                    'profession' => $item->profession,
                    'photo_pic'  => $item->photo_pic,
                    'timestamp'  => $item->timestamp,
                ];
            });

        if ($history->isEmpty()) {
            return response()->json([
                'message' => 'No search history found.',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'message' => '✅ Search history retrieved successfully.',
            'data'    => $history,
        ]);
    }

    public function deleteSearchHistory()
    {
        try {
            $currentUserCode = Auth::user()->code;

            $deleted = DB::table('user_activity')
                ->where('viewer_code', $currentUserCode)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => $deleted > 0
                    ? 'Search history cleared successfully.'
                    : 'No history found to delete.',
                'deleted_rows' => $deleted,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete search history', [
                'user_code' => Auth::user()->code,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete search history. Please try again.',
            ], 500);
        }
    }



}
