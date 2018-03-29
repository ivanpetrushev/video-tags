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
Route::resource('tag', 'TagController', ['only' => ['index']]);
Route::get('/directory/tree', 'DirectoryController@tree');
Route::get('/file/tags', 'FileController@tags');
Route::delete('/file/remove_tag', 'FileController@removeTag');
Route::put('/file/stop_tag', 'FileController@stopTag');
Route::put('/file/save_tag', 'FileController@saveTag');
Route::post('/file/new_tag', 'FileController@newTag');
Route::post('/file/copy_tag', 'FileController@copyTag');
Route::get('/file/export/{id}', 'FileController@export');
Route::post('/file/import', 'FileController@import');

Route::get('/', function () {
    return view('welcome');
});
