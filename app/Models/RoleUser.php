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
        'licenses',
        'role_id',
        'user_id'
    ];

    ################################################################
    /* Scopes */
    ################################################################
    /* Attributes */
    public function getItemsCountAttribute(){
        $id_channels = $this->rss_channels->pluck('id')->toArray();
        return Item::whereIn('rss_channel_id', $id_channels)->count();
    }
    ################################################################
    /* Relaciones */
    ################################################################
    /*Si es jefe*/
    public function boss_user(){
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }#ok

    public function user(){
        return $this->belongsTo(User::class);
    }#ok

    public function desc_role(){
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }#ok

    public function rss_channels(){
        return $this->hasMany(RssChannel::class, 'user_id', 'id');
    }#ok

}
