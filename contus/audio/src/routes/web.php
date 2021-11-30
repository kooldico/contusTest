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
Route::prefix('medias/web/v2')->namespace('Contus\Audio\Http\Controllers\Frontend')->group(function () {
    Route::group(['middleware' => []], function () {
        Route::get('radio', 'AudioBaseController@getRadio');
    });
});
