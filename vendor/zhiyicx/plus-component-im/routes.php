<?php

use Illuminate\Support\Facades\Route;

// Created IM APIs.
Route::prefix('/api/v1/im')
    ->middleware('api')
    ->namespace('Zhiyi\\Component\\ZhiyiPlus\\PlusComponentIm\\Controllers')
    ->group(__DIR__.'/routes/api.php');

// Created IM APIs V2.
Route::prefix('/api/v2/im')
    ->middleware('api')
    ->namespace('Zhiyi\\Component\\ZhiyiPlus\\PlusComponentIm\\Controllers\\V2')
    ->group(__DIR__.'/routes/api_v2.php');

// Created IM manage routes.
Route::prefix('im/admin')
    ->middleware('web')
    ->namespace('Zhiyi\\Component\\ZhiyiPlus\\PlusComponentIm\\AdminControllers')
    ->group(__DIR__.'/routes/admin.php');
