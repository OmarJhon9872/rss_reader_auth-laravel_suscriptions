<?php

use Illuminate\Support\Facades\Route;

/*\DB::listen(function ($query){
    echo "<pre>".$query->sql."</pre>";
});*/

Route::get('/', 'HomeController@index')->name('index');
#Route::get('/rss', 'HomeController@rss')->name('rss');
Route::post('/rss_masivo', 'PruebasController@rss_masivo')->name('rss_masivo');

Route::controller('HomeController')->prefix('home/')->name('home.')->group(function() {

    Route::delete('/borrar_usuario/{usuario}', 'borrar_usuario')->name('borrar_usuario');

    Route::post('/cambiar_accion_boton/{tipo?}', 'cambiar_accion_boton')->name('cambiar_accion_boton');

    Route::post('/cambiar_analista', 'cambiar_analista')->name('cambiar_analista');

    Route::post('/crear_usuario', 'crear_usuario')->name('crear_usuario');
    Route::post('/cambiar_clave_usuario/{usuario}', 'cambiar_clave_usuario')->name('cambiar_clave_usuario');

    Route::get('/usuarios', 'usuarios_cliente')->name('usuarios_cliente');

    Route::post('/actualizarCategoria/{categoria}', 'actualizarCategoria')->name('actualizarCategoria');
    Route::post('/guardarCategoria', 'guardarCategoria')->name('guardarCategoria');

    Route::post('/verifica_nombre_categoria', 'verifica_nombre_categoria')->name('verifica_nombre_categoria');

    Route::get('/buscar', 'buscar')->name('buscar');
    Route::post('/verifica_tiene_items/{usuario?}', 'verifica_tiene_items')->name('verifica_tiene_items');

    Route::post('/agregar_rss', 'agregar_rss')->name('agregar_rss');
    Route::post('/guardar_mostrar_campos', 'guardar_mostrar_campos')->name('guardar_mostrar_campos');

    Route::post('/categorizar_elemento/{id?}/{tipo_elemento?}', 'categorizar_elemento')->name('categorizar_elemento');
    Route::post('/eliminar_elemento/{id?}/{tipo_elemento?}', 'eliminar_elemento')->name('eliminar_elemento');

    Route::get('/canales/{canal?}', 'canales')->name('canales');
    Route::get('/categorias/{categoria?}', 'categorias')->name('categorias');

    Route::get('/favoritos', 'itemsFavoritos')->name('favoritos');
    Route::get('/vistos', 'itemsVistos')->name('vistos');

});

if(config('auth.need_auth_rss')){
    Auth::routes(['reset' => false]);
}
