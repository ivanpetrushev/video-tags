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

Route::post('/geojson', 'PlacesController@geojson');
Route::get('/categories', 'PlacesController@categories');
Route::get('/gpx', 'PlacesController@gpx');

Route::get('/', function () {
    return view('welcome');
});
