<?php

namespace LaravelPodioTest\Exceptions;

use PHPUnit\Framework\TestCase;
use SpotOnLive\LaravelClicData\Exceptions\ConfigurationException;

class ConfigurationExceptionTest extends TestCase
{
    public function testMessage()
    {
        $message = 'demo message';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage($message);

        ConfigurationException::message($message);
    }

    public function testMissingApp()
    {
        $appName = 'demo-app';

        $this->expectException(ConfigurationException::class);

        $this->expectExceptionMessage(sprintf(
            'No configuration for application %s',
            $appName
        ));

        ConfigurationException::missingApp($appName);
    }
}
