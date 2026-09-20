<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::post('/auth/jwt/create/', [LoginController::class, 'store']);
