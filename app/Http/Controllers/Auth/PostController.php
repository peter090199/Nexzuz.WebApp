<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\Resource;
use Illuminate\Support\Str;
use DB;
class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
            // if(Auth::check()){
            //     try {
            //         $requestedCode = $request->code;
            //         $authCode = Auth::user()->code;
                    
            //         $result = [];
                    
            //         $posts = Post::where(function ($query) use ($requestedCode, $authCode) {
            //             if ($requestedCode) {
            //                 // Scenario 1: Viewing a specific code profile
            //                 $query->where('code', $requestedCode)
            //                       ->where(function ($subQuery) use ($requestedCode, $authCode) {
            //                           $subQuery->where('status', 1); // Public posts
                    
            //                           // Allow private posts if visiting own profile
            //                           if ($requestedCode == $authCode) {
            //                               $subQuery->orWhere('status', 0);
            //                           }
            //                       });
            //             } else {
            //                 // Scenario 2: Viewing own profile
            //                 $query->where('code', $authCode)
            //                       ->where(function ($subQuery) {
            //                           $subQuery->where('status', 1) // Public posts
            //                                    ->orWhere('status', 0); // Private posts
            //                       });
            //             }
            //         })
            //         ->get();
                    
            //         // Format output
            //         foreach ($posts as $post) {
            //             if (!isset($result[$post->posts_uuid])) {
            //                 $result[$post->posts_uuid] = [
            //                     "Fullname" => $post->created_by,
            //                     "status" => $post->status,
            //                     "caption" => $post->caption,
            //                     "posts_uuind"=>$post->posts_uuind,
            //                     "posts" => []
            //                 ];
            //             }
                    
            //             $result[$post->posts_uuid]['posts'][] = [
            //                 "posts_uuind" => $post->posts_uuind,
            //                 "post" => $post->post,
            //                 "transNo" => $post->transNo
            //             ];
            //         }
                    
            //         return response()->json(array_values($result));
                    
                

            //     } catch (\Throwable $th) {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'An error occurred: ' . $th->getMessage(),
            //         ]);
            //     }
                
            // }
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
        
            if ($request->hasFile('posts')) {
                foreach ($request->file('posts') as $file) {
                    $uuid = Str::uuid();
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $filePath = "uploads/posts/${codeuser}/{$folderuuid}/{$filename}";
                    $file->storeAs("uploads/posts/${codeuser}/{$folderuuid}", $filename, 'public');
        
                    $uploadedFiles[] = [
                        'uuid' => $uuid,
                        'folderuuid' => $folderuuid, // Fix key name
                        'filename' => $filename,
                        'path' => asset('storage/' . $filePath),
                    ];
        
                    DB::table('posts')->insert([
                        'posts_uuid' => $folderuuid,
                        'transNo' => $newtrans,
                        'posts_uuind' => $uuid,
                        'caption' => $data['caption'],
                        'post' => "https://lightgreen-pigeon-122992.hostingersite.com/".asset('storage/' . $filePath), // Use asset()
                        'status' => $data['status'],
                        'code' => $codeuser,
                        'created_by' => Auth::user()->fullname
                    ]);
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
            // return response()->json([
            //     'caption' => $request->input('caption', ''),
            //     'status' => $request->input('status', 0),
            //     'attachment' => $uploadedFiles,
            // ]);
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
    public function update(Request $request, string $id)
    {
        //

        
        if(Auth::check()){
            
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


}
