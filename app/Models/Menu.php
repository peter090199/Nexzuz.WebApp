<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Menu extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'transNo',
        'desc_code',
        'description',
        'icon',
        'class',
        'routes',
        'sort',
        'status',
        'created_by',
        'updated_by'
    ];

    public static function updateById($id, $data, $updatedBy = 'system')
    {
        $menu = self::find($id);

        if (!$menu) {
            return null;
        }

        $menu->fill($data);
        $menu->updated_by = $updatedBy;
        $menu->save();

        return $menu;
    }

    
}
