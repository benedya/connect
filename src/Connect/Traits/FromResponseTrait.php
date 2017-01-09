<?php

namespace Benedya\Connect\Traits;

trait FromResponseTrait
{
    /**
     * @param array $data
     * @return $this
     */
    public static function fromResponse(array $data)
    {
        $instance = new static();
        foreach($data as $key => $value) {
            if(isset($instance->{$key})) {
                $instance->{$key} = $value;
            }
        }
        return $instance;
    }
}
