<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\Attachmentpost;
use App\Models\Resource;
use App\Models\Userprofile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use  App\Http\Controllers\Postcomments\CommentController;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (Auth::check()) {
            try {
                $requestedCode = $request->code;
                $authCode = Auth::user()->code;

                $result = [];

                if ($requestedCode) {
                    // Scenario 1: Viewing a specific code profile
                    if ($authCode == $requestedCode) {
                        // Show all posts (including private)
                        $posts = Post::where('code', $authCode)->get();
                    } else {
                        // Show only public posts
                        $posts = Post::where('status', 1)
                            ->where('code', $requestedCode)
                            ->get();
                    }

                    $commentController = new CommentController();

                    foreach ($posts as $post) {
                        // Fetch attachments
                        $attachmentQuery = Attachmentpost::where('posts_uuid', $post->posts_uuid);

                        if ($authCode != $requestedCode) {
                            // If viewing someone else's posts, only show public attachments
                            $attachmentQuery->where('status', 1);
                        }

                        $attachments = $attachmentQuery->get();

                        // Group attachments
                        $images = $attachments->where('posts_type', 'image')->values();
                        $videos = $attachments->where('posts_type', 'video')->values();

                        // Call Postcomments@index with a fake request
                        $fakeRequest = new Request(['post_uuidOrUind' => $post->posts_uuid]);
                        $commentResponse = $commentController->index($fakeRequest);
                        $commentData = json_decode($commentResponse->getContent());

                        $result[] = [
                            "profile_pic" => $post->profile_pic,
                            "fullname" => $post->fullname,
                            "posts_uuid" => $post->posts_uuid,
                            "caption" => $post->caption,
                            "status" => $post->status,
                            "created_at" => $post->created_at,
                            "updated_at" => $post->updated_at,
                            "images" => $images,
                            "videos" => $videos,
                            "comments" => $commentData
                        ];
                    }
                }

                return response()->json([
                    'success' => true,
                    'data' => array_values($result)
                ]);
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $th->getMessage(),
                ]);
            }
        }

        // return view('testuploads');
    }

    public function index22(Request $request)
    {
        if (Auth::check()) {
            try {
                $requestedCode = $request->code;
                $authCode = Auth::user()->code;

                $result = [];

                if ($requestedCode) {
                    // Scenario 1: Viewing a specific code profile
                    if ($authCode == $requestedCode) {
                        // Show all posts (including private)
                        $posts = Post::where('code', $authCode)->get();
                    } else {
                        // Show only public posts
                        $posts = Post::where('status', 1)
                            ->where('code', $requestedCode)
                            ->get();
                    }

                    $commentController = new CommentController();
                    // Loop through posts and add attachments to result
                    foreach ($posts as $post) {
                        // Conditional attachments based on access
                        $attachmentQuery = Attachmentpost::where('posts_uuid', $post->posts_uuid);

                        if ($authCode != $requestedCode) {
                            // If viewing someone else's posts, only show public attachments
                            $attachmentQuery->where('status', 1);
                        }

                        $attachments = $attachmentQuery->get();


                        // Create a new Request object with posts_uuid as query param
                        $fakeRequest = new Request(['post_uuidOrUind' => $post->posts_uuid]);

                        // Call Postcomments@index with the fake request
                        $commentResponse = $commentController->index($fakeRequest);
                        $commentData = json_decode($commentResponse->getContent());

                        $result[] = [
                            "Fullname" => $post->created_by,
                            "status" => $post->status,
                            "caption" => $post->caption,
                            "posts_uuid" => $post->posts_uuid,
                            "posts" => $attachments,
                            "comments" => $commentData
                        ];
                    }
                }
                return response()->json(array_values($result));
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $th->getMessage(),
                ]);
            }
        }

        // return view('testuploads');
    }


    public function updatePostByTransNo(Request $request, $transNo)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'caption' => 'nullable|string',
            'status'  => 'required|integer', // status is required
            'posts.*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:3000',
            'video'   => 'nullable|mimetypes:video/mp4|max:50000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $codeuser = Auth::user()->code;
            $fullname = Auth::user()->fullname;

            // ---------- FIND EXISTING POST ----------
            $post = Post::where('transNo', $transNo)
                ->where('code', $codeuser)
                ->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found or not owned by user.'
                ], 404);
            }

            $folderuuid = $post->posts_uuid;

            // ---------- DELETE OLD ATTACHMENTS ----------
            $attachments = DB::table('attachmentposts')
                ->where('posts_uuid', $folderuuid)
                ->get();

            foreach ($attachments as $a) {
                $path = str_replace(asset('storage') . '/', '', $a->path_url);
                Storage::disk('public')->delete($path);
            }

            DB::table('attachmentposts')
                ->where('posts_uuid', $folderuuid)
                ->delete();

            // ---------- UPDATE POST ----------
            $post->update([
                'caption'    => $data['caption'] ?? $post->caption,
                'status'     => $data['status'],
                'updated_by' => $fullname,
                'updated_at' => now(),
            ]);

            // ---------- UPLOAD NEW IMAGES ----------
            if ($request->hasFile('posts')) {
                foreach ($request->file('posts') as $file) {
                    $uuid = Str::uuid();
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->storeAs("uploads/posts/{$codeuser}/{$folderuuid}", $filename, 'public');

                    DB::table('attachmentposts')->insert([
                        'code'        => $codeuser,
                        'transNo'     => $transNo,
                        'posts_uuid'  => $folderuuid,
                        'posts_uuind' => $uuid,
                        'status'      => $data['status'],
                        'path_url'    => asset("storage/app/public/uploads/posts/{$codeuser}/{$folderuuid}/{$filename}"),
                        'posts_type'  => 'image',
                        'created_by'  => $fullname,
                        'created_at'  => now(),
                    ]);
                }
            }

            // ---------- UPLOAD NEW VIDEO ----------
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $uuid = Str::uuid();
                $filename = time() . '_' . $video->getClientOriginalName();
                $video->storeAs("uploads/posts/{$codeuser}/{$folderuuid}", $filename, 'public');

                DB::table('attachmentposts')->insert([
                    'code'        => $codeuser,
                    'transNo'     => $transNo,
                    'posts_uuid'  => $folderuuid,
                    'posts_uuind' => $uuid,
                    'status'      => $data['status'],
                    'path_url'    => asset("storage/app/public/uploads/posts/{$codeuser}/{$folderuuid}/{$filename}"),
                    'posts_type'  => 'video',
                    'created_by'  => $fullname,
                    'created_at'  => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully.',
                'transNo' => $transNo,
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $th->getMessage(),
            ]);
        }
    }

    public function savePost(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'posts.*' => 'file|mimes:jpeg,png,jpg,gif|max:3000', // only images here
            'caption' => 'nullable|string',
            'status'  => 'required|integer',
            'video'   => 'nullable|mimetypes:video/mp4|max:50000' // separate video
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction(); // Start transaction

            $transNo   = Post::max('transNo');
            $newtrans  = empty($transNo) ? 1 : $transNo + 1;
            $folderuuid = Str::uuid();
            $codeuser   = Auth::user()->code;

            // Insert main post
            Post::create([
                'code'       => $codeuser,
                'posts_uuid' => $folderuuid,
                'transNo'    => $newtrans,
                'caption'    => $data['caption'] ?? null,
                'status'     => $data['status'],
                'created_by' => Auth::user()->fullname,
                'updated_by' => '',
                'created_at' => now()
            ]);

            // Save image files
            if ($request->hasFile('posts')) {
                foreach ($request->file('posts') as $file) {
                    $uuid     = Str::uuid();
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $filePath = "app/public/uploads/posts/{$codeuser}/{$folderuuid}/{$filename}";

                    // Store file
                    $file->storeAs("uploads/posts/{$codeuser}/{$folderuuid}", $filename, 'public');

                    DB::table('attachmentposts')->insert([
                        'code'        => $codeuser,
                        'transNo'     => $newtrans,
                        'posts_uuid'  => $folderuuid,
                        'posts_uuind' => $uuid,
                        'status'      => $data['status'],
                        'path_url'    => asset("storage/{$filePath}"), // consistent URL
                        'posts_type'  => 'image',
                        'created_by'  => Auth::user()->fullname,
                    ]);
                }
            }
            // Save video file
            if ($request->hasFile('video')) {
                $video    = $request->file('video');
                $uuid     = Str::uuid();
                $filename = time() . '_' . $video->getClientOriginalName();
                $videoPath = "app/public/uploads/posts/{$codeuser}/{$folderuuid}/{$filename}";

                // Store video
                $video->storeAs("uploads/posts/{$codeuser}/{$folderuuid}", $filename, 'public');

                DB::table('attachmentposts')->insert([
                    'code'        => $codeuser,
                    'transNo'     => $newtrans,
                    'posts_uuid'  => $folderuuid,
                    'posts_uuind' => $uuid,
                    'status'      => $data['status'],
                    'path_url'    => asset("storage/{$videoPath}"),
                    'posts_type'  => 'video',
                    'created_by'  => Auth::user()->fullname,
                ]);
            }

            DB::commit(); // Commit transaction
            event(new \App\Events\NewPostCreated([
                'transNo' => $newtrans,
                'code' => $codeuser,
                'caption' => $data['caption'] ?? '',
                'created_by' => Auth::user()->fullname,
                'created_at' => now(),
            ]));


            return response()->json([
                'success' => true,
                'message' => 'Successfully uploaded.'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack(); // Rollback transaction on error

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $th->getMessage()
            ]);
        }
    }

  
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'posts.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,avi,mov|max:3000', // Validate multiple files
            'caption' => 'nullable|string',
            'status' => 'required|integer',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction(); // Start transaction

            $transNo = Post::max('transNo');
            $newtrans = empty($transNo) ? 1 : $transNo + 1;

            $uploadedFiles = [];
            $folderuuid = Str::uuid();
            $codeuser = Auth::user()->code;
            Post::insert([
                [
                    'code' => $codeuser,
                    'posts_uuid' => $folderuuid,
                    'transNo' => $newtrans,
                    'caption' =>  $data['caption'],
                    'status' => $data['status'],
                    'created_by' => Auth::user()->fullname,
                    'updated_by' => '',
                    'created_at' => now()
                ]
            ]);

            if ($request->hasFile('posts')) {
                foreach ($request->file('posts') as $file) {
                    $uuid = Str::uuid();
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $filePath = "uploads/posts/{$codeuser}/{$folderuuid}/{$filename}";
                    $file->storeAs("uploads/posts/{$codeuser}/{$folderuuid}", $filename, 'public');

                    // Determine file type
                    $mime = $file->getMimeType();
                    $postType = str_contains($mime, 'video') ? 'video' : (str_contains($mime, 'image') ? 'image' : 'other');

                    // Store full URL to the file
                    $storageUrl = asset('storage/' . $filePath);

                    // Store file record in DB
                    DB::table('attachmentposts')->insert([
                        'code' => $codeuser,
                        'transNo' => $newtrans,
                        'posts_uuid' => $folderuuid,
                        'posts_uuind' => $uuid,
                        'status' => $data['status'],
                        'path_url' => 'https://lightgreen-pigeon-122992.hostingersite.com/storage/app/public/uploads/posts/' . Auth::user()->code . '/' . $folderuuid . '/' . $filename,
                        'posts_type' => $postType,
                        'created_by' => Auth::user()->fullname,
                    ]);
                    // Optional array to return or track uploaded files
                    $uploadedFiles[] = [
                        'uuid' => $uuid,
                        'folderuuid' => $folderuuid,
                        'filename' => $filename,
                        'path' => $storageUrl,
                        'type' => $postType,
                    ];
                }
            } else {
                $uuid = Str::uuid(); // Ensure $uuid is always set
                DB::table('posts')->insert([
                    'posts_uuid' => $folderuuid,
                    'transNo' => $newtrans,
                    'posts_uuind' => $uuid,
                    'caption' => $data['caption'],
                    'status' => $data['status'],
                    'code' => $codeuser,
                    'created_by' => Auth::user()->fullname
                ]);
            }

            DB::commit(); // Commit transaction
            return response()->json([
                'success' => true,
                'message' => 'Successfully uploaded.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack(); // Rollback the transaction on error
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $th->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        //
        // return 'test';
        if (Auth::check()) {
            try {

                if (Auth::check()) {
                    try {
                        $requestedCode = $request->code;
                        $authCode = Auth::user()->code;

                        $result = [];

                        // Scenario 1: Viewing a specific code profile
                        if ($authCode == $requestedCode) {
                            // Show all posts (including private)
                            $posts = Post::where('code', $authCode)
                                ->where('posts_uuid', $id)
                                ->get();
                        } else {
                            // Show only public posts
                            $posts = Post::where('status', 1)
                                ->where('code', $requestedCode)
                                ->where('posts_uuid', $id)
                                ->get();
                        }

                        // Loop through posts and add attachments to result
                        foreach ($posts as $post) {
                            // Determine if full attachments should be shown
                            if ($authCode == $requestedCode) {
                                // Show all attachments
                                $attachments = Attachmentpost::where('posts_uuid', $post->posts_uuid)->get();
                            } else {
                                // Show only public attachments
                                $attachments = Attachmentpost::where('posts_uuid', $post->posts_uuid)
                                    ->where('status', 1)
                                    ->get();
                            }

                            $profilePic = Userprofile::select('photo_pic')->where('code', $requestedCode)->first();
                            $result[] = [
                                "Fullname" => $post->created_by,
                                "photo_pic" => $profilePic->photo_pic ?? 'https://lightgreen-pigeon-122992.hostingersite.com/storage/app/public/uploads/DEFAULTPROFILE/DEFAULTPROFILE.png',
                                "status" => $post->status,
                                "caption" => $post->caption,
                                "posts_uuid" => $post->posts_uuid,
                                "posts" => $attachments
                            ];
                        }
                        return response()->json(array_values($result));
                    } catch (\Throwable $th) {
                        return response()->json([
                            'success' => false,
                            'message' => 'An error occurred: ' . $th->getMessage(),
                        ]);
                    }
                }
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $th->getMessage(),
                ]);
            }
        }
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
    public function update(Request $request, $id)
    {


        // return $id;
        $data = $request->all();

        // return $data;
        // Validate incoming data
        $validator = Validator::make($data, [
            'posts.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,avi,mov|max:3000', // Validate multiple files
            'caption' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction(); // Start transaction

            // Find the post to be updated
            $post = Post::where('posts_uuid', $id)->first();

            // If post doesn't exist or user doesn't own the post, return error
            if (!$post || $post->code !== Auth::user()->code) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this post.',
                ], 403);
            }

            // Get the transaction number and folder UUID
            $transNo = $post->transNo;
            $folderuuid = $post->posts_uuid;
            $codeuser = Auth::user()->code;

            // Update the post table
            $post->caption = $data['caption'] ?? $post->caption; // Only update if provided
            $post->status = $data['status'] ?? $post->status;   // Only update if provided
            $post->updated_by = Auth::user()->fullname;
            $post->updated_at = now();
            $post->save(); // Save updated post data

            // Handle file uploads if any
            $uploadedFiles = [];
            if ($request->hasFile('posts')) {
                // Remove old attachments related to this post before updating
                DB::table('attachmentposts')->where('posts_uuid', $folderuuid)->delete();

                foreach ($request->file('posts') as $file) {
                    $uuid = Str::uuid();
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $filePath = "uploads/posts/{$codeuser}/{$folderuuid}/{$filename}";
                    $file->storeAs("uploads/posts/{$codeuser}/{$folderuuid}", $filename, 'public');

                    // Determine file type
                    $mime = $file->getMimeType();
                    $postType = str_contains($mime, 'video') ? 'video' : (str_contains($mime, 'image') ? 'image' : 'other');

                    // Store full URL to the file
                    $storageUrl = asset('storage/' . $filePath);

                    // Store file record in DB
                    DB::table('attachmentposts')->insert([
                        'code' => $codeuser,
                        'transNo' => $transNo,
                        'posts_uuid' => $folderuuid,
                        'posts_uuind' => $uuid,
                        'status' => $data['status'],
                        'path_url' => 'https://lightgreen-pigeon-122992.hostingersite.com/storage/app/public/uploads/posts/' . Auth::user()->code . '/' . $folderuuid . '/' . $filename,
                        'created_by' => Auth::user()->fullname,
                    ]);

                    // Optional array to return or track uploaded files
                    // $uploadedFiles[] = [
                    //     'uuid' => $uuid,
                    //     'folderuuid' => $folderuuid,
                    //     'filename' => $filename,
                    //     'path' => $storageUrl,
                    //     'type' => $postType,
                    // ];
                }
            }

            DB::commit(); // Commit transaction

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated.',
                // 'uploadedFiles' => $uploadedFiles ?? [],
            ]);
        } catch (\Throwable $th) {
            DB::rollBack(); // Rollback the transaction on error
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $th->getMessage(),
            ]);
        }
    }





    /**
     * Remove the specified resource from storage.
     */
    // DELETE ALL POST TRANSACTIONS
    public function destroy(string $id)
    {
        //
        if (Auth::check()) {
            DB::beginTransaction();

            try {
                // Find the post that matches the ID and belongs to the authenticated user
                $post = Post::where('posts_uuid', $id)->where('code', Auth::user()->code)->first();

                if (!$post) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'You are not authorized to delete this post.',], 403);
                }

                $folderPath = "uploads/posts/$post->code/$post->posts_uuid";

                // Delete the entire folder and its contents
                Storage::disk('public')->deleteDirectory($folderPath);

                // Delete the post and its attachments
                Post::where('posts_uuid', $id)->delete();
                Attachmentpost::where('posts_uuid', $id)->delete();

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Post and its attachments were successfully deleted.',], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }

    // DELETE INDIVIDUAL ATTACHMENT 
    public function deleteIndividualPost(string $id)
    {
        if (Auth::check()) {
            DB::beginTransaction();

            try {
                // Find the post that matches the ID and belongs to the authenticated user
                $countAttach = Attachmentpost::where('posts_uuind', $id)->where('code', Auth::user()->code)->get();

                if (count($countAttach) > 0) {
                    $Attachment = Attachmentpost::where('posts_uuind', $id)->where('code', Auth::user()->code)->first();
                    $folderPath = "uploads/posts/$Attachment->code/$Attachment->posts_uuid/$Attachment->posts_uuind";

                    Attachmentpost::where('posts_uuind', $id)->where('code', Auth::user()->code)->delete();
                    // Delete the entire folder and its contents
                    Storage::disk('public')->deleteDirectory($folderPath);
                    DB::commit();
                    return response()->json(['success' => true, 'message' => 'Post and its attachments were successfully deleted.',], 200);
                }
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'You are not authorized to delete this post.',], 403);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }

    public function authpostshow()
    {
        if (Auth::check()) {
        }
    }
    public function showForm()
    {
        return view('post');
    }


    public function getDataPost(Request $request)
    {
        if (!Auth::check()) return response()->json([]);

        try {
            $requestedCode = $request->code;
            $authCode = Auth::user()->code;

            $posts = DB::table('posts')
                ->where('code', $requestedCode ?: $authCode)
                ->where(function ($query) use ($requestedCode, $authCode) {
                    $query->where('status', 1);
                    if (!$requestedCode || $requestedCode == $authCode) {
                        $query->orWhere('status', 0);
                    }
                })
                ->get();

            $result = [];
            foreach ($posts as $post) {
                $result[$post->posts_uuid]['Fullname'] = $post->created_by;
                $result[$post->posts_uuid]['status'] = $post->status;
                $result[$post->posts_uuid]['caption'] = $post->caption;
                $result[$post->posts_uuid]['posts'][] = [
                    'posts_uuind' => $post->posts_uuind,
                    'post' => $post->post,
                    'transNo' => $post->transNo
                ];
            }

            return response()->json(array_values($result));
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
