<?php
/**
 * Created by IntelliJ IDEA.
 * User: robjuz
 * Date: 25.10.17
 * Time: 09:03
 */

namespace robjuz\LaravelClicData\Controllers;


use robjuz\LaravelClicData\LaravelClicData;

class OAuthController extends Controller
{

    public function init(LaravelClicData $clic_data)
    {
        return redirect($clic_data->authorizeUrl());
    }

    public function process(LaravelClicData $clic_data)
    {
        $clic_data->accessToken();
    }

}