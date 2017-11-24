<?php
/**
 * Created by IntelliJ IDEA.
 * User: robjuz
 * Date: 24.11.17
 * Time: 09:49
 */

namespace robjuz\LaravelClicData;

use Ixudra\Curl\Facades\Curl;

class Data
{
    public static function get($id, $page = "")
    {
        $response = Curl::to("https://api.clicdata.com/data/{$id}")
                        ->withHeader('Authorization: Bearer ' . Facade::accessToken())
                        ->asJson()
                        ->get();

        return $response->data;
    }

    public static function all($options = [])
    {
        $response = Curl::to('https://api.clicdata.com/data/')
                        ->withHeader('Authorization: Bearer ' . Facade::accessToken())
                        ->asJson()
                        ->get();

        return $response->data;
    }
}