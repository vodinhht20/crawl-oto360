<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CrawlDataController;
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

Route::get('/login', [AuthenticationController::class, 'index'])->name("login");
Route::get('/logout', [AuthenticationController::class, 'logout'])->name("logout");
Route::post('/post-login', [AuthenticationController::class, 'postLogin'])->name("post-login");
Route::get('/', [CrawlDataController::class, 'index'])->middleware("authenticated")->name("crawl-data");
Route::post('/post-crawl-data', [CrawlDataController::class, 'handleCrawl'])->middleware("authenticated")->name('post-crawl-data');
Route::post('/post-view-data', [CrawlDataController::class, 'viewData'])->middleware("authenticated")->name('post-view-data');
