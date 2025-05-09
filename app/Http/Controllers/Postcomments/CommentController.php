<?php

namespace App\Http\Controllers\Postcomments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;
use Auth;
use App\Models\CommentPost;
use App\Models\Userprofile;
use App\Models\CommentReply;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    //  {
    // "post_uuidOrUind" : 
    // }
    public function index(Request $request)
    {
        //
        if (Auth::check()) {
            $commentpost = CommentPost::where('post_uuidOrUind', $request->post_uuidOrUind)->get();
            $result = [];
            for ($i = 0; $i < count($commentpost); $i++) {

                // $replycomment = CommentReply::where('comment_uuid', $commentpost[$i]->comment_uuid)->get();

                // $comment = [];
                // for($j = 0 ; $j < count($replycomment); $j++){
                //     $comment [$j] = [
                //         "comment_uuid" => $replycomment[$j]->comment_uuid,
                //         "replies_uuid" => $replycomment[$j]->replies_uuid,
                //         "status" => $replycomment[$j]->status, 
                //         "code" => $replycomment[$j]->code,
                //         "comment" => $replycomment[$j]->comment, 
                //         "date_comment" =>$replycomment[$j]->date_comment,
                //         "created_by" => $replycomment[$j]->created_by,
                //         "replies" => CommentReply::where('replies_uuid', $replycomment[$j]->comment_uuid)->get()
                //     ];
                // }
                $user = User::where('code', $commentpost[$i]->code)->first();

                $reply = CommentReply::where('comment_uuid', $commentpost[$i]->comment_uuid)->get();

                $replies = [];
                
                foreach ($reply as $rep) {
                    $userrep = User::where('code', $rep->code)->first();
                    $replies[] = [
                        "profile_pic" => Userprofile::where('code', $rep->code)->value('photo_pic')
                            ?? 'https://lightgreen-pigeon-122992.hostingersite.com/storage/app/public/uploads/DEFAULTPROFILE/DEFAULTPROFILE.png',
                        "fullname" => $userrep ? ($userrep->fname . ' ' . $userrep->lname) : '',
                        "id" => $rep->id,
                        "comment_uuid" => $rep->comment_uuid,
                        "status" => $rep->status,
                        "code" => $rep->code,
                        "comment" => $rep->comment,
                        "date_comment" => $rep->date_comment,
                        "created_by" => $rep->created_by,
                        "updated_by" => $rep->updated_by,
                        "created_at" => $rep->created_at,
                        "updated_at" => $rep->updated_at,
                    ];
                }

                $result[$i] = [
                    "profile_pic" => Userprofile::where('code', $commentpost[$i]->code)->value('photo_pic')
                    ?? 'https://lightgreen-pigeon-122992.hostingersite.com/storage/app/public/uploads/DEFAULTPROFILE/DEFAULTPROFILE.png',
                    "fullname" => $user ? ($user->fname . ' ' . $user->lname) : '',
                    "comment_uuid" => $commentpost[$i]->comment_uuid,
                    "post_uuidOrUind" => $commentpost[$i]->post_uuidOrUind,
                    "status" => $commentpost[$i]->status,
                    "code" => $commentpost[$i]->code,
                    "comment" => $commentpost[$i]->comment,
                    "date_comment" => $commentpost[$i]->date_comment,
                    "fullname" => $commentpost[$i]->fullname,
                    "replies" => $replies
                ];
            }
        
            return response()->json($result);
        }        
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


    //  STORE Postman Format
    // {
    //     "post_uuid": "c3e7f5ae-779b-4d64-b683-ea8883e0ff68",
    //     "comment": "This is a test comment."
    //     "status": 1 //0 - PUBLIC 1 - PRIVATE
    // }
    public function store(Request $request)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            // Validate input

            $validator = Validator::make($request->all(), [
                'post_uuid'     => 'required|string',
                'comment'       => 'required|string',
                'status'        => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                // Return validation error response if validation fails
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->all()
                ], 422); // 422 Unprocessable Entity
            }

            $comment =CommentPost::create([
                'comment_uuid'  => (string) \Str::uuid(),
                'post_uuidOrUind' => $request->post_uuid,
                'status'        => $request->status ?? 0,
                'code'          => Auth::user()->code,
                'comment'       => $request->comment,
                'date_comment'  => now(),
                'created_by'    => Auth::user()->fullname,
                'updated_by'    => null
            ]);
            // Return a success response with the created comment data
            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully',
                'data' => $comment
            ], 201);
        } else {
            // If user is not authenticated, return an error response
            return response()->json([
                'message' => 'User not authenticated'
            ], 401); // Unauthorized status
        }
    }
     
     

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    //  COMMENTREPLY Postman Format
    // {
    //     "comment_uuid": "c3e7f5ae-779b-4d64-b683-ea8883e0ff68",
    //     "comment": "This is a test comment."
    //     "status": 1 //0 - PUBLIC 1 - PRIVATE
    // }


    public function commentreply(Request $request){
         // Check if the user is authenticated
         if (Auth::check()) {
            // Validate input

            $validator = Validator::make($request->all(), [
                'comment_uuid'     => 'required|string',
                'comment'       => 'required|string',
                'status'        => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                // Return validation error response if validation fails
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->all()
                ], 422); // 422 Unprocessable Entity
            }


            $comment =CommentReply::create([
                'comment_uuid'  => $request->comment_uuid,
                // 'replies_uuid' => (string) \Str::uuid(),
                'status'        => $request->status ?? 0,
                'code'          => Auth::user()->code,
                'comment'       => $request->comment,
                'date_comment'  => now(),
                'created_by'    => Auth::user()->fullname,
                'updated_by'    => null
            ]);
            // Return a success response with the created comment data
            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully',
                'data' => $comment
            ], 201);
        } else {
            // If user is not authenticated, return an error response
            return response()->json([
                'message' => 'User not authenticated'
            ], 401); // Unauthorized status
        }
    }
}
