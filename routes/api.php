<?php

use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [LoginController::class, 'login']);


Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('logout', [LoginController::class, 'logout']);
    Route::get('category', [CategoryController::class,'index']);
    Route::get('blog', [BlogController::class,'index']);
});

Route::group(['middleware' => ['jwt.verify:user']], function() {
    Route::apiResource('blog', BlogController::class,
        ['only' => [
                'store',
                'show',
                'update',
                'destroy',
            ]
        ]
    );
});

Route::group(['middleware' => ['jwt.verify:admin']], function() {
    Route::apiResource('category', CategoryController::class,
        ['only' => [
                'store',
                'show',
                'update',
                'destroy',
            ]
        ]
    );
});