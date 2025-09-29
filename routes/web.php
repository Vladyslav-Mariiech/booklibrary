<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Crud\BookController as CrudBookController;
use App\Http\Controllers\Templates\BookController as TemplateBookController;
use App\Http\Controllers\Crud\AuthorController as CrudAuthorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [MainController::class, 'index'])->name('home');
Route::get('/books', [TemplateBookController::class, 'index'])->name('books.index');


Route::get('/secretRoom', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/secretRoom', [LoginController::class, 'login']);
Route::get('/secretMember', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/secretMember', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('crud')->name('crud.')->group(function () {

    // Главная crud
    Route::view('/', 'crud.index')->name('index');

    // CRUD для категорий
    Route::resource('books', CrudBookController::class);

    Route::resource('authors', CrudAuthorController::class);

});
