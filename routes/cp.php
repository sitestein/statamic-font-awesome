<?php

use Illuminate\Support\Facades\Route;
use Sitestein\FontAwesome\Http\Controllers\FontAwesomeController;

/*
|--------------------------------------------------------------------------
| Cp Routes
|--------------------------------------------------------------------------
|
| Here is where you can register cp routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "cp" middleware group. Now create something great!
|
*/

Route::get('/font-awesome/search/{search}', [FontAwesomeController::class, 'search']);
