<?php

use App\Http\Controllers\AddDropController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\TeamController;
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


Route::middleware('auth')->get('debugcode', [TeamController::class, 'removeMe']);

Route::middleware('auth')->get('/home', [MainController::class, 'main'])->name('home');

Route::get('/',function(){
    return redirect('login');
});


Route::middleware('auth')->prefix('queue')->controller(QueueController::class)->group(function () {
    Route::get('/create/room', 'processQueueCreateTeam');
    Route::get('/instructor/add', 'processQueueAddInstructor');
    Route::get('/student/add', 'processQueueAddStudent');
    Route::get('/event/create', 'processQueueCreateEvent');
    Route::get('/event/delete', 'deleteAllEvent');
    Route::get('/get/class', 'testNun');
// Route::get('team/event/delete', [MsController::class, 'deleteAllEvent']);
// Route::get('team/delete', [MsController::class, 'processQueueDeleteAllTeam']);
    Route::get('/remove/me', 'removeMeAllTeam');
    Route::get('/remove/member/me', 'removeMeAll');
});

Route::middleware('auth')->get('adds/add/student', [AddDropController::class, 'addStudent']);

Route::middleware('auth')->prefix('class')->controller(MainController::class)->group(function () {
    Route::get('/create', 'getClassCreate')->name('class/create');
    Route::post('/create', 'postClassCreate');

    Route::post('/add/student', 'postAddStudent');
    Route::post('/remove/student', 'postRemoveStudent');
    Route::get('/remove/me', 'postRemoveMe');

    Route::get('/detail/{id}', 'getClassDetail');
    Route::post('/add/owner', 'addOwner');
});

Route::middleware('auth')->prefix('team')->controller(MainController::class)->group(function () {
    Route::get('/create', 'getClassCreate');
    Route::post('/create', 'postClassCreate');
    Route::post('/delete/all', 'deleteTeam');
});

// Route::get('/adddrop/add', [AddDropController::class, 'addStudent']);
// Route::get('/adddrop/drop', [AddDropController::class, 'dropStudent']);

Route::middleware('auth')->get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
Auth::routes();
