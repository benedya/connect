<?php

namespace Benedya\Connect\Provider;

interface ProviderInterface
{
    function getAccessToken($code = false);
    function getUrl();
    function getUserData();
    function get($endpoint, $options, $useAccessToken = false);
}
