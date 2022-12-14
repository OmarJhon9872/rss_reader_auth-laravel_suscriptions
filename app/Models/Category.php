<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
    protected static function booted(){

        $rol_usuario = auth()->user()->role->desc_role->id;
        /*Si el usuario es cliente, podra ver all lo que le pertenezca*/
        if($rol_usuario == 1){

            $id_empleados = auth()->user()->employees->pluck('user_id')->toArray();
            $id_empleados[] = auth()->id();

            static::addGlobalScope('item_propios_cliente', function (Builder $builder)use($id_empleados) {
                $builder->whereIn('user_id', $id_empleados);
            });
        }
        /*Si el usuario es investigador o analista, podra ver all lo que le pertenezca*/
        elseif($rol_usuario == 2 or $rol_usuario == 3){

            $id_empleados = auth()->user()->my_boss->boss_user->employees->pluck('user_id')->toArray();

            static::addGlobalScope('item_propios_analista_investigador', function (Builder $builder)use($id_empleados) {
                $builder->whereIn('user_id', $id_empleados);
            });
        }
    }
    ################################################################
    /* Attributes */
    ################################################################
    /* Relaciones */
    ################################################################
    public function getItemsAttribute(){
        $item_ids = $this->cat_rss_channel_tble->pluck('item_id')->toArray();

        return Item::whereIn('id', $item_ids)->get();
    }

    public function cat_rss_channel_tble(){
        return $this->hasMany(CategoryRssChannel::class);
    }

    public function rss_channels(){
        return $this->belongsToMany(RssChannel::class);
    }

    public function subcategories(){
        return $this->hasMany(Category::class);
    }
}
