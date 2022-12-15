<?php
#################### RETURN ALL THE NEWS WHEN THE VIEW HEADER IS CALLED#################
namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;


class FeaturedAndViewedItemsProvider extends ServiceProvider
{


    public function register()
    {
        //
    }

    public function boot()
    {
        #Modificar de acuerdo a lo requerido
        View::composer(['complements.item'], function($view){

            $itemsFeatured = auth()->user()->itemsFeatured->get()->pluck('id')->toArray();
            $itemsLooked = auth()->user()->itemsLooked->get()->pluck('id')->toArray();;

            $view->with([
                'itemsFeatured' => $itemsFeatured,
                'itemsLooked'   => $itemsLooked
            ]);
        });

    }


}
