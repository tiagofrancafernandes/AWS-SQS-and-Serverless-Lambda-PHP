<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::any('fake-webhook', function (Request $request) {
    file_put_contents(
        storage_path('logs/fake-webhook-' . date('Y-m-d') . '.log'),
        spf(
            '%s[%s]%s%s',
            PHP_EOL,
            date('c'),
            PHP_EOL,
            json_encode($request->all(), 64|128).PHP_EOL,
        ),
        FILE_APPEND
    );

    return response()->json([
        'received_on' => date('c'),
    ], 200);
})
->name('fake-webhook')
->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
