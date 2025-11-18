<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::controller(ReportController::class)
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
    
    Route::post('upload-rar', 'uploadRAR')->name('upload.rar');
    Route::post('upload-bg', 'uploadBG')->name('upload.bg');
    Route::post('download', 'download')->name('download');
});