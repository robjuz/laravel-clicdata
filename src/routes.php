<?php

Route::group([
    'namespace' => 'robjuz\LaravelClicData\Controllers',
    'prefix' => 'clicdata',
], function() {

    Route::get('auth/init', 'OAuthController@init')->name('clicdata.oauth.init');
    Route::get('auth/process', 'OAuthController@process')->name('clicdata.oauth.process');

    Route::get('data', 'DataController@index')->name('clicdata.data.index');
    Route::get('data/{id}', 'DataController@show')->name('clicdata.data.show');

});