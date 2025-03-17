<?php

namespace App\Http\Controllers\Lookup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Resource;
use DB;
use Auth;

class LookupController extends Controller
{
    //

    public function userlists(Request $request) {
        // Check if the search term exists (not empty or null)
        if (empty($request->s1)) {
            // Return an empty response or handle as needed when no search term is provided
            return response()->json([]);
        }
        // Proceed with the search if there's a value in $s1
        $data = DB::select('SELECT 
            fname AS fname,
            lname AS lname,
            code AS code,
            CASE 
                WHEN EXISTS (SELECT 1 FROM userprofiles WHERE code = resources.code LIMIT 1) 
                THEN (SELECT photo_pic FROM userprofiles WHERE code = resources.code LIMIT 1)
                ELSE NULL 
            END AS profile_pic
        FROM resources 
        WHERE fname LIKE ? OR lname LIKE ?', ['%' . $request->s1 . '%', '%' . $request->s1 . '%']);
    
        return response()->json($data);
    }

    



}
