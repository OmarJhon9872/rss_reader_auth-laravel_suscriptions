<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
    protected static function booted(){

        $rol_usuario = auth()->user()->role->desc_role->id;
        /*Si el usuario es cliente, podra ver all lo que le pertenezca*/
        if($rol_usuario == 1){

            $id_empleados = auth()->user()->employees->pluck('user_id')->toArray();

            $canales_empleados = RssChannel::whereIn('user_id', $id_empleados)->pluck('id')->toArray();

            static::addGlobalScope('item_propios_cliente', function (Builder $builder)use($canales_empleados) {
                $builder->whereIn('rss_channel_id', $canales_empleados);
            });
        }
        /*Si el usuario es investigador o analista, podra ver all lo que le pertenezca*/
        elseif($rol_usuario == 2 or $rol_usuario == 3){

            $id_empleados = auth()->user()->my_boss->boss_user->employees->pluck('user_id')->toArray();

            $canales_empleados = RssChannel::whereIn('user_id', $id_empleados)->pluck('id')->toArray();

            static::addGlobalScope('item_propios_analista_investigador', function (Builder $builder)use($canales_empleados) {
                $builder->whereIn('rss_channel_id', $canales_empleados);
            });
        }
    }
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
