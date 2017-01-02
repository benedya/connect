<?php

namespace Benedya\Connect;

class ProviderFactory
{
    /**
     * @param $provider
     * @param $secret
     * @param $options
     * @return \Benedya\Connect\Provider\ProviderInterface
     * @throws \Exception
     */
    public static function create($provider, $secret, $options)
    {
        $class = "\\Benedya\\Connect\\Provider\\".ucfirst(strtolower($provider))."Provider";
        if(!class_exists($class)) {
            throw new \Exception('Class "'.$class.'" not found.');
        }
        return new $class(
            $secret,
            $options
        );
    }
}
