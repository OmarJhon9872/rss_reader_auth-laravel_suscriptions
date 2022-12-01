<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RssChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'channel_url',
        'channel_title',
        'channel_description',
        'category_id'
    ];

    ################################################################
    /* Scopes */
    ################################################################
    /* Attributes */
    ################################################################
    /* Relaciones */
    ################################################################
    public function items(){
        return $this->hasMany(Item::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class);
    }
}
