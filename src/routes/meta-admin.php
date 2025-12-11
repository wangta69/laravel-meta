<?php

// 메타관리
Route::get('/', 'MetaController@index')->name('index');
Route::get('edit/{item}', 'MetaController@edit')->name('edit');
Route::put('edit/{item}', 'MetaController@update');
Route::delete('delete/{item}', 'MetaController@destory')->name('delete');
Route::get('create', 'MetaController@create')->name('create');
Route::post('create', 'MetaController@store');
Route::get('create-sitemap', 'MetaController@createSitemap')->name('create.sitemap');

Route::post('indexnow/{item}', 'MetaController@indexNow')->name('index-now');
