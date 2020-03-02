<?php

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
use App\Http\Middleware\ApiAuthMiddleware;
Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/pruebas/animales','PruebasController@index');

Route::get('/pruebas/{nombre}',function($nombre){
    $texto="<h1>Nombre =</h1>";
    $texto.=" -".$nombre;
    return view('pruebas',array(
        'texto'=>$texto
    )); 
});
Route::get('test-orm','PruebasController@testOrm');
Route::get('pruebas-user','UserController@pruebas');
Route::get('pruebas-post','PostController@pruebas');
Route::get('pruebas-category','CategoryController@pruebas');
//Route::get('test-orm','PruebasController@testOrm');

//rutas de usuario
Route::post('/api/register','UserController@register');
Route::post('/api/login','UserController@login');
Route::put('/api/user/update','UserController@update');
Route::post('/api/user/upload','UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/api/user/avatar/{filename}','UserController@getImage');
Route::get('/api/user/detail/{id}','UserController@detail');


//rutas de categoria
Route::resource('/api/category','CategoryController');

//rutas de post
Route::resource('/api/post','PostController');
Route::post('/api/post/upload','PostController@upload');
Route::get('/api/post/image/{filename}','PostController@getImage');
Route::get('/api/post/user/{id}','PostController@postsByUser');
Route::get('/api/post/category/{id}','PostController@postsByCategory');
