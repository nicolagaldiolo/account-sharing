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
        return new \App\Http\Resources\User($request->user());
    });

    Route::patch('settings/complete-registration', 'Settings\ProfileController@completeRegistration');

    Route::group(['middleware' => 'RegistrationCompleted'], function () {

        // Categories
        Route::resource('categories', 'Categories\CategoriesController');

        // Sharings
        Route::resource('sharings', 'Sharings\SharingsController');
        //Route::patch('sharings/{sharing}/transitions/{transition}', 'Sharings\SharingsController@transition')->name('sharings.transition');
        //Route::get('sharing-requests', 'Sharings\SharingsController@requestToManage')->name('sharing.requests');

        Route::post('sharings/{sharing}/join', 'Sharings\SharingsController@join')->name('sharings.join');
        Route::patch('sharings/{sharing}/user/{user}/update', 'Sharings\SharingsController@update')->name('sharings.renewal.update');
        Route::patch('sharings/{sharing}/transitions/{transition?}', 'Sharings\SharingsController@transition')->name('sharings.transition');
        Route::patch('sharings/{sharing}/user/{user}/transition-user/{transition}', 'Sharings\SharingsController@transitionUser')->name('sharings.user.transition');
        Route::get('sharings/{sharing}/credentials/', 'Sharings\CredentialController@index')->name('sharings.credentials');
        Route::get('sharings/{sharing}/getcredentials/', 'Sharings\CredentialController@get')->name('sharings.getcredentials');
        Route::patch('sharings/{sharing}/credential/{recipient?}', 'Sharings\CredentialController@update')->name('sharings.credentials.update');
        Route::post('sharings/{sharing}/credential/{type}', 'Sharings\CredentialController@confirm')->name('sharings.credentials.confirm');
        Route::post('sharings/{sharing}/subscribe/', 'Sharings\SharingsController@subscribe')->name('sharings.credentials.subscribe');
        Route::post('sharings/{sharing}/restore/', 'Sharings\SharingsController@restore')->name('sharings.credentials.restore');

        // Chat
        Route::get('sharings/{sharing}/chats', 'Sharings\ChatsController@index')->name('sharings.chats');
        Route::post('sharings/{sharing}/chat/', 'Sharings\ChatsController@store')->name('sharings.chat.store');

        // Settings
        // Profile
        Route::patch('settings/profile', 'Settings\ProfileController@update');
        Route::patch('settings/profile-needed-info', 'Settings\ProfileController@neededInfo');
        Route::post('settings/verify-account', 'Settings\ProfileController@verifyAccount');
        Route::post('settings/bank-account', 'Settings\ProfileController@bankAccount');
        Route::patch('settings/password', 'Settings\PasswordController@update');

        // Transactions
        Route::get('settings/transactions', 'Settings\TransactionsController@index');

        // Balance
        Route::get('settings/balance', 'Settings\BalanceController@index');

        // Payment Methods
        Route::get('settings/paymentmethods', 'Stripe\PaymentMethodsController@index');
        Route::get('settings/getCustomer', 'Stripe\PaymentMethodsController@getCustomer');
        Route::get('settings/setupintent', 'Stripe\PaymentMethodsController@setupintent');
        Route::post('settings/paymentmethods', 'Stripe\PaymentMethodsController@store');
        Route::patch('settings/paymentmethods', 'Stripe\PaymentMethodsController@update');
        Route::delete('settings/paymentmethods', 'Stripe\PaymentMethodsController@destroy');
        Route::post('settings/paymentmethods', 'Stripe\PaymentMethodsController@store');

        // Refunds
        Route::post('settings/refunds', 'Settings\RefundsController@store');
    });
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
