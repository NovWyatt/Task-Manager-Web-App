<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

// Projects
Route::resource('projects', ProjectController::class)->middleware(['auth']);

// Tasks
Route::resource('tasks', TaskController::class)->middleware(['auth']);

// Categories
Route::resource('categories', CategoryController::class)->middleware(['auth']);

// Project Members
Route::post('/projects/{project}/members', [ProjectController::class, 'addMember'])->middleware(['auth'])->name('projects.members.add');
Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->middleware(['auth'])->name('projects.members.remove');

// Task Comments
Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment'])->middleware(['auth'])->name('tasks.comments.add');
// Task Attachments
Route::delete('/tasks/{task}/attachments/{media}', [TaskController::class, 'removeAttachment'])->middleware(['auth'])->name('tasks.attachments.remove');

//Task Excel
Route::get('/tasks/export', [TaskController::class, 'export'])->middleware(['auth'])->name('tasks.export');
Route::post('/tasks/import', [TaskController::class, 'import'])->middleware(['auth'])->name('tasks.import');

// Admin Routes
// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->middleware('can:viewAny,App\Models\User')->name('users.index');
    Route::get('/users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->middleware('can:create,App\Models\User')->name('users.create');
    Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->middleware('can:create,App\Models\User')->name('users.store');
    Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->middleware('can:update,user')->name('users.edit');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->middleware('can:update,user')->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->middleware('can:delete,user')->name('users.destroy');
});

//test
// Temporary route for checking user roles - remove in production
// Route::get('/debug/users', function () {
//     if (!auth()->check() || !auth()->user()->hasRole('admin')) {
//         abort(403);
//     }
    
//     $users = \App\Models\User::with('roles')->get();
//     return view('debug.users', compact('users'));
// })->middleware('auth');