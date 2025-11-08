<?php

namespace App\Http\Controllers\Postreaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommentPost;
use App\Models\Userprofile;
use App\Models\CommentReply;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostreactionController extends Controller
{
    public function show(string $id)
    {
        $data = DB::select('
            SELECT 
                r.code,
                CONCAT(u.fname, " ", u.lname) AS fullname,
                pf.photo_pic AS photo_pic,
                r.post_uuidOrUind,
                r.reaction,
                r.created_at
            FROM reactions AS r
            LEFT JOIN users AS u 
                ON u.code = r.code
            LEFT JOIN userprofiles AS pf 
                ON pf.code = r.code
            WHERE r.post_uuidOrUind = ?
            AND r.reaction != "Unlike"
        ', [$id]);

        $grouped = [];
        foreach ($data as $item) {
            $type = $item->reaction;

            if (!isset($grouped[$type])) {
                $grouped[$type] = [
                    'reaction' => $type,
                    'count' => 0,
                    'person' => []
                ];
            }

            $grouped[$type]['count']++;
            $grouped[$type]['person'][] = [
                "code" => $item->code,
                "fullname" => $item->fullname,
                "photo_pic" => $item->photo_pic 
            ];
        }

        $react = array_values($grouped);
        $result = [
            'count' => count($data),
            'reaction' => $data,
            'react' => $react
        ];

        return response()->json($result);
    }

    public function update(Request $request, string $id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            DB::beginTransaction();

            // Check if this user has already reacted to this post
            $exists = DB::select('SELECT COUNT(*) AS count FROM reactions WHERE code = ? AND post_uuidOrUind = ?', [
                Auth::user()->code,
                $id
            ]);

            if ($exists[0]->count > 0) {
                // Update existing reaction
                DB::update('UPDATE reactions SET reaction = ? WHERE code = ? AND post_uuidOrUind = ?', [
                    $request->reaction,
                    Auth::user()->code,
                    $id
                ]);
            } else {
                // Insert new reaction
                DB::insert('INSERT INTO reactions (code, post_uuidOrUind, reaction, created_at)
                            VALUES (?, ?, ?, NOW())', [
                     Auth::user()->code,
                    $id,
                    $request->reaction
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Success: ' . $request->reaction
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    
}
