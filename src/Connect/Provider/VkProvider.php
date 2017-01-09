<?php

namespace Benedya\Connect\Provider;

class VkProvider implements ProviderInterface
{
    protected $clientSecret;
    protected $requestParameters;
    protected $apiUrl = 'https://api.vk.com/method/';
    protected $authorizeUrl = 'https://oauth.vk.com/authorize';
    protected $accessTokenUrl = 'https://oauth.vk.com/access_token';
    protected $userDataEndpoint = 'users.get';
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
        $result = file_get_contents($this->accessTokenUrl . $this->buildQuery([
                'client_id' => $this->requestParameters['client_id'],
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->requestParameters['redirect_uri'],
                'code' => $code,
            ]));
        $this->accessTokenData = json_decode($result, true);
        if(!isset($this->accessTokenData['access_token'])) {
            throw new \Exception('Oops.. Can not get access token. (Response: ' . $result . ')');
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
        $result = $this->get($this->userDataEndpoint,  array_merge([
            'user_ids' => $this->accessTokenData['user_id'],
        ], $fields), true);
        return array_merge(array_pop($result), [
            'email' => $this->accessTokenData['email'],
        ]);
    }

    /**
     * @param $endpoint
     * @param $options
     * @param bool|false $useAccessToken
     * @return mixed|string
     * @throws \Exception
     */
    public function get($endpoint, $options, $useAccessToken = false)
    {
        if($useAccessToken) {
            $accessToken = $this->getAccessToken();
            $options = array_merge([
                'access_token' => $accessToken,
            ], $options);
        }
        $result = file_get_contents($this->apiUrl. $endpoint . $this->buildQuery($options));
        $data = json_decode($result, true);
        if(!isset($data['response'])) {
            throw new \Exception('Response is empty. (Response: ' . $result . ')');
        }
        return $data['response'];
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
     * @param string $accessTokenUrl
     * @return VkProvider
     */
    public function setAccessTokenUrl($accessTokenUrl)
    {
        $this->accessTokenUrl = $accessTokenUrl;
        return $this;
    }

    /**
     * @param string $authorizeUrl
     * @return VkProvider
     */
    public function setAuthorizeUrl($authorizeUrl)
    {
        $this->authorizeUrl = $authorizeUrl;
        return $this;
    }

    /**
     * @param string $apiUrl
     * @return VkProvider
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
        return $this;
    }

    /**
     * @param string $userDataEndpoint
     * @return VkProvider
     */
    public function setUserDataEndpoint($userDataEndpoint)
    {
        $this->userDataEndpoint = $userDataEndpoint;
        return $this;
    }
}
