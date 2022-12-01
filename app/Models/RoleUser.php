<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    use HasFactory;

    protected $table = 'role_user';

    protected $fillable = [
        'owner_id',
        'role_id',
        'user_id'
    ];

    ################################################################
    /* Scopes */
    ################################################################
    /* Attributes */
    ################################################################
    /* Relaciones */
    ################################################################
    /*Si es jefe*/
    public function boss_user(){
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }#ok
    public function employees(){
        return $this->hasMany(RoleUser::class, 'user_id', 'owner_id');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }#ok

}
