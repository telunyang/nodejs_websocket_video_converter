<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/


//youtube 影片下載器
Route::get('/youtube', 				'YoutubeController@index'); //主頁面
Route::get('/youtube/getFiles', 	'YoutubeController@getFiles'); //取得檔案列表
Route::post('/youtube/deleteFile',  'YoutubeController@deleteFile'); //刪除影片
