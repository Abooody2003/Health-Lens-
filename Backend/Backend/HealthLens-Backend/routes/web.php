<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\SpecializationController;
use App\Http\Controllers\Admin\SurgeryAnalysisController;

Route::get('/web-test', function () {
    return 'WEB OK';
});


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Redirect accidental browser hits to API login.
Route::get('/api/auth/login', function () {
    return redirect()->route('login');
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Users
    Route::resource('users', UserController::class);
    
    // Doctors
    Route::resource('doctors', DoctorController::class);
    
    // Specializations
    Route::resource('specializations', SpecializationController::class);
    
    // Surgery Analyses
    Route::resource('surgery-analyses', SurgeryAnalysisController::class)->parameters([
        'surgery-analyses' => 'surgeryAnalysis'
    ]);
});
