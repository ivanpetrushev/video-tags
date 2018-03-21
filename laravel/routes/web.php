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

Route::resource('directory', 'DirectoryController', ['only' => ['index', 'store', 'update', 'destroy']]);
//Route::get('/directory', 'DirectoryController@listing');
//Route::post('/directory', 'DirectoryController@add');
//Route::delete('/directory', 'DirectoryController@delete');

Route::get('/', function () {
    return view('welcome');
});
