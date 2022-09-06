<?php

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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/crawl-data', [CrawlDataController::class, 'index']);
Route::post('/post-crawl-data', [CrawlDataController::class, 'handleCrawl'])->name('post-crawl-data');
