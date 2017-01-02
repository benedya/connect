<?php

namespace Benedya\Connect\Provider;

use Benedya\Connect\Traits\CurlToolsTrait;

class InstProvider implements ProviderInterface
{
    use CurlToolsTrait;

    protected $clientSecret;
    protected $requestParameters;
    protected $authorizeUrl = 'https://api.instagram.com/oauth/authorize/';
    protected $accessTokenUrl = 'https://api.instagram.com/oauth/access_token';
    protected $userDataUrl = 'https://api.instagram.com/v1/users/self/';
    protected $requiredParameters = ['client_id', 'redirect_uri'];
    protected $accessTokenData;

    function __construct($clientSecret, array $requestParameters)
    {
        if(!(count(array_intersect_key(array_flip($this->requiredParameters), $requestParameters)) === count($this->requiredParameters))) {
            throw new \Exception('Fields "' . join(', ', $this->requiredParameters) . '" are required.');
        }
        $this->requestParameters = $requestParameters;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->authorizeUrl .'?'. http_build_query($this->requestParameters);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getAccessToken()
    {
        if(isset($this->accessTokenData['access_token'])) {
            return $this->accessTokenData['access_token'];
        }
        $code = isset($_GET['code']) ? $_GET['code'] : null;
        if(!$code) {
            throw new \Exception('Code not found.');
        }
        $data = $this->post($this->accessTokenUrl, [
                'client_id' => $this->requestParameters['client_id'],
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->requestParameters['redirect_uri'],
                'code' => $code,
                'grant_type' => 'authorization_code',
            ]);
        $this->accessTokenData = json_decode($data, true);
        if(!isset($this->accessTokenData['access_token'])) {
            throw new \Exception('Oops.. Can not get access token, response ' . print_r($this->accessTokenData, true));
        }
        return $this->accessTokenData['access_token'];
    }

    /**
     * @param array $fields
     * @return array|mixed
     * @throws \Exception
     */
    public function getUserData(array $fields = [])
    {
        $accessToken = $this->getAccessToken();
        $requestParameters = array_merge([
            'access_token' => $accessToken,
        ], $fields);
        $response = file_get_contents($this->userDataUrl . '?' . http_build_query($requestParameters));
        $data = json_decode($response, true);
        if(!is_array($data)) {
            throw new \Exception('Oops.. Response is wrong: ' . print_r($response, true));
        }
        return $data['data'];
    }

    /**
     * @param string $authorizeUrl
     * @return InstProvider
     */
    public function setAuthorizeUrl($authorizeUrl)
    {
        $this->authorizeUrl = $authorizeUrl;
        return $this;
    }

    /**
     * @param string $userDataUrl
     * @return InstProvider
     */
    public function setUserDataUrl($userDataUrl)
    {
        $this->userDataUrl = $userDataUrl;
        return $this;
    }

    /**
     * @param string $accessTokenUrl
     * @return InstProvider
     */
    public function setAccessTokenUrl($accessTokenUrl)
    {
        $this->accessTokenUrl = $accessTokenUrl;
        return $this;
    }

    public function get($endpoint, $options, $useAccessToken = false)
    {
        // todo implement this
    }
}
