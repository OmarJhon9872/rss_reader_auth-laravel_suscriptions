<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'field',
        'value',
        'name',
        'showField',
    ];

    ################################################################
    /* Scopes */
    ################################################################
    /* Attributes */
    ################################################################
    /* Relaciones */
    ################################################################
}
