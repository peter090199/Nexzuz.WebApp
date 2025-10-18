<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'username',
        'fname',
        'lname',
        'mname',
        'contactno',
        'fullname',
        'email',
        'password',
        'status',
        'company',
        'code',
        'role_code',
        'is_online',
        'coverphoto'
    ];

   
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'code',
        'role_code',
        'status',
        'created_at',
        'updated_at'

    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_online' => 'boolean'
    ];

    
    /**
     * Save or update the user's cover photo
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string $coverPhotoUrl
     */

    public function saveCoverPhoto($file)
    {
        // Delete old cover photo if exists
        if ($this->cover_photo) {
            $oldPath = str_replace('/storage/', 'public/', $this->cover_photo);
            if (Storage::exists($oldPath)) {
                Storage::delete($oldPath);
            }
        }

        // Store new cover photo
        $uuid = \Illuminate\Support\Str::uuid();
        $folderPath = "uploads/{$this->id}/cover_photo/{$uuid}";
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs($folderPath, $fileName, 'public');
        $fullPath = '/storage/app/public/' . $filePath;

        // Save URL to database
        $this->cover_photo = Storage::url($fullPath);
        $this->save();

        return $this->cover_photo;
    }

}
