<?php

namespace Benedya\Connect\Provider;

use Benedya\Connect\Traits\CurlToolsTrait;

class InstagramProvider implements ProviderInterface
{
    use CurlToolsTrait;

    protected $clientSecret;
    protected $requestParameters;
    protected $apiUrl = 'https://api.instagram.com/v1/';
    protected $authorizeUrl = 'https://api.instagram.com/oauth/authorize/';
    protected $accessTokenUrl = 'https://api.instagram.com/oauth/access_token';
    protected $userDataEndpoint = 'users/self/';
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
     * @param bool|false $code
     * @return mixed
     * @throws \Exception
     */
    public function getAccessToken($code = false)
    {
        if(isset($this->accessTokenData['access_token'])) {
            return $this->accessTokenData['access_token'];
        }
        if(!$code) {
            $code = isset($_GET['code']) ? $_GET['code'] : null;
        }
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
     * @param array $options
     * @return string
     */
    public function buildQuery(array $options)
    {
        return '?' . http_build_query($options);
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
        $result = file_get_contents($this->apiUrl . $this->userDataEndpoint . $this->buildQuery($requestParameters));
        $data = json_decode($result, true);
        if(!is_array($data)) {
            throw new \Exception('Oops.. Response is wrong: (Response: ' . $result . ')');
        }
        return $data['data'];
    }

    /**
     * @param string $authorizeUrl
     * @return $this
     */
    public function setAuthorizeUrl($authorizeUrl)
    {
        $this->authorizeUrl = $authorizeUrl;
        return $this;
    }

    /**
     * @param string $accessTokenUrl
     * @return $this
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

    /**
     * @param string $apiUrl
     * @return $this
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
        return $this;
    }

    /**
     * @param string $userDataEndpoint
     * @return $this
     */
    public function setUserDataEndpoint($userDataEndpoint)
    {
        $this->userDataEndpoint = $userDataEndpoint;
        return $this;
    }
}
