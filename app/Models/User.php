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

    private const PER_PAGE_SEARCH = 9;
    private const PER_PAGE_SIMPLE_PAGINATION = 15;

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

    public function actions(){
        return $this->hasMany(ItemAction::class);
    }#ok

    /*Action numero 1 featured*/
    public function getItemsFeaturedAttribute(){
        $id_items = $this->actions->where('action', 1)->pluck('item_id')->toArray();

        return Item::whereIn('id', $id_items)
                        ->latest()
                        ->with(['rss_channel', 'categories', 'item_contents']);
                        /*->simplePaginate($this::PER_PAGE_SIMPLE_PAGINATION);*/
    }#ok

    /*Action numero 2 vistos*/
    public function getItemsLookedAttribute(){
        $id_items = $this->actions->where('action', 2)->pluck('item_id')->toArray();
        return Item::whereIn('id', $id_items)
                        ->latest()
                        ->with(['rss_channel', 'categories', 'item_contents']);
                        /*->simplePaginate($this::PER_PAGE_SIMPLE_PAGINATION);*/
    }#ok
}
