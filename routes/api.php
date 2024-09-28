<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CodeMiddleware;
use App\Http\Controllers\Auth\CodeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\SessionTokenMiddleware;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Widget\getDataForWidgetController;
use App\Http\Controllers\Controllers\DownloadCsvFileForFeed;
use App\Http\Controllers\Controllers\ShowErrorLogsController;
use App\Http\Controllers\Facebook\CreateFeedByCatalogIdInFacebookController;

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

Route::group(['prefix' => 'auth'], function () {
    ROute::post('code', [CodeController::class, 'action'])->middleware(CodeMiddleware::class);
    Route::post('login', [LoginController::class, 'action'])->middleware(SessionTokenMiddleware::class);
});

Route::middleware(['store_id'])->group(function () {
    Route::group(['prefix' => 'settings'], function () {
        Route::post('store', [SettingsController::class, 'store']);
        Route::post('update', [SettingsController::class, 'update']);
        Route::post('delete', [SettingsController::class, 'delete']);
    });
    Route::get('/csv/download', [DownloadCsvFileForFeed::class, 'downloadCsvFileForAdmin']);
    Route::get('create-feed-facebook', [CreateFeedByCatalogIdInFacebookController::class, 'createFacebookFeed']);
    Route::get('show-feed-logs', [ShowErrorLogsController::class, 'ShowProductsWIthErrors']);
    Route::get('show-aal-products', [ShowErrorLogsController::class, 'ShowAllProducts']);
});

Route::get('get-widget-data', [getDataForWidgetController::class, 'getDataForWidget']);