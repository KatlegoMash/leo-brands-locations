<?php

use Illuminate\Support\Facades\Route;

$api = app('Jetwaves\LaravelImplicitRouter\Router');
$api->controller('/brands', '\App\Http\Controllers\BrandController');
$api->controller('locations', '\App\Http\Controllers\BrandLocationController');
$api->controller('widget-categories', '\App\Http\Controllers\WidgetController');
$api->controller('name-tags', '\App\Http\Controllers\NameTagsController');

Route::post('/', function () {
    redirect('https://google.com');
});
Route::get('/', function () {
    redirect('/brands/index');
});

$api->controller('/seller', '\App\Http\Controllers\SellerController');
Route::get('/seller/getReturnJsonObject', 'SellerController@getReturnJsonObject');//the implicit routing is making the method throw a page error

//download json files
Route::get('download-json', 'SellerController@downloadJson')->name('download-json');
Route::get('download-selected-json', 'SellerController@downloadSelectedJson')->name('download-selected-json');
