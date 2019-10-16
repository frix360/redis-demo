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

Route::get('/', 'HomeController@index')->name('index');

Route::post('/shoppingCarts', 'HomeController@createShoppingCart')->name('shoppingCart.create');

Route::delete('/shoppingCarts/{id}', 'HomeController@deleteShoppingCart')->name('shoppingCart.delete');

Route::post('/products', 'HomeController@createProduct')->name('products.create');

Route::post('/products/{id}', 'HomeController@deleteProduct')->name('products.delete');

Route::post('/addToCart', 'HomeController@addProductToCart')->name('products.addToCart');

Route::post('/removeFromCart', 'HomeController@removeProductFromCart')->name('products.removeFromCart');

