<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\Post;
use DB;
class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        return view('testuploads');
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
                $transNo = UserProfile::max('transNo');
                $newtrans = empty($transNo) ? 1 : $transNo + 1;
            
                $uploadedFiles = [];
                if ($request->hasFile('posts')) {
                    foreach ($request->file('posts') as $file) {
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs('uploads', $filename, 'public');
                        $uploadedFiles[] = [
                            'filename' => $filename,
                            'path' => asset('storage/' . $filePath),
                        ];
                    }
                }
            
                return response()->json([
                    'caption' => $request->input('caption', ''), 
                    'status' => $request->input('status', 0),
                    'attachment' => $uploadedFiles,
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
            try {
                DB::beginTransaction();
                
                $data = $request->all();
                // $validator = Validator::make($data, [
                //     'posts' => 'required|file|image|max:3000',
                // ]);
                // if ($validator->fails()) {
                //     return response()->json([
                //         'success' => false,
                //         'message' => $validator->errors()->all(),
                //     ]);
                // }
                $userCode = Auth::user()->code;
                $folderPath = "uploads/{$userCode}/posts"; 
                $posts = [];
                for ($i = 0; $i < count($data['Post']); $i++) {

                    // $file = $request->file('file.$i');
                    // $fileName = time() . '.' . $file->getClientOriginalExtension();
                    // $photoPath = $file->storeAs($folderPath, $fileName, 'public');
                    // $photoUrl = asset(Storage::url($photoPath));

                    $posts[] = [
                        'transNo' => $data['Post']['transNo'][$i],
                        'caption' => $data['Post']['caption'][$i],
                        'post' => $data['Post']['post'][$i],
                        'status' => $data['Post']['status'][$i],
                        'code' => $data['Post']['code'][$i],
                        'created_by' => Auth::user()->fullname,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            
                // Insert all posts in one query
                DB::table('posts')->insert($posts);
            
                return response()->json(['message' => 'Posts inserted successfully']);

            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error occurred: ' . $th->getMessage(),
               ], 500);
            }
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
