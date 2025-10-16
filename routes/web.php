<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;

Route::get('/', function () {
    return view('/chatbot');
});

// Rute untuk menampilkan halaman chatbot
Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');

// Rute untuk menerima pesan dari chatbot
Route::post('/chat/send', [ChatbotController::class, 'sendMessage'])->name('chat.send');
