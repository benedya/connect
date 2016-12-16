<?php

namespace Benedya\Connect\Provider;

class FbProvider implements ProviderInterface
{
    protected $clientSecret;
    protected $requestParameters;
    protected $apiUrl = 'https://graph.facebook.com/v2.8/';
    protected $authorizeUrl = 'https://www.facebook.com/v2.8/dialog/oauth';
    protected $accessTokenUrl = 'https://graph.facebook.com/v2.8/oauth/access_token';
    protected $userDataUrl = 'https://graph.facebook.com/v2.8/me';
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
            'access_token' => $accessToken,
        ], $fields);
        $response = file_get_contents($this->userDataUrl . '?' . http_build_query($requestParameters));
        $data = json_decode($response, true);
        if(!is_array($data)) {
            throw new \Exception('Oops.. Response is wrong: ' . print_r($response, true));
        }
        return $data;
    }

    /**
     * @param string $authorizeUrl
     * @return FbProvider
     */
    public function setAuthorizeUrl($authorizeUrl)
    {
        $this->authorizeUrl = $authorizeUrl;
        return $this;
    }

    /**
     * @param string $accessTokenUrl
     * @return FbProvider
     */
    public function setAccessTokenUrl($accessTokenUrl)
    {
        $this->accessTokenUrl = $accessTokenUrl;
        return $this;
    }

    /**
     * @param string $userDataUrl
     * @return FbProvider
     */
    public function setUserDataUrl($userDataUrl)
    {
        $this->userDataUrl = $userDataUrl;
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
        if(!is_array($data['data'])) {
            throw new \Exception('Response is empty ' . print_r($data, true));
        }
        return $data['data'];
    }
}
