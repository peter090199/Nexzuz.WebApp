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
    /**
     * Display a listing of the resource.
     */
    // HOME
    public function index()
    {
        //


    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     //
    //     $data = DB::select('CALL sprocReactionDetails(?)',[
    //         $id
    //     ]);




    //     $result = ["count"=>count($data),
    //                "reaction"=>$data];
    //     return response()->json($result);
    // }
    // public function show(string $id)
    // {
    //     $data = DB::select('CALL sprocReactionDetails(?)', [$id]);
    //     // return $data;
    //     $grouped = [];
    //     foreach ($data as $item) {
    //         $type = $item->reaction;
    
    //         if (!isset($grouped[$type])) {
    //             $grouped[$type] = [
    //                 'reaction' => $type,
    //                 'count' => 0,
    //                 'person' => []
    //             ];
    //         }
    //         $grouped[$type]['count']++;
    //         $grouped[$type]['person'][] = [
    //             "code" => $item->code,
    //             "fullname" =>$item->fullname,
    //             "photo_pic"=> $item->photo_pic ?? 'https://lightgreen-pigeon-122992.hostingersite.com/storage/app/public/uploads/DEFAULTPROFILE/DEFAULTPROFILE.png' 
    //         ];
    //     }
    //     $react = array_values($grouped);
    //     $result = [
    //         'count' => count($data),
    //         'reaction' => $data,
    //         'react' => $react
    //     ];
    
    //     return response()->json($result);
    // }
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     //
    //     if (Auth::check()) {
   
    //         $data = DB::select('CALL sprocReactionSave(?, ?, ?)', [
    //             Auth::user()->code,
    //             $id,
    //             $request->reaction 
    //         ]); 
   
    //         $messageParts = explode(';', $data[0]->result);
    //         $statusCode = trim($messageParts[0]);
    //         $message = trim($messageParts[1]);
    //         if ($statusCode == '1') {
    //             DB::commit();
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => $message,
    //             ]);
    //         } else {
    //             DB::rollBack();
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => $message,
    //             ], 400);
    //         }
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Authenticated',
    //     ]);

     
        
    // }





    // Like , Love , Care , Haha ,Wow , Sad , Angry

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

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
