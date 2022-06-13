<?php

use App\Http\Controllers\API\V1\ArticleController;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\AuthorController;
use App\Http\Controllers\API\V1\BlogController;
use App\Http\Controllers\API\V1\UserController;
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


Route::group(['prefix'=>'v1/auth'],function(){
    Route::post('/login',[AuthController::class,'login']);
    Route::post('/register',[AuthController::class,'register']);

});

Route::group(['prefix'=>'v1','middleware'=>'auth:sanctum'],function(){
    //Articles Route
    Route::apiResource('/articles',ArticleController::class);

    //Author Routes, {user} can be either id or slug
    Route::get('/authors/{user}',[AuthorController::class,'show'])->name('authors');

    //User
    Route::get('/user',UserController::class);//invoke function ko lagi yesari lekhna milxa

    Route::get('/logout',[AuthController::class,'logout']);
    //get current authenticated user
    Route::get('/me',[AuthController::class,'getAuthenticatedUser']);

    //blog routes
    Route::apiResource('/blogs',BlogController::class);
});
