<?php

use Illuminate\Support\Facades\Route;

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
    return redirect()->to('/login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::middleware('auth')->group(function () {
    Route::resource('product-variant', 'VariantController');
    Route::get('products', 'ProductController@index')->name('product.index');
    Route::post('product', 'ProductController@store')->name('product.store');
    Route::get('product/{product}', 'ProductController@edit')->name('product.edit');
    Route::post('product/{product}', 'ProductController@update')->name('product.update');
    Route::get('product', 'ProductController@create')->name('product.create');
    Route::resource('blog', 'BlogController');
    Route::resource('blog-category', 'BlogCategoryController');
});
