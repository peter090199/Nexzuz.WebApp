<?php

namespace App\Models\CV\DAL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class UserLanguages extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usercapabilities'; // Optional: specify table name
    public $timestamps = true;

    protected $fillable = [
        'code',
        'transNo',
        'language',
    ];

    /**
     * Insert a new language capability for a user.
     */
    public function insertCapability($code, $transNo, $language)
    {
        $record = self::create([
            'code' => $code,
            'transNo' => $transNo,
            'language' => $language,
        ]);

        return $record->id; 
    }


    public function getMaxTransNo()
    {
        return DB::table('userprofiles')->max('transNo');
    }

   
}
