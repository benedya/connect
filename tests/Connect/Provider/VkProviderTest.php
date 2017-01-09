<?php

namespace Benedya\Connect\Tests\Provider;

use Benedya\Connect\Provider\VkProvider;

class VkProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var VkProvider  */
    static $provider;

    /**
     * @dataProvider mockedProvider
     */
    public function testGetAccessTokenError(VkProvider $provider)
    {
        $provider->setAccessTokenUrl(__DIR__ . '/../../data/vkontakte/access_token.error.json');
        $this->expectException(\Exception::class);
        $provider->getAccessToken('code');
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testGetAccessTokenSuccess(VkProvider $provider)
    {
        $provider->setAccessTokenUrl(__DIR__ . '/../../data/vkontakte/access_token.success.json');
        $res = $provider->getAccessToken('code');
        $this->assertEquals('access_token', $res);
        self::$provider = $provider;
    }

    public function testGetUserData()
    {
        self::$provider
            ->setApiUrl(__DIR__ . '/../../data/vkontakte/')
            ->setUserDataEndpoint('user_data.endpoint.json');
        ;
        $this->assertArrayHasKey('id', self::$provider->getUserData());
    }

    public function testGet()
    {
        self::$provider
            ->setApiUrl(__DIR__ . '/../../data/vkontakte/')
            ->setUserDataEndpoint('user_data.endpoint.json');
        ;
        $this->assertArrayHasKey('id', array_pop(self::$provider->get('user_data.endpoint.json', [])));
    }

    public function testGetUrl()
    {
        $this->assertEquals(
            'https://oauth.vk.com/authorize?client_id=client_id&redirect_uri=redirect_uri',
            self::$provider->getUrl()
        );
    }

    public function testBuildQuery()
    {
        $this->assertEquals(
            '?q=test',
            (new VkProvider('secret', [
                'client_id' => 'client_id',
                'redirect_uri' => 'redirect_uri',
            ]))
                ->buildQuery(['q' => 'test'])
        );
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testSetAccessTokenUrl(VkProvider $provider)
    {
        $this->assertInstanceOf(VkProvider::class, $provider->setAccessTokenUrl(''));
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testSetAuthorizeUrl(VkProvider $provider)
    {
        $this->assertInstanceOf(VkProvider::class, $provider->setAuthorizeUrl(''));
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testSetApiUrl(VkProvider $provider)
    {
        $this->assertInstanceOf(VkProvider::class, $provider->setApiUrl(''));
    }

    /**
     * @dataProvider mockedProvider
     */
    public function testSetUserDataEndpoint(VkProvider $provider)
    {
        $this->assertInstanceOf(VkProvider::class, $provider->setUserDataEndpoint(''));
    }

    public function mockedProvider()
    {
        $provider = $this->getMockBuilder(VkProvider::class)
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
