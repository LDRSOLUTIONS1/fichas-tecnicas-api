<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    const ADMIN = 1;
    const VIEWER = 2;
    const DIRECTOR = 3;
    const GERENTE = 4;
    const SUPER_ADMIN = 5;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'second_last_name',
        'email',
        'phone',
        'employee_number',
        'password',
        'user_type',
        'position',
        'url',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'password',
    ];

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]);
    }
}
