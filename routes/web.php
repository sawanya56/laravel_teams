<?php

use App\Http\Controllers\MainController;
use App\Http\Controllers\MsController;
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

Route::get('test', [MsController::class, 'addMe']);

Route::get('team/token', [MsController::class, 'getAccessTokenDatabase']);

Route::get('team/create', [MsController::class, 'processQueueCreateTeam']);
Route::get('team/student/add', [MsController::class, 'processQueueAddStudent']);
Route::get('team/instructor/add', [MsController::class, 'processQueueAddInstructor']);
Route::get('team/event/create', [MsController::class, 'porcessQueueCreateEvent']);
Route::get('team/post/massage', [MsController::class, 'processQueuePostMessageToTeam']);

Route::get('groupmail', [MsController::class, 'getGroupmail']);

Route::get('team/delete', [MsController::class, 'processQueueDeleteAllTeam']);
Route::get('team/event/delete', [MsController::class, 'deleteAllEvent']);
Route::get('team/event/create', [MsController::class, 'porcessQueueCreateEvent']);

Route::get('/main', [MainController::class, 'main']);
Route::get('/class/detail/{id}', [MainController::class, 'getClassDetail']);

Route::post('/class/add/owner', [MainController::class, 'addOwner']);
