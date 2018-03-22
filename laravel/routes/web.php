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
Route::get('/directory/tree', 'DirectoryController@tree');

Route::get('/', function () {
    return view('welcome');
});
