<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('upload_big_file');
});

Route::post('/upload', [\App\Http\Controllers\UploadFileController::class, 'upload'])->name('upload');
