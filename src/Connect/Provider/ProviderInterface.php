<?php

namespace Benedya\Connect\Provider;

interface ProviderInterface
{
    function getData();
    function getUrl();
    function handleCode();
}
