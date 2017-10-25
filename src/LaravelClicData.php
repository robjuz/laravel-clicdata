<?php
/**
 * Created by IntelliJ IDEA.
 * User: robjuz
 * Date: 25.10.17
 * Time: 08:43
 */

namespace robjuz\LaravelClicData;


use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use robjuz\LaravelClicData\Exceptions\ConfigurationException;

class LaravelClicData
{
    public function __construct($config)
    {
        $clientId = config('clicdata.clientId');
        $clientSecret = config('clicdata.clientSecret');

        if (!$clientId || !$clientSecret) {
            ConfigurationException::message('Please provide a client id & client secret for ClicData');
        }
    }

    protected function makeTokenRequest($data)
    {
        $data_string = http_build_query($data);

        $ch = curl_init('https://api.clicdata.com/oauth20/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded',
                'Content-Length: ' . strlen($data_string)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $result = curl_exec($ch);

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Check response
        if ($http_status > 200) {
            throw new \Exception($result);
        }

        $json          = json_decode($result, true);
        $access_token  = $json['access_token'];
        $refresh_token = $json['refresh_token'];
        $expires_in    = $json['expires_in'];

        file_put_contents(__DIR__ . '/config/refresh_token.txt', $refresh_token);

        $expiresAt = Carbon::now()->addSeconds($expires_in);

        Cache::put('clicdata.access_token', $access_token, $expiresAt);

        return $access_token;
    }

    protected function getAccessToken()
    {
        // Set post variables
        $code          = request()->code; // something like b1ca93d9-4747-4bf1-96ff-4944588e45f1 that you will get from the callback;
        $client_id     = config('clicdata.clientId'); // Your client id
        $client_secret = config('clicdata.clientSecret'); // Your client secret

        // Create array and jSON encode it
        $data = array(
            "grant_type"    => "authorization_code",
            "code"          => "$code",
            "client_id"     => "$client_id",
            "client_secret" => "$client_secret"
        );

        return $this->makeTokenRequest($data);

    }

    protected function refreshAccessToken()
    {
        $refresh_token_file = __DIR__ . '/config/refresh_token.txt';
        if ( ! file_exists($refresh_token_file) OR empty($refresh_token = file_get_contents($refresh_token_file))) {
            return $this->getAccessToken();
        }


        // Set post variables
        $client_id     = config('clicdata.clientId'); // Your client id
        $client_secret = config('clicdata.clientSecret'); // Your client secret

        $data = array(
            "grant_type"    => "refresh_token",
            "code"          => "$refresh_token",
            "client_id"     => "$client_id",
            "client_secret" => "$client_secret"
        );

        return $this->makeTokenRequest($data);

    }

    public function authorizeUrl()
    {
        $client_id    = config('clicdata.clientId');
        $redirect_uri = route('clicdata.oauth.process');

        return "https://api.clicdata.com/oauth20/authorize?response_type=code&client_id={$client_id}&scope=data_read&redirect_uri={$redirect_uri}";
    }

    public function accessToken()
    {
        return Cache::get('clicdata.access_token', function () {
            return $this->refreshAccessToken();
        });
    }

    public function get($id, $page = "")
    {
        $access_token = $this->accessToken();

        $data        = array(
            "id"   => $id,
            "page" => $page,
        );
        $data_string = json_encode($data);

        $ch = curl_init('https://api.clicdata.com/data/');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        $result = curl_exec($ch);

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status > 200) {
            if ($http_status > 200) {
                throw new \Exception($result);
            }
        }

        return json_decode($result);
    }
}