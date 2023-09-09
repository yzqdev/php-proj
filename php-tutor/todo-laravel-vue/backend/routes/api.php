<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function() {
        return Response::json(['user' => Auth::user()]);
    });

    Route::get('/task', [TaskController::class, 'getAll']);
    Route::get('/task/{id}', [TaskController::class, 'getById']);
    Route::put('/task/{id}', [TaskController::class, 'update']);
    Route::post('/task', [TaskController::class, 'create']);
    Route::get('/task/{id}/tag', [TaskController::class, 'getTaskTags']);

    Route::post('/task/{id}/attachment', [AttachmentController::class, 'upload']);
    Route::get('/task/{id}/attachment', [AttachmentController::class, 'getAttachmentsByTask']);
    Route::delete('/task/{id}/attachment/{attachmentId}', [AttachmentController::class, 'delete']);

    Route::post('/tag', [TagController::class, 'create']);
    Route::get('/tag', [TagController::class, 'getAll']);

    Route::delete('/task/{id}', [TaskController::class, 'delete']);
});

Route::get('/task/{id}/attachment/{attachmentId}/download', [AttachmentController::class, 'download']);

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
