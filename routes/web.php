<?php

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
    //$empleyee = \App\EmployeeProfile::find(1);
    //dd($empleyee->skills[0]->pivot->percentage);

    //$skill = \App\Skill::find(1);
    //dd($skill->employees);

    return view('welcome');
});

Auth::routes();
Route::resource('cv', 'CVController');


//Route::get('/home', 'HomeController@index')->name('home');
