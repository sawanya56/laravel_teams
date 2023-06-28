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


Route::get('test',[MsController::class,'CreateEvent']);


Route::get('team/token',[MsController::class,'getAccessToken']);

Route::get('team/create',[MsController::class,'processQueueCreateTeam']);
Route::get('team/student/add',[MsController::class,'processQueueAddStudent']);
Route::get('team/instructor/add',[MsController::class,'processQueueAddInstructor']);
Route::get('team/event/create',[MsController::class,'porcessQueueCreateEvent']);


Route::get('groupmail',[MsController::class,'getGroupmail']);




Route::get('team/delete',[MsController::class,'processQueueDeleteAllTeam']);
Route::get('team/event/delete',[MsController::class,'deleteAllEvent']);
Route::get('team/event/create',[MsController::class,'porcessQueueCreateEvent']);

