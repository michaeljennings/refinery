<?php

use Michaeljennings\Refinery\Refinery;

class ObjectRefinery extends Refinery
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
            'foo' => $item->foo,
            'bar' => $item->bar,
            'baz' => 'qux',
        ];
    }

    public function fooBarAttach()
    {
        return $this->attach(FooBarObjectRefinery::class);
    }

    public function fooBarEmbed()
    {
        return $this->embed(FooBarObjectRefinery::class);
    }

    public function fooBarNest()
    {
        return $this->nest(FooBarObjectRefinery::class, function($raw) {
            return $raw;
        });
    }
}