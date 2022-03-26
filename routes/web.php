<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CacheController;

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


Route::get('/', ['middleware' => 'auth', 'as' => 'dashboard.list', 'uses' => 'App\Http\Controllers\DashboardController@index']);
Route::get('/dashboard', ['middleware' => 'auth', 'as' => 'dashboard.list', 'uses' => 'App\Http\Controllers\DashboardController@index']);

Route::get('/settings', ['middleware' => 'auth', 'as' => 'settings.list', 'uses' => 'App\Http\Controllers\SettingsController@index']);
Route::post('/settings/update', ['middleware' => 'auth', 'as' => 'settings.update', 'uses' => 'App\Http\Controllers\SettingsController@update']);

//Route::get('scan/{name?}', ['as' => 'data.scan', 'uses' => 'DataController@scan'])->where('name', '[A-Za-z-_]+');
Route::get('/cache_clear', ['middleware' => 'auth', 'as' => 'cache.clear', 'uses' => 'App\Http\Controllers\CacheController@clear']);

Route::get('/data_scan', ['middleware' => 'auth', 'as' => 'data.scan', 'uses' => 'App\Http\Controllers\DataScanController@scan']);

Route::get('/websites', ['middleware' => 'auth', 'as' => 'company.list', 'uses' => 'App\Http\Controllers\WebsitesController@index']);
Route::get('/websites/{id}/edit', ['middleware' => 'auth', 'as' => 'company.edit', 'uses' => 'App\Http\Controllers\WebsitesController@edit']);
Route::post('/websites/update', ['middleware' => 'auth', 'as' => 'company.update', 'uses' => 'App\Http\Controllers\WebsitesController@update']);

Route::get('/vacancies', ['middleware' => 'auth', 'as' => 'vacancy.list', 'uses' => 'App\Http\Controllers\VacanciesController@index']);

Route::get('/logs', ['middleware' => 'auth', 'as' => 'logs.list', 'uses' => 'App\Http\Controllers\LogsController@index']);
Route::get('/logs/destroy', ['middleware' => 'auth', 'as' => 'logs.destroy', 'uses' => 'App\Http\Controllers\LogsController@destroy']);

Route::get('/alllogs', ['middleware' => 'auth', 'as' => 'alllogs.list', 'uses' => 'App\Http\Controllers\LogsController@allLogs']);

Route::get('data/export/', ['middleware' => 'auth', 'as' => 'export.list', 'uses' => 'App\Http\Controllers\VacanciesController@export']);
require __DIR__.'/auth.php';

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
