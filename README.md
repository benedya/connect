## A lightweight library for oauth authorization(Facebook, Vkontakte, Instagram).


## How to use
```php
// crate provider
$clientSecret = 'client_secreat';
$clientId = 'client_id';
$redirectUri = 'http://example.com';
$provider = 'fb'; // fb, vk, inst
$provider = \Benedya\Connect\ProviderFactory::create($provider, $clientSecret, [
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri
]);
// authorization link
echo '<a href="'.$provier->getUrl().'">Authorization</a';
// process code
if($_GET['code']) {
    // get access token
    var_dump($proivder->getAccessToken());
    // get user's data
    var_dump($proivder->getUserData());
}
```
