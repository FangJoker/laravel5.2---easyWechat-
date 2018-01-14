<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/


Route::any('/wechat', 'WechatController@serve');

Route::any('/test', 'WechatController@test');


Route::any('/paySuccess', 'WechatController@paySuccess');  //支付回调


Route::group(['middleware' => ['web', 'wechat.oauth']], function () {  //微信认证路由群
   
Route::any('/pay', 'WechatController@pay');          //支付页面
 
   
});




