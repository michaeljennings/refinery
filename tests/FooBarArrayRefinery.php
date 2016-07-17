<?php

use Michaeljennings\Refinery\Refinery;

class FooBarArrayRefinery extends Refinery
{

    /**
     * Set the template the refinery will use for each item passed to it
     *
     * @param mixed $item
     * @return mixed
     */
    protected function setTemplate($item)
    {
        return [
            'foobar' => $item['foobar'],
        ];
    }
}