<?php

namespace Michaeljennings\Refinery\Tests;

use Michaeljennings\Refinery\Refinery;

class ArrayRefinery extends Refinery
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
            'foo' => $item['foo'],
            'bar' => $item['bar'],
            'baz' => 'qux',
            'quux' => $this->quux
        ];
    }

    public function fooBarAttach()
    {
        return $this->attach(FooBarArrayRefinery::class);
    }

    public function fooBarEmbed()
    {
        return $this->embed(FooBarArrayRefinery::class);
    }

    public function fooBarNest()
    {
        return $this->nest(FooBarArrayRefinery::class, function($raw) {
            return $raw;
        });
    }

    public function fooBarRaw()
    {
        return $this->attach(function($item) {
            return $item['foo'] . $item['bar'];
        });
    }

    public function classDoesNotExist()
    {
        return $this->attach("NonExistentClass");
    }
}