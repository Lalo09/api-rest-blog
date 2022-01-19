<?php
//Cargar clases
use App\Http\Middleware\ApiAuthMiddleware;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test/{name?}',function($name){
    $text = '<h2>Text from a route</h2>';
    $text .= 'Nombre: '.$name;
    return $text;
});

Route::get('/testorm','Controller@testOrm');

//Rutas del api

    /**
     * Metodos HTTP comunes
     * GET: Conseguir datos
     * POST: Guardar datos o recursos o hacer logica
     * PUT: Actualizar datos
     * DELETE: Eliminar datos o recursos
     */
    
    //Rutas de prueba
    Route::get('/usuario/prueba','UserController@pruebas');
    Route::get('/categoria/prueba','CategoryController@pruebas');
    Route::get('/posts/prueba','PostController@pruebas');
    
    //Rutas del controlador de usuarios
    Route::post('/api/register','UserController@register');
    Route::post('/api/login','UserController@login');
    Route::put('/api/user/update','UserController@update');
    Route::post('/api/user/upload','UserController@upload')->middleware(ApiAuthMiddleware::class);
    Route::get('/api/user/avatar/{filename}','UserController@getImage');
    Route::get('/api/user/detail/{id}','UserController@detail');
    
    //Rutas del controlador de categorias
    Route::resource('/api/category','CategoryController'); //Poner los metodos automaticamente, para verlos usar: php artisan route:list  
    
    //Ruta del controlador de entradas (posts)
    Route::resource('/api/post','PostController');
    Route::post('/api/post/upload','PostController@upload')->middleware(ApiAuthMiddleware::class);
    Route::get('/api/post/image/{filename}','PostController@getImage');
    Route::get('/api/post/category/{id}','PostController@getPostsByCategory');
    Route::get('/api/post/user/{id}','PostController@getPostsByUser');