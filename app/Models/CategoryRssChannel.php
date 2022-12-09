<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryRssChannel extends Model
{
    use HasFactory;

    protected $table = 'category_rss_channel';

    public function categories(){
        return $this->belongsToMany(Category::class);
    }

    public function rss_channels(){
        return $this->belongsToMany(Category::class);
    }
}
