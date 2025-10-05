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
    public function index()
    {
        //


    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        $data = DB::select('SELECT code,getFullname(code) AS fullname,getUserprofilepic(code) AS photo_pic,
            post_uuidOrUind,reaction,created_at FROM reactions WHERE post_uuidOrUind = ? AND reaction !="Unlike"', [$id]);

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
                "fullname" =>$item->fullname,
                "photo_pic"=> $item->photo_pic ?? 'https://lightgreen-pigeon-122992.hostingersite.com/storage/app/public/uploads/DEFAULTPROFILE/DEFAULTPROFILE.png' 
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


    public function edit(string $id)
    {
        //
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

    
    public function destroy(string $id)
    {
        //
    }
}
