<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\Attachmentpost;
use App\Models\Resource;
use Illuminate\Support\Str;
use DB;
class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //         if(Auth::check()){
    //             try {
    //                 $requestedCode = $request->code;
    //                 $authCode = Auth::user()->code;
                    
    //                 $result = [];
                    
    //                 if ($requestedCode) {
    //                     // Scenario 1: Viewing a specific code profile
    //                     if ($authCode == $requestedCode) {
    //                         // If the auth code matches the requested code, show all posts (including private)
    //                         $posts = Post::all(); // Retrieve all posts (both public and private)
    //                     } else {
    //                         // If the auth code does not match the requested code, show only public posts
    //                         $posts = Post::where('status', 1)->get(); // Retrieve only public posts
    //                     }
                    
    //                     // Loop through posts and add attachments to result
    //                     foreach ($posts as $post) {
    //                         $attachment = Attachmentpost::where('posts_uuid', $post->posts_uuid)
    //                             ->where('status', 1)
    //                             ->get(); // Get public attachments
                    
    //                         $result[] = [
    //                             "Fullname" => $post->created_by,
    //                             "status" => $post->status,
    //                             "caption" => $post->caption,
    //                             "posts_uuid" => $post->posts_uuid,
    //                             "posts" => $attachment
    //                         ];
    //                     }
    //                 }
                     
    //                 return response()->json(array_values($result));
    //             } catch (\Throwable $th) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'An error occurred: ' . $th->getMessage(),
    //                 ]);
    //             }
                
    //         }
    //     // return view('testuploads');
    // }

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
                        // If the auth code matches the requested code, show all posts (including private)
                        $posts = Post::where('code', Auth::user()->code)->get(); // Get posts created by the authenticated user
                    } else {
                        // If the auth code does not match the requested code, show only public posts
                        $posts = Post::where('status', 1)
                        ->where('code', $requestedCode)
                        ->get();
                    }

                    // Loop through posts and add attachments to result
                    foreach ($posts as $post) {
                        $attachment = Attachmentpost::where('posts_uuid', $post->posts_uuid)
                            ->where('status', 1)
                            ->get(); // Get public attachments

                        $result[] = [
                            "Fullname" => $post->created_by,
                            "status" => $post->status,
                            "caption" => $post->caption,
                            "posts_uuid" => $post->posts_uuid,
                            "posts" => $attachment
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
                    'code' =>$codeuser,
                    'posts_uuid' =>$folderuuid,
                    'transNo' => $newtrans,
                    'caption' =>  $data['caption'],
                    'status' => $data['status'],
                    'created_by' => Auth::user()->fullname,
                    'updated_by' => '',
                    'created_at'=> now()
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
    public function show(string $id)
    {
        //

        if(Auth::check()){
            try {
                
                

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
    public function update(Request $request,string $id)
    {
        $data = $request->all();

        return $data;
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
                        'path_url' =>'https://lightgreen-pigeon-122992.hostingersite.com/storage/app/public/uploads/posts/' . Auth::user()->code . '/' . $folderuuid . '/' . $filename,
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
            }
    
            DB::commit(); // Commit transaction
    
            return response()->json([
                'success' => true,
                'message' => 'Successfully updated.',
                'uploadedFiles' => $uploadedFiles ?? [],
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
    public function destroy(string $id)
    {
        //
    }


    public function authpostshow(){
        if(Auth::check()){
            

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
