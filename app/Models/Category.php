<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'user_id',
        'description'
    ];


    ################################################################
    /* Scopes */
    ################################################################
    /* Attributes */
    ################################################################
    /* Relaciones */
    ################################################################
    public function items(){
        return $this->belongsToMany(Item::class, 'category_rss_channel');
    }

    public function rss_channels(){
        return $this->belongsToMany(RssChannel::class);
    }
}
