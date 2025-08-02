<?php

namespace App\Http\Controllers\CV;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CV\DAL\UserLanguages;
use Illuminate\Support\Facades\Auth;

class UserLanguage extends Controller
{
    protected $dal;

    public function __construct(UserLanguages $dal)
    {
        $this->dal = $dal;
    }

    // public function savelanguage(Request $request)
    // {
    //     return $this->languageDAL->savelanguage($request);
    // }
    public function saveLanguage(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|string|max:255',
        ]);

        $currentUserCode = Auth::user()->code;

        $transNo = DB::table('usercapabilities')
            ->where('code', $currentUserCode)
            ->max('transNo');

        $newTrans = $transNo ? $transNo + 1 : 1;

        $id = $this->dal->insertCapability(
            $currentUserCode,
            $newTrans,
            $validated['language']
        );

        return response()->json([
            'success' => true,
            'message' => 'Language saved successfully.',
            'id' => $id,
        ]);
    }

    // public function saveLanguage($data)
    // {
    //     $maxTransNo = $this->dal->getMaxTransNo();
    //     $newTransNo = empty($maxTransNo) ? 1 : $maxTransNo + 1;

    //     $id = $this->dal->insertCapability(
    //         $data['code'],
    //         $newTransNo,
    //         $data['language']
    //     );

    //     return $id;
    // } 
}
