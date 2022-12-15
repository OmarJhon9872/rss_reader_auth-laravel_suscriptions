<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemAction extends Model
{
    use HasFactory;

    /*Action numero 1 featured*/
    /*Action numero 2 vistos*/
    protected $fillable = [
        'user_id',
        'item_id',
        'action'
    ];

    ################################################################
    /* Scopes */
    ################################################################
    /* Attributes */
    ################################################################
    /* Relaciones */
    ################################################################
    public function item(){
        return $this->belongsTo(Item::class);
    }
}
