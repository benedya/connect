<?php

namespace Benedya\Connect\Tests\Provider;

use Benedya\Connect\Provider\InstagramProvider;

class InstagramProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var InstagramProvider*/
    static $provider;

    /**
     * @dataProvider mockedProvider
     */
    public function testGetAccessToken(InstagramProvider $provider)
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
    public function testGetUrl(InstagramProvider $provider)
    {
        $this->assertEquals(
            'https://api.instagram.com/oauth/authorize/?client_id=client_id&redirect_uri=redirect_uri&response_type=code',
            $provider->getUrl()
        );
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testBuildQuery(InstagramProvider $provider)
    {
        $this->assertEquals(
            '?q=test',
            (new InstagramProvider(
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
    public function testSetAuthorizeUrl(InstagramProvider $provider)
    {
        $this->assertInstanceOf(InstagramProvider::class, $provider->setAuthorizeUrl(''));
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testSetAccessTokenUrl(InstagramProvider $provider)
    {
        $this->assertInstanceOf(InstagramProvider::class, $provider->setAccessTokenUrl(''));
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testSetApiUrl(InstagramProvider $provider)
    {
        $this->assertInstanceOf(InstagramProvider::class, $provider->setApiUrl(''));
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testSetUserDataEndpoint(InstagramProvider $provider)
    {
        $this->assertInstanceOf(InstagramProvider::class, $provider->setUserDataEndpoint(''));
    }

    public function mockedProvider()
    {
        $provider = $this->getMockBuilder(InstagramProvider::class)
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
