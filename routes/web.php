<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan; 

Route::get('/', function () {
    return view('welcome');
});

Route::get('/limpiar-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    
    Artisan::call('config:cache');
    
    return "✅ ¡Listo papi! Memoria borrada y configuración actualizada. Ya puedes probar el Login.";
});
