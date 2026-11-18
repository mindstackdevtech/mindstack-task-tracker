<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectToolsController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Support\Facades\Route;

/*
 | Every route sits inside the SecurityHeaders middleware.
 | "throttle:6,1" means at most 6 requests a minute from one person,
 | which slows down password guessing and stops email or message flooding.
 */
Route::middleware(SecurityHeaders::class)->group(function () {

    Route::redirect('/', '/dashboard');

    // Pages anyone can open, logged in or not
    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/contact', [PageController::class, 'contact'])->name('contact');
    Route::post('/contact', [PageController::class, 'sendContact'])->middleware('throttle:3,1')->name('contact.send');

    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');

        // Forgot password
        Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
        Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:6,1')->name('password.email');
        Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
        Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Notifications (the bell)
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::get('/notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');

        // Account settings
        Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
        Route::put('/account', [AccountController::class, 'updateProfile'])->middleware('throttle:10,1')->name('account.profile');
        Route::put('/account/password', [AccountController::class, 'updatePassword'])->middleware('throttle:6,1')->name('account.password');

        // Calendar of due dates
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

        Route::resource('projects', ProjectController::class)->except(['create', 'edit']);

        // Team chat inside a project
        Route::get('/projects/{project}/chat', [ChatController::class, 'show'])->name('projects.chat');
        Route::get('/projects/{project}/chat/messages', [ChatController::class, 'feed'])->name('projects.chat.feed');
        Route::post('/projects/{project}/chat', [ChatController::class, 'store'])->middleware('throttle:30,1')->name('projects.chat.store');

        // Activity history and spreadsheet export
        Route::get('/projects/{project}/activity', [ProjectToolsController::class, 'activity'])->name('projects.activity');
        Route::get('/projects/{project}/export', [ProjectToolsController::class, 'export'])->middleware('throttle:10,1')->name('projects.export');

        // Invites and members
        Route::get('/join/{code}', [ProjectMemberController::class, 'join'])->middleware('throttle:20,1')->name('projects.join');
        Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store'])->middleware('throttle:10,1')->name('projects.members.store');
        Route::post('/projects/{project}/invite/reset', [ProjectMemberController::class, 'resetInvite'])->name('projects.invite.reset');
        Route::delete('/projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');

        Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
        Route::resource('tasks', TaskController::class)->except(['edit']);

        Route::post('/tasks/{task}/checklist', [ChecklistController::class, 'store'])->name('checklist.store');
        Route::patch('/checklist/{item}', [ChecklistController::class, 'toggle'])->name('checklist.toggle');
        Route::delete('/checklist/{item}', [ChecklistController::class, 'destroy'])->name('checklist.destroy');

        Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->middleware('throttle:30,1')->name('comments.store');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    });
});
