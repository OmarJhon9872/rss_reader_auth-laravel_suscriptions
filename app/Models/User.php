<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'last1',
        'last2',
        'email',
        'password',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    ################################################################
    /* Scopes */
    ################################################################
    /* Attributes */
    public function getRoleUserAttribute(){
        return $this->role->description;
    }
    ################################################################
    /* Relaciones */
    ################################################################
    public function employees(){
        return $this->hasMany(RoleUser::class, 'owner_id', 'id');
    }#ok

    public function my_boss(){
        return $this->hasOne(RoleUser::class);
    }#ok
    public function role(){
        return $this->hasOne(RoleUser::class);
    }#ok
    public function rss_channels(){
        return $this->hasMany(RssChannel::class);
    }#ok
}
