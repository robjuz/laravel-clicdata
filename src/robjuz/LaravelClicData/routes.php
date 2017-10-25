<?php

Route::group([
    'namespace' => 'robjuz\LaravelClicData\Controllers',
    'prefix' => 'clicdata',
], function() {

    Route::get('auth/init',
        'OAuthController@init')->name('clicdata.oauth.init');

    Route::get('auth/process',
        'OAuthController@process')->name('clicdata.oauth.process');

});