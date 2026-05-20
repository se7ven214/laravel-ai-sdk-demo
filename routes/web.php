<?php

use App\Http\Controllers\AiDemoController;
use App\Http\Controllers\AiStreamController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/ai-demo');

Route::get('/ai-demo', [AiDemoController::class, 'index'])->name('ai-demo.index');
Route::get('/ai-demo/prompt', fn () => redirect()->route('ai-demo.index'));
Route::post('/ai-demo/prompt', [AiDemoController::class, 'prompt'])->name('ai-demo.prompt');
Route::get('/ai-demo/stream', fn () => redirect()->route('ai-demo.index'));
Route::post('/ai-demo/stream', [AiStreamController::class, 'stream'])->name('ai-demo.stream');
Route::get('/ai-demo/clear', [AiDemoController::class, 'clear'])->name('ai-demo.clear');
