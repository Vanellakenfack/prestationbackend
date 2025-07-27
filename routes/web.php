<?php

use Illuminate\Support\Facades\Route;

// routes/web.php
Route::get('/sanctum/csrf-cookie', function () {
    return response()->noContent();
});