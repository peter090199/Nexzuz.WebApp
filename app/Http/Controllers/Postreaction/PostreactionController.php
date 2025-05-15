<?php

namespace App\Http\Controllers\Postreaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommentPost;
use App\Models\Userprofile;
use App\Models\CommentReply;
use App\Models\User;
use Auth;
use DB;
use Illuminate\Support\Facades\Validator;

class PostreactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
    public function show(string $id)
    {
        //
        $data = DB::select('CALL sprocReactionDetails(?)',[
            $id
        ]);

        $result = ["count"=>count($data),
                   "reaction"=>$data];
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
    public function update(Request $request, string $id)
    {
        //
        if (Auth::check()) {
            $data = DB::select('CALL sprocReactionSave(?, ?, ?)', [
                Auth::user()->code,
                $id,
                $request->reaction 
            ]);
        
   
            $messageParts = explode(';', $data[0]->result);
            $statusCode = trim($messageParts[0]);
            $message = trim($messageParts[1]);
            if ($statusCode == '1') {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 400);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Authenticated',
        ]);

     
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
