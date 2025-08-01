<?php

namespace App\Models\CV\DAL;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserLanguages extends Model
{
   
    use HasApiTokens, HasFactory, Notifiable;

     public function insertCapability($code, $transNo, $language)
    {
        return DB::table('usercapabilities')->insertGetId([
            'code' => $code,
            'transNo' => $transNo,
            'language' => $language,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getMaxTransNo()
    {
        return DB::table('userprofiles')->max('transNo'); 
    }

    // public function savelanguage()
    // {
    //     $currentUserCode = Auth::user()->code;

    // }
    // public function savelanguage($request)
    // {
    //     $data = $request->validate([
    //         'code' => 'required|string',
    //         'transNo' => 'required|integer',
    //         'language' => 'required|string|max:255',
    //     ]);

    //     $transNo = UserProfile::max('transNo');
    //     $newTrans = empty($transNo) ? 1 : $transNo + 1;

    //     $id = DB::table('usercapabilities')->insertGetId([
    //         'code' => $currentUserCode,
    //         'transNo' => $data['transNo'],
    //         'language' => $data['language'],
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Inserted language successfully',
    //         'id' => $id
    //     ]);
    // }
}
