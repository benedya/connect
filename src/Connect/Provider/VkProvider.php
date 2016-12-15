<?php

namespace Benedya\Connect\Provider;

class VkProvider implements ProviderInterface
{
    protected $clientSecret;
    protected $requestParameters;
    protected $apiUrl = 'https://api.vk.com/method/';
    protected $authorizeUrl = 'https://oauth.vk.com/authorize';
    protected $accessTokenUrl = 'https://oauth.vk.com/access_token';
    protected $userDataUrl = 'https://api.vk.com/method/users.get';
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
        $data = file_get_contents($this->accessTokenUrl . '?' . http_build_query([
                'client_id' => $this->requestParameters['client_id'],
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->requestParameters['redirect_uri'],
                'code' => $code,
            ]));
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
            'user_ids' => $this->accessTokenData['user_id'],
            'access_token' => $accessToken,
        ], $fields);
        $data = file_get_contents($this->userDataUrl . '?' . http_build_query($requestParameters));
        $data = json_decode($data, true);
        if(!isset($data['response'])) {
            throw new \Exception('Response is empty ' . print_r($data, true));
        }
        return array_merge(array_pop($data['response']), [
            'user_id' => $this->accessTokenData['user_id'],
            'email' => $this->accessTokenData['email'],
        ]);
    }

    /**
     * @param string $userDataUrl
     * @return VkProvider
     */
    public function setUserDataUrl($userDataUrl)
    {
        $this->userDataUrl = $userDataUrl;
        return $this;
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
        $data = file_get_contents($this->apiUrl. $endpoint . '?' . http_build_query($options));
        $data = json_decode($data, true);
        if(!isset($data['response'])) {
            throw new \Exception('Response is empty ' . print_r($data, true));
        }
        return $data['response'];
    }
}
