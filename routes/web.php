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

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/master',function(){
    return view("master");
})->middleware("auth")->name('master');
Route::get('/master/{path}',function(){
    return view("master");
})->where("path","([A-z\d\/_.]+)?")->middleware("auth");