<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\CommentController;

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

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Route::resource('posts', PostController::class)->only([
//     'index', 'show', 'store', 'update', 'destroy'
// ])->middleware('auth:sanctum');

Route::prefix('posts')->middleware('auth:sanctum')->group(function() {
    Route::get('{post}', [PostController::class, 'show']);
    Route::post('', [PostController::class, 'store'])->middleware('ability:create-post');
    Route::patch('{post}', [PostController::class, 'update'])->middleware('ability:update-post');
    Route::delete('{post}', [PostController::class, 'destroy'])->middleware('ability:delete-post');

    Route::prefix('{post}/comments')->group(function() {
        Route::get('{comment}', [CommentController::class, 'show']);
        Route::post('', [CommentController::class, 'store'])->middleware('ability:create-comment');
        Route::patch('{comment}', [CommentController::class, 'update'])->middleware('ability:update-comment');
        Route::delete('{comment}', [CommentController::class, 'destroy'])->middleware('ability:delete-comment');
    });
});

// Route::resource('posts.comments', CommentController::class)->only([
//     'index', 'show', 'store', 'update', 'destroy'
// ])->middleware('auth:sanctum');
