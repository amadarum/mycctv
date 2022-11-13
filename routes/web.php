<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CameraController;
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
    return redirect('/dashboard');
});

Route::get('/dashboard', [DashboardController::class,'index'])->middleware(['auth'])->name('dashboard');
Route::resource('/camera', CameraController::class)->middleware(['auth']);
Route::get('/capture/{id}', [DashboardController::class,'camera'])->middleware(['auth'])->name('capture');
Route::get('/thumbnail/{hostname}/{filename}', [DashboardController::class,'thumbnail'])->middleware(['auth'])->name('thumbnail');
Route::get('/video/{hostname}/{filename}', [DashboardController::class,'video'])->middleware(['auth'])->name('video');

require __DIR__.'/auth.php';
