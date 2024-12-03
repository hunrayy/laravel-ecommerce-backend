<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/{any}', function () {
    return file_get_contents(public_path('frontend/index.html'));
})->where('any', '.*');


// Route::get('/{any}', function () {
//     return file_get_contents(public_path('frontend/index.html'));
// })->where('any', '^(?!frontend/assets).*$');


