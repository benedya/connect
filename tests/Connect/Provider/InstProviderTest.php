<?php

namespace Benedya\Connect\Tests\Provider;

use Benedya\Connect\Provider\InstProvider;

class InstProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var InstProvider*/
    static $provider;

    /**
     * @dataProvider mockedProvider
     */
    public function testGetAccessToken(InstProvider $provider)
    {
        $provider
            ->method('post')
            ->will($this->returnValue(file_get_contents(__DIR__ . '/../../data/instagram/access_token.json')))
        ;
        $this->assertEquals('access_token', $provider->getAccessToken('code'));
        self::$provider = $provider;
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testGetUrl(InstProvider $provider)
    {
        $this->assertEquals(
            'https://api.instagram.com/oauth/authorize/?client_id=client_id&redirect_uri=redirect_uri&response_type=code',
            $provider->getUrl()
        );
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testBuildQuery(InstProvider $provider)
    {
        $this->assertEquals(
            '?q=test',
            (new InstProvider(
                'secret',
                [
                    'client_id' => 'client_id',
                    'redirect_uri' => 'redirect_uri',
                    'response_type' => 'code',
                ]
            ))->buildQuery(['q' => 'test'])
        );
    }

    public function testGetUserData()
    {
        self::$provider
            ->setApiUrl(__DIR__ . '/../../data/instagram/')
            ->setUserDataEndpoint('user_data.endpoint.json')
        ;
        $this->assertArrayHasKey('id', self::$provider->getUserData());
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testSetAuthorizeUrl(InstProvider $provider)
    {
        $this->assertInstanceOf(InstProvider::class, $provider->setAuthorizeUrl(''));
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testSetAccessTokenUrl(InstProvider $provider)
    {
        $this->assertInstanceOf(InstProvider::class, $provider->setAccessTokenUrl(''));
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testSetApiUrl(InstProvider $provider)
    {
        $this->assertInstanceOf(InstProvider::class, $provider->setApiUrl(''));
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testSetUserDataEndpoint(InstProvider $provider)
    {
        $this->assertInstanceOf(InstProvider::class, $provider->setUserDataEndpoint(''));
    }

    public function mockedProvider()
    {
        $provider = $this->getMockBuilder(InstProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([
                'secret',
                [
                    'client_id' => 'client_id',
                    'redirect_uri' => 'redirect_uri',
                    'response_type' => 'code',
                ]
            ])
            ->setMethods(['post', 'buildQuery'])
            ->getMock();
        $provider
            ->method('buildQuery')
            ->will($this->returnValue(''));
        return [[$provider]];
    }
}
