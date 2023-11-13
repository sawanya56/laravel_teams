<?php

use App\Http\Controllers\AddDropController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\MsController;
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

Route::get('/', function () {
    return view('app');
});



Route::get('team/create/room', [TeamController::class, 'createTeams']);
Route::get('team/instructor/add', [TeamController::class, 'addInstructor']);
Route::get('team/student/add', [TeamController::class, 'addStudent']);
Route::get('team/student/remove', [TeamController::class, 'removeStudent']);
Route::get('team/event/create', [TeamController::class, 'createEvent']);
Route::get('team/post/massage', [TeamController::class, 'postMeetingToTeam']);
Route::get('team/delete/room', [TeamController::class, 'deleteAllGroup']);
Route::get('team/delete/event', [TeamController::class, 'RemoveEvent']);
// Route::get('test', [MsController::class, 'CreateEvent']);

// Route::get('team/token', [MsController::class, 'getAccessTokenDatabase']);

Route::get('queue/create/room', [QueueController::class, 'processQueueCreateTeam']);
Route::get('queue/instructor/add', [QueueController::class, 'processQueueAddInstructor']);
Route::get('queue/student/add', [QueueController::class, 'processQueueAddStudent']);
Route::get('queue/event/create', [QueueController::class, 'processQueueCreateEvent']);
Route::get('queue/post/massage', [QueueController::class, 'processQueuePostMessageToTeam']);
Route::get('queue/get/groupmail', [QueueController::class, 'getGroupMailAndChannelId']);
Route::get('queue/get/groupmail', [QueueController::class, 'getGroupMailAndChannelId']);
// Route::get('team/event/delete', [MsController::class, 'deleteAllEvent']);
// Route::get('team/delete', [MsController::class, 'processQueueDeleteAllTeam']);


// Route::get('team/student/delete', [MsController::class, 'RemoveMember']);
// Route::get('groupmail', [MsController::class, 'getGroupmail']);
// Route::get('team/delete/event', [MsController::class, 'RemoveEvent']);
// Route::get('team/event/create', [MsController::class, 'porcessQueueCreateEvent']);

Route::get('/main', [MainController::class, 'main'])->name('main');
Route::get('/class/detail/{id}', [MainController::class, 'getClassDetail']);

// Route::post('/class/add/owner', [MainController::class, 'addOwner']);
// Route::post('/team/delete/all', [MainController::class, 'deleteTeam']);

Route::get('/class/create', [MainController::class, 'getClassCreate'])->name('class/create');
Route::post('/class/create', [MainController::class, 'postClassCreate']);
// Route::post('/class/add/student', [MainController::class, 'postAddStudent']);
// Route::post('/class/remove/student', [MainController::class, 'postRemoveStudent']);


// Route::get('/adddrop/add', [AddDropController::class, 'addStudent']);
// Route::get('/adddrop/drop', [AddDropController::class, 'dropStudent']);


Route::prefix('team')->controller(MainController::class)->group(function(){
    Route::get('/create', 'getClassCreate');
    Route::post('/create',  'postClassCreate');
}); 



Route::get('test', [QueueController::class, 'processQueueCreateTeam'])->name("nun");

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);




Route::get('/app', function () {
    return view('app');
});
Route::get('/menu', function () {
    return view('menu');
});
