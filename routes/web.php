<?php

use App\Http\Controllers\MainController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MsController;

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

// Route::get('test','TestController@');
Route::get('main',[MsController::class,'main']);
Route::get('student',[MsController::class,'AddStudent']);
Route::get('instructor',[MsController::class,'AddInstructor']);
Route::get('groupmail',[MsController::class,'getGroupmail']);

