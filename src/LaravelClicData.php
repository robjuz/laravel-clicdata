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
use Ixudra\Curl\Facades\Curl;
use robjuz\LaravelClicData\Exceptions\ConfigurationException;

class LaravelClicData
{
    public function __construct($config)
    {
        $clientId     = config('clicdata.clientId');
        $clientSecret = config('clicdata.clientSecret');

        if ( ! $clientId || ! $clientSecret) {
            ConfigurationException::message('Please provide a client id & client secret for ClicData');
        }
    }

    protected function accessTokenCacheKey()
    {
        return 'clicdata.access_token';
    }

    protected function refreshTokenPath()
    {
        return storage_path('refresh_token.txt');
    }

    protected function storeAccessToken($response)
    {
        $access_token  = $response['access_token'];
        $refresh_token = $response['refresh_token'];
        $expires_in    = $response['expires_in'];

        file_put_contents($this->refreshTokenPath(), $refresh_token);

        $expiresAt = Carbon::now()->addSeconds($expires_in - 60);

        Cache::put($this->accessTokenCacheKey(), $access_token, $expiresAt);

        return $access_token;
    }

    protected function getAccessToken()
    {
        $response = Curl::to('https://api.clicdata.com/oauth20/token')
                        ->withData([
                            "grant_type"    => "authorization_code",
                            "code"          => request()->code,
                            "client_id"     => config('clicdata.clientId'),
                            "client_secret" => config('clicdata.clientSecret'),
                            "redirect_uri"  => route('clicdata.oauth.process')
                        ])
                        ->returnResponseObject()
                        ->post();

        // Check response
        if ($response->status > 200) {
            throw new \Exception($response->content);
        }

        $result = $response->content;

        return $this->storeAccessToken(json_decode($result, true));

    }

    protected function refreshAccessToken()
    {
        $refresh_token_file = $this->refreshTokenPath();

        if ( ! file_exists($refresh_token_file) OR empty($refresh_token = file_get_contents($refresh_token_file))) {
            return $this->getAccessToken();
        }

        $response = Curl::to('https://api.clicdata.com/oauth20/token')
                        ->withData([
                            "grant_type"    => "refresh_token",
                            "refresh_token" => "$refresh_token",
                            "client_id"     => config('clicdata.clientId'),
                            "client_secret" => config('clicdata.clientSecret')
                        ])
                        ->returnResponseObject()
                        ->post();

        // Check response
        if ($response->status > 200) {
            throw new \Exception($response->content);
        }

        $result = $response->content;

        return $this->storeAccessToken(json_decode($result, true));
    }

    public function authorizeUrl()
    {
        $client_id    = config('clicdata.clientId');
        $redirect_uri = route('clicdata.oauth.process');

        return "https://api.clicdata.com/oauth20/authorize?response_type=code&client_id={$client_id}&scope=data_read&redirect_uri={$redirect_uri}";
    }

    public function accessToken()
    {
        return Cache::get($this->accessTokenCacheKey(), function () {
            return $this->refreshAccessToken();
        });
    }
}