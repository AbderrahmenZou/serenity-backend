<?php
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdviserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReviewerController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BecomeAdviserController;
use App\Http\Controllers\RapportController;


Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::prefix('auth')
    ->as('auth.')
    ->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('login');
       
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login_with_token', [AuthController::class, 'loginWithToken'])
            ->middleware('auth:sanctum')
            ->name('login_with_token');
        Route::get('logout', [AuthController::class, 'logout'])
            ->middleware('auth:sanctum')
            ->name('logout');
    });

Route::middleware('auth:sanctum')
    ->group(function () {
        Route::apiResource('chat', ChatController::class)->only(['index', 'store', 'show']);
        Route::apiResource('chat_message', ChatMessageController::class)->only(['index', 'store']);
        Route::apiResource('user', UserController::class)->only(['index']);
        Route::post('adviser/search', [UserController::class, 'search']);
        Route::post('become-adviser', [BecomeAdviserController::class, 'store']);
        Route::get('become-adviser', [BecomeAdviserController::class, 'index']);
        Route::apiResource('adviser', AdviserController::class)->only(['index', 'show']);
        Route::post('rapports', [RapportController::class, 'store'])->name('rapports.store');
        Route::get('rapports', [RapportController::class, 'index'])->name('rapports.index');

        
        // Route::get('/reviewer/users', [AdminController::class, 'index'])->middleware('auth', 'admin');
        // Route::post('/reviewer/users/{user}/approve', [AdminController::class, 'approve'])->middleware('auth', 'admin');
        
        Route::prefix('reviewer')
            ->middleware(['auth:sanctum', 'reviewer'])
            ->group(function () {
                Route::get('advisers', [ReviewerController::class, 'index']);
                Route::post('advisers/{user}/approve', [ReviewerController::class, 'approve']);
            });
        // Route::apiResource('admin', AdminController::class);
        // Route::apiResource('client', ClientController::class);
        // Route::apiResource('Reviewer', ReviewerController::class);
    });
