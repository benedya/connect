<?php

namespace Benedya\Connect\Provider;

interface ProviderInterface
{
    function getAccessToken();
    function getUrl();
    function getUserData();
    function get($endpoint, $options, $useAccessToken = false);
}
