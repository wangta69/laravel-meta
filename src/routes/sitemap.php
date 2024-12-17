<?php
// 메타관리
Route::get('{vender}.xml', array('uses'=>'SitemapController@index'));