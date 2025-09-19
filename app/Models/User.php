<?php

namespace App\Models;


// ... diğer use ifadeleri
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Mass assignment için fillable alanları
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function surveys()
    {
        return $this->hasMany(Survey::class, 'created_by');
    }
}
