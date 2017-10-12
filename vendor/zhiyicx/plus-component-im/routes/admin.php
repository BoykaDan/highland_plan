<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'HomeController@show')->name('im:admin');
Route::patch('/', 'HomeController@update')
    ->middleware('auth:web')
    ->name('im.manage.request');

Route::get('/helps', 'HelperController@show')
    ->middleware('auth:web')
    ->name('im:admin-helper');
Route::post('/helps', 'HelperController@store')
    ->middleware('auth:web')
    ->name('im:admin-helper-store');
Route::get('/helps/{uid}', 'HelperController@delete')
    ->middleware('auth:web')
    ->name('im:admin-helper-delete');
