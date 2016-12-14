<?php

namespace Benedya\Connect\Traits;

trait CurlToolsTrait
{
    /**
     * @param $url
     * @param array $fields
     * @return mixed
     * @throws \Exception
     */
    public function post($url, array $fields = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        if($fields) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec ($ch);
        if($res === false) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $res;
    }
}