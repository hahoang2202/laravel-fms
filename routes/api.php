<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

if (App::environment('production')) {
    URL::forceScheme('https');
}

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['guest'])->post('/register', 'UsersController@register');
Route::middleware(['guest'])->post('/login', 'UsersController@login');
Route::get('/searchAuction', 'AuctionsController@searchAuction');
Route::post('/forgotPassword', 'UsersController@forgotPassword');
Route::get('/auctions', 'AuctionsController@index');
Route::post('/logout', 'UsersController@logout');

// Make sure only authenticated users can bid and close auctions
Route::middleware('auth:api')->put('/auction/{id}', 'AuctionsController@bidAuction');
Route::middleware('auth:api')->put('/auction/{id}/close', 'AuctionsController@closeAuction');
Route::middleware('auth:api')->post('/auction', 'AuctionsController@store');
Route::middleware('auth:api')->get('/auctions/{email}', 'AuctionsController@userAuctions');

Route::middleware('auth:api')->get('/wallet', 'UserWalletController@getBalance');
Route::middleware('auth:api')->put('/wallet/deposit', 'UserWalletController@deposit');
Route::middleware('auth:api')->put('/wallet/withdraw', 'UserWalletController@withdraw');
Route::middleware('auth:api')->put('/wallet/transfer', 'UserWalletController@transfer');