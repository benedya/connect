<?php

namespace Benedya\Connect\Tests;

class ProviderFactory extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getProviders
     */
    public function testCreate($provider)
    {
        $class = "\\Benedya\\Connect\\Provider\\".ucfirst(strtolower($provider))."Provider";
        $this->assertInstanceOf(
            $class,
            \Benedya\Connect\ProviderFactory::create($provider, '', [
                'client_id' => 1,
                'redirect_uri' => 'http://example.com'
            ])
        );
    }

    public function getProviders()
    {
        return [['vk'], ['facebook'], ['instagram']];
    }
}
