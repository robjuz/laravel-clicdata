<?php namespace robjuz\LaravelClicData;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return LaravelClicData::class;
    }

    public static function authorizeUrl()
    {
        static::$app->make(LaravelClicData::class)->authorizeUrl();
    }
}