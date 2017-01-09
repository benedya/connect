<?php

namespace Benedya\Connect\Tests\Provider;

use Benedya\Connect\Provider\FbProvider;

class FbProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var FbProvider  */
    static $provider;

    /**
     * @dataProvider mockedProvider
     */
    public function testGetAccessTokenSuccess(FbProvider $provider)
    {
        $provider->setAccessTokenUrl(__DIR__ . '/../../data/facebook/access_token.json');
        $this->assertEquals('access_token', $provider->getAccessToken('code'));
        self::$provider = $provider;
    }

    public function testGetUserData()
    {
        self::$provider
            ->setApiUrl(__DIR__ . '/../../data/facebook/')
            ->setUserDataEndpoint('user_data.endpoint.json');
        $this->assertArrayHasKey('id', self::$provider->getUserData());
    }

    public function testGet()
    {
        self::$provider
            ->setApiUrl(__DIR__ . '/../../data/facebook/')
            ->setUserDataEndpoint('user_data.endpoint.json');
        ;
        $this->assertArrayHasKey('id', self::$provider->get('user_data.endpoint.json', []));
    }

    public function mockedProvider()
    {
        $provider = $this->getMockBuilder(FbProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([
                'secret',
                [
                    'client_id' => 'client_id',
                    'redirect_uri' => 'redirect_uri',
                ]
            ])
            ->setMethods(['buildQuery'])
            ->getMock();
        $provider
            ->expects($this->any())
            ->method('buildQuery')
            ->will($this->returnValue(''))
        ;
        return [[$provider]];
    }
}
