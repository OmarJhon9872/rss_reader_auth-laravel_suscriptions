<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'rss_channel_id',
        'guid',
        'title',
        'description',
        'link',
        'keywords',
    ];

    ################################################################
    /* Scopes */
    ################################################################
    /* Attributes */
    ################################################################
    /* Relaciones */
    ################################################################
    public function rss_channel(){
        return $this->belongsTo(RssChannel::class);
    }

    public function item_contents(){
        return $this->hasMany(ItemContent::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class, 'category_rss_channel');
    }
}
