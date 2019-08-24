<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'Auth\LoginController@logout');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Categories
    Route::resource('categories', 'Categories\CategoriesController');

    // Sharings
    Route::resource('sharings', 'Sharings\SharingsController');
    //Route::patch('sharings/{sharing}/transitions/{transition}', 'Sharings\SharingsController@transition')->name('sharings.transition');
    //Route::get('sharing-requests', 'Sharings\SharingsController@requestToManage')->name('sharing.requests');

    Route::post('sharings/{sharing}/join', 'Sharings\SharingsController@join')->name('sharings.join');
    Route::patch('sharings/{sharing}/left', 'Sharings\SharingsController@left')->name('sharings.left');
    Route::patch('sharings/{sharing}/transitions/{transition}', 'Sharings\SharingsController@transition')->name('sharings.transition');
    Route::patch('sharings/{sharing}/user/{user}/transition-user/{transition}', 'Sharings\SharingsController@transitionUser')->name('sharings.user.transition');

    // Settings
    Route::patch('settings/profile', 'Settings\ProfileController@update');
    Route::patch('settings/password', 'Settings\PasswordController@update');
});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');

    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');

    Route::post('email/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify');
    Route::post('email/resend', 'Auth\VerificationController@resend');

    Route::post('oauth/{driver}', 'Auth\OAuthController@redirectToProvider');
    Route::get('oauth/{driver}/callback', 'Auth\OAuthController@handleProviderCallback')->name('oauth.callback');
});
