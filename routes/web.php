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

//    Route::group(['middleware'=> 'default_lang'],function () {
//
//        Route::get('/', 'HomeController@home')->name('home');
//        Route::get('login', 'AuthController@login')->name('login');
//        Route::get('sign-up', 'AuthController@signUp')->name('signUp');
//        Route::post('sign-up-process', 'AuthController@signUpProcess')->name('signUpProcess');
//        Route::post('login-process', 'AuthController@loginProcess')->name('loginProcess');
//        Route::get('forgot-password', 'AuthController@forgotPassword')->name('forgotPassword');
//        Route::get('verify-email', 'AuthController@verifyEmailPost')->name('verifyWeb');
//        Route::get('reset-password', 'AuthController@resetPasswordPage')->name('resetPasswordPage');
//        Route::post('send-forgot-mail', 'AuthController@sendForgotMail')->name('sendForgotMail');
//        Route::post('reset-password-save-process', 'AuthController@resetPasswordSave')->name('resetPasswordSave');
//        Route::get('/g2f-checked', 'AuthController@g2fChecked')->name('g2fChecked');
//        Route::post('/g2f-verify', 'AuthController@g2fVerify')->name('g2fVerify');
//
//        Route::get('page/{id}/{key}', 'HomeController@getCustomPage')->name('getCustomPage');
//        Route::post('contact-us-email-details', 'admin\SettingsController@getDescriptionByID')->name('getDescriptionByID');
//        Route::get('contact-us', 'admin\SettingsController@contactEmailList')->name('contactEmailList');
//        Route::post('contact-us', 'HomeController@contactUs')->name('ContactUs');
//
//    // Referral Registration
//        Route::get('referral-reg', 'HomeController@signup')->name('referral.registration');
//    });

Route::group(['middleware'=> 'default_lang'], function () {

    Route::get('/', function () {
        return redirect()->away('https://svscoin.org');
    })->name('home');

    Route::get('login', function () {
        return redirect()->away('https://svscoin.org');
    })->name('login');

    Route::get('sign-up', function () {
        return redirect()->away('https://svscoin.org');
    })->name('signUp');

    Route::post('sign-up-process', function () {
        return redirect()->away('https://svscoin.org');
    })->name('signUpProcess');

    Route::post('login-process', function () {
        return redirect()->away('https://svscoin.org');
    })->name('loginProcess');

    Route::get('forgot-password', function () {
        return redirect()->away('https://svscoin.org');
    })->name('forgotPassword');

    Route::get('verify-email', function () {
        return redirect()->away('https://svscoin.org');
    })->name('verifyWeb');

    Route::get('reset-password', function () {
        return redirect()->away('https://svscoin.org');
    })->name('resetPasswordPage');

    Route::post('send-forgot-mail', function () {
        return redirect()->away('https://svscoin.org');
    })->name('sendForgotMail');

    Route::post('reset-password-save-process', function () {
        return redirect()->away('https://svscoin.org');
    })->name('resetPasswordSave');

    Route::get('/g2f-checked', function () {
        return redirect()->away('https://svscoin.org');
    })->name('g2fChecked');

    Route::post('/g2f-verify', function () {
        return redirect()->away('https://svscoin.org');
    })->name('g2fVerify');

    Route::get('page/{id}/{key}', function () {
        return redirect()->away('https://svscoin.org');
    })->name('getCustomPage');

    Route::post('contact-us-email-details', function () {
        return redirect()->away('https://svscoin.org');
    })->name('getDescriptionByID');

    Route::get('contact-us', function () {
        return redirect()->away('https://svscoin.org');
    })->name('contactEmailList');

    Route::post('contact-us', function () {
        return redirect()->away('https://svscoin.org');
    })->name('ContactUs');

    // Referral Registration
    Route::get('referral-reg', function () {
        return redirect()->away('https://svscoin.org');
    })->name('referral.registration');
});


//    require base_path('routes/link/admin.php');
//    require base_path('routes/link/user.php');

    Route::group(['middleware' => ['auth']], function () {
        Route::get('logout', 'AuthController@logOut')->name('logOut');
    });

