<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index')->name('index');
#Route::get('/rss', 'HomeController@rss')->name('rss');

Route::controller('HomeController')->prefix('home/')->name('home.')->group(function() {

    Route::post('/actualizarCategoria/{categoria}', 'actualizarCategoria')->name('actualizarCategoria');
    Route::post('/guardarCategoria', 'guardarCategoria')->name('guardarCategoria');

    Route::post('/verifica_nombre_categoria', 'verifica_nombre_categoria')->name('verifica_nombre_categoria');

    Route::get('/buscar', 'buscar')->name('buscar');
    Route::post('/agregar_rss', 'agregar_rss')->name('agregar_rss');
    Route::post('/guardar_mostrar_campos', 'guardar_mostrar_campos')->name('guardar_mostrar_campos');

    Route::post('/categorizar_elemento/{id?}/{tipo_elemento?}', 'categorizar_elemento')->name('categorizar_elemento');
    Route::post('/eliminar_elemento/{id?}/{tipo_elemento?}', 'eliminar_elemento')->name('eliminar_elemento');

    Route::get('/canales/{canal?}', 'canales')->name('canales');
    Route::get('/categorias/{categoria?}', 'categorias')->name('categorias');

});

if(config('auth.need_auth_rss')){
    Auth::routes(['reset' => false]);
}
