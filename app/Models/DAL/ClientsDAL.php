<?php

namespace App\Models\DAL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;


class ClientsDAL extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $fillable = [
        'code',
        'fname',
        'lname',
        'mname',
        'fullname',
        'contact_no',
        'age',
        'email',
        'profession',
        'company',
        'industry',
        'companywebsite',
        'role_code',
        'designation',
        'h1_fname',
        'h1_lname',
        'h1_mname',
        'h1_fullname',
        'h1_contact_no',
        'h1_email',
        'h1_address1',
        'h1_address2',
        'h1_city',
        'h1_province',
        'h1_postal_code',
        'h1_companycode',
        'h1_rolecode',
        'h1_designation',
        'created_by',
        'updated_by'
    ];

   
    public function getListClients()
    {
      return DB::table('resources')
        ->leftJoin('userprofiles', 'resources.code', '=', 'userprofiles.code')
        ->leftJoin('users', 'resources.code', '=', 'users.code')
        ->select(
            'userprofiles.photo_pic',
            'resources.fullname',
            'resources.profession',
            'resources.company',
            'resources.industry',
            'users.code',
            'users.is_online'
        )
        ->where('users.status', 'A')
        ->get();
    }






}
