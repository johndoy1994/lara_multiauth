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


Route::group(['middleware' => ['web']], function () {
    
    // //Login Routes...
    // Route::get('/admin/login','Backend\AdminAuth\AuthController@showLoginForm');
    // Route::post('/admin/login','Backend\AdminAuth\AuthController@login');
    // Route::get('/admin/logout','Backend\AdminAuth\AuthController@logout');

    // // Registration Routes...
    // Route::get('admin/register', 'Backend\AdminAuth\AuthController@showRegistrationForm');
    // Route::post('admin/register', 'Backend\AdminAuth\AuthController@register');

    // Route::get('/admin', 'AdminController@index');



    //Login Routes...
    Route::get('/admin/login','Backend\AdminAuth\AuthController@getLogin')->name('getAdminLoginPage');
    Route::post('/admin/login','Backend\AdminAuth\AuthController@postLogin')->name('postAdminLoginPage');
    Route::get('/admin/logout','Backend\AdminAuth\AuthController@getLogout')->name('getAdminLogOutPage');

    // Registration Routes...
    Route::get('admin/register', 'Backend\AdminAuth\AuthController@showRegistrationForm')->name('getAdminRegisterPage');
    Route::post('admin/register', 'Backend\AdminAuth\AuthController@register')->name('postAdminRegisterPage');

    Route::get('/admin', 'AdminController@index');



    Route::group(['prefix'=>'admin', 'namespace'=>'Backend'],function(){
    	/*=== Home routes ==== */		
		Route::get('/','DashboardController@index')->name("dashboard");
        Route::get('/dashboard','DashboardController@index')->name("admindashboard");
		/*=== Home routes End ==== */


		Route::get('get-ajax-model/','ModelDetailsController@getAjaxModel')->name("getAdminModelByAjax");
    });


});


// public routes
Route::get('/recordperpage',['uses'=>"CommonController@getRecordPerPage","as"=>'api-public-recordPerPage']);
Route::get('/change-status',['uses'=>"CommonController@getChangeStatus","as"=>'api-public-change-status']);
