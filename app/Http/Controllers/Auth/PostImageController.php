<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use Illuminate\Support\Str;
use DB;

class PostImageController extends Controller
{
    public function PostNexzus(Request $request)
    {
        $data = $request->all();
    
        $validator = Validator::make($data, [
            'posts.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate multiple files
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
    
            $folderuuid = Str::uuid();
            $codeuser = Auth::user()->code;
    
            $uploadedFiles = [];
            $filePath = null; // Default value in case no file is uploaded
    
            if ($request->hasFile('posts')) {
                foreach ($request->file('posts') as $file) {
                    $uuid = Str::uuid();
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs("uploads/posts/{$codeuser}/{$folderuuid}", $filename, 'public');
                    
                    // Ensure that the file path is being saved correctly
                    $uploadedFiles[] = [
                        'uuid' => $uuid,
                        'folderuuid' => $folderuuid,
                        'filename' => $filename,
                        'path' => asset('storage/' . $filePath), // Correct URL generation
                    ];
    
                    $photoUrl = asset(Storage::url($filePath));
                    // Insert the post record
                    DB::table('posts')->insert([
                        'posts_uuid' => $folderuuid,
                        'transNo' => $newtrans,
                        'posts_uuind' => $uuid,
                        'caption' => $data['caption'],
                        'post' => $filePath,
                        'status' => $data['status'],
                        'code' => $codeuser,
                        'created_by' => Auth::user()->fullname
                    ]);
                }
            }
    
            // If no files were uploaded, insert a post without an image
            if (!$request->hasFile('posts') || empty($filePath)) {
                $uuid = Str::uuid();
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
    
}
