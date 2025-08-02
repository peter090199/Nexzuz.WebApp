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
        return DB::table($this->table)->insertGetId([
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

   
}
