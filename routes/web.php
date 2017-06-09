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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', 'TestController@test');
Route::post('/users/register', 'UsersController@register');

Route::group(['prefix' => 'api/v1', 'middleware' => 'auth:api'], function () {
    Route::get('me', 'UsersController@me');
    Route::get('users/lawyers', 'UsersController@lawyers');
    // note: put resource routes at the end of the list
    Route::resource('appointments', 'AppointmentsController', ['only' => ['index', 'store', 'update', 'destroy', 'show']]);
});


Auth::routes();

#GET ALL ROUTES DISPLAYED ON THE WEB:
Route::get('routes', function() {
    $routeCollection = Route::getRoutes();

    echo "<table style='width:100%'>";
    echo "<tr>";
    echo "<td width='10%'><h4>HTTP Method</h4></td>";
    echo "<td width='10%'><h4>Route</h4></td>";
    echo "<td width='80%'><h4>Corresponding Action</h4></td>";
    echo "</tr>";
    foreach ($routeCollection as $value) {
        echo "<tr>";
        echo "<td>" . $value->methods()[0] . "</td>";
        echo "<td>" . $value->uri() . "</td>";
        echo "<td>" . $value->getActionName() . "</td>";
        echo "</tr>";
    }
    echo "</table>";
});
#END