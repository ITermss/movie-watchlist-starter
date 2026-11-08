<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->file(public_path('filmlog.html'), [
        'Content-Type' => 'text/html; charset=UTF-8',
    ]);
});

Route::get('/app.js', function () {
    return response()->file(public_path('app.js'), [
        'Content-Type' => 'application/javascript; charset=UTF-8',
    ]);
});
