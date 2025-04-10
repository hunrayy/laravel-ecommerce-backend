<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';
// require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());


// (require_once __DIR__.'/bootstrap/app.php')
//     ->handle(Request::capture());



































// <?php

// use Illuminate\Http\Request;

// define('LARAVEL_START', microtime(true));

// // Determine if the application is in maintenance mode...
// if (file_exists($maintenance = __DIR__.'/../laravel_app/storage/framework/maintenance.php')) {
//     require $maintenance;
// }

// // Register the Composer autoloader...
// require __DIR__.'/../laravel_app/vendor/autoload.php';
// // require __DIR__.'/vendor/autoload.php';

// // Bootstrap Laravel and handle the request...
// (require_once __DIR__.'/../laravel_app/bootstrap/app.php')
//     ->handleRequest(Request::capture());


// // (require_once __DIR__.'/bootstrap/app.php')
// //     ->handle(Request::capture());
