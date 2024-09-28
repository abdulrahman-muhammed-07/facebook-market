<?php

use App\Http\Controllers\Auth\FacebookAuthController;
use App\Http\Controllers\Controllers\DownloadCsvFileForFeed;
use App\Http\Controllers\Controllers\ForceExportCsvFileForFeed;
use App\Http\Controllers\DumpController;
use App\Http\Controllers\Settings\UninstallController;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['store_id'])->group(function () {
    Route::get('/facebook-redirect', [FacebookAuthController::class, 'redirectToFacebook']);
    Route::get('/csv/make', [ForceExportCsvFileForFeed::class, 'ForceExportCsvFileForFeed']);
});

Route::get('/uninstall', [UninstallController::class, 'uninstall']);

Route::get('/facebook-handle', [FacebookAuthController::class, 'handleFacebookCallback']);

Route::get('/csv/download', [DownloadCsvFileForFeed::class, 'downloadCsvFile']);

Route::get('/dump', [DumpController::class, 'index']);