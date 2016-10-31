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
            'quux' => $this->quux
        ];
    }

    public function fooBarAttach()
    {
        return $this->attach('FooBarObjectRefinery');
    }

    public function fooBarEmbed()
    {
        return $this->embed('FooBarObjectRefinery');
    }

    public function fooBarNest()
    {
        return $this->nest('FooBarObjectRefinery', function($raw) {
            return $raw;
        });
    }
}