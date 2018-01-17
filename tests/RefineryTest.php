<?php

namespace Michaeljennings\Refinery\Tests;

use PHPUnit_Framework_TestCase;
use stdClass;

class RefineryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_refines_an_array()
    {
        $refinery = new ArrayRefinery();

        $raw = [
            'foo' => 'foo',
            'bar' => 'bar',
            'foo1' => 'foo1',
            'bar2' => 'bar2',
            'fooBarAttach' => [
                'foobar' => 'foobar',
            ],
            'fooBarEmbed' => [
                'foobar' => 'foobar',
            ],
            'foobar' => [
                'foobar' => 'foobar',
            ],
        ];

        $refined = $refinery->with(['quux' => 'quux1'])->refine($raw);

        $this->assertArrayHasKey('baz', $refined);
        $this->assertArrayNotHasKey('foo1', $refined);
        $this->assertContains('foo', $refined);
        $this->assertContains('quux1', $refined);
    }

    /**
     * @test
     */
    public function it_refines_an_object()
    {
        $refinery = new ObjectRefinery();

        $raw = new stdClass();

        $raw->foo = 'foo';
        $raw->bar = 'bar';
        $raw->foo1 = 'foo1';
        $raw->bar1 = 'bar1';
        $raw->fooBarAttach = new stdClass();
        $raw->fooBarAttach->foobar = 'foobar';
        $raw->fooBarEmbed = new stdClass();
        $raw->fooBarEmbed->foobar = 'foobar';
        $raw->foobar = new stdClass();
        $raw->foobar->foobar = 'foobar';

        $refined = $refinery->with(['quux' => 'quux1'])->refine($raw);

        $this->assertArrayHasKey('baz', $refined);
        $this->assertContains('foo', $refined);
        $this->assertArrayNotHasKey('foo1', $refined);
        $this->assertContains('quux1', $refined);
    }

    /**
     * @test
     */
    public function it_refines_multiple_arrays()
    {
        $refinery = new ArrayRefinery();

        $raw = [
            [
                'foo' => 'foo',
                'bar' => 'bar',
                'foo1' => 'foo1',
                'bar2' => 'bar2',
                'fooBarAttach' => [
                    'foobar' => 'foobar',
                ],
                'fooBarEmbed' => [
                    'foobar' => 'foobar',
                ],
                'foobar' => [
                    'foobar' => 'foobar',
                ],
            ],
            [
                'foo' => 'foo',
                'bar' => 'bar',
                'foo1' => 'foo1',
                'bar2' => 'bar2',
                'fooBarAttach' => [
                    'foobar' => 'foobar',
                ],
                'fooBarEmbed' => [
                    'foobar' => 'foobar',
                ],
                'foobar' => [
                    'foobar' => 'foobar',
                ],
            ],
            [
                'foo' => 'foo',
                'bar' => 'bar',
                'foo1' => 'foo1',
                'bar2' => 'bar2',
                'fooBarAttach' => [
                    'foobar' => 'foobar',
                ],
                'fooBarEmbed' => [
                    'foobar' => 'foobar',
                ],
                'foobar' => [
                    'foobar' => 'foobar',
                ],
            ],
        ];

        $refined = $refinery->with(['quux' => 'quux1'])->refine($raw);

        $this->assertCount(3, $refined);
        $this->assertArrayHasKey('baz', $refined[0]);
        $this->assertArrayNotHasKey('foo1', $refined[0]);
        $this->assertContains('foo', $refined[0]);
        $this->assertContains('quux1', $refined[0]);
    }

    /**
     * @test
     */
    public function it_refines_multiple_objects()
    {
        $refinery = new ObjectRefinery();

        $raw = new stdClass();

        $raw->foo = 'foo';
        $raw->bar = 'bar';
        $raw->foo1 = 'foo1';
        $raw->bar1 = 'bar1';
        $raw->fooBarAttach = new stdClass();
        $raw->fooBarAttach->foobar = 'foobar';
        $raw->fooBarEmbed = new stdClass();
        $raw->fooBarEmbed->foobar = 'foobar';
        $raw->foobar = new stdClass();
        $raw->foobar->foobar = 'foobar';

        $rawCollection = [$raw, $raw, $raw];

        $refined = $refinery->with(['quux' => 'quux1'])->refine($rawCollection);

        $this->assertCount(3, $refined);
        $this->assertArrayHasKey('baz', $refined[0]);
        $this->assertContains('foo', $refined[0]);
        $this->assertArrayNotHasKey('foo1', $refined[0]);
        $this->assertContains('quux1', $refined[0]);
    }

    /**
     * @test
     */
    public function it_embeds_into_the_refined_array()
    {
        $refinery = new ArrayRefinery();

        $raw = [
            'foo' => 'foo',
            'bar' => 'bar',
            'foo1' => 'foo1',
            'bar2' => 'bar2',
            'fooBarAttach' => [
                'foobar' => 'foobar',
            ],
            'fooBarEmbed' => [
                'foobar' => 'foobar',
            ],
            'foobar' => [
                'foobar' => 'foobar',
            ],
        ];

        $refined = $refinery->bring('fooBarAttach', 'fooBarEmbed', 'fooBarNest')->with(['quux' => 'quux1'])->refine($raw);

        $this->assertArrayHasKey('baz', $refined);
        $this->assertArrayNotHasKey('foo1', $refined);
        $this->assertContains('foo', $refined);
        $this->assertArrayHasKey('fooBarAttach', $refined);
        $this->assertArrayHasKey('fooBarEmbed', $refined);
        $this->assertArrayHasKey('fooBarNest', $refined);
        $this->assertArrayHasKey('foobar', $refined['fooBarNest']);
        $this->assertContains('quux1', $refined);
    }

    /**
     * @test
     */
    public function it_retains_keys_where_appropriate()
    {
        $refinery = new NamedArrayRefinery();

        $raw = [
            'namedKey' => [
                'foo' => [
                    'bar' => 'any number of things',
                ],
            ],
        ];

        // Without Key
        $refined = $refinery->refine($raw);
        $this->assertArrayNotHasKey('namedKey', $refined);

        $retainKey = false;
        $refined = $refinery->refine($raw, $retainKey);
        $this->assertArrayNotHasKey('namedKey', $refined);

        // With Key
        $retainKey = true;
        $refined = $refinery->refine($raw, $retainKey);
        $this->assertArrayHasKey('namedKey', $refined);
    }

    /**
     * @test
     */
    public function it_embeds_from_an_object()
    {
        $refinery = new ObjectRefinery();

        $raw = new stdClass();

        $raw->foo = 'foo';
        $raw->bar = 'bar';
        $raw->foo1 = 'foo1';
        $raw->bar1 = 'bar1';
        $raw->fooBarAttach = new stdClass();
        $raw->fooBarAttach->foobar = 'foobar';
        $raw->fooBarEmbed = new stdClass();
        $raw->fooBarEmbed->foobar = 'foobar';
        $raw->foobar = new stdClass();
        $raw->foobar->foobar = 'foobar';

        $refined = $refinery->bring('fooBarAttach', 'fooBarEmbed', 'fooBarNest')->with(['quux' => 'quux1'])->refine($raw);

        $this->assertArrayHasKey('baz', $refined);
        $this->assertContains('foo', $refined);
        $this->assertArrayNotHasKey('foo1', $refined);
        $this->assertArrayHasKey('fooBarAttach', $refined);
        $this->assertArrayHasKey('fooBarEmbed', $refined);
        $this->assertArrayHasKey('fooBarNest', $refined);
        $this->assertArrayHasKey('foobar', $refined['fooBarNest']);
        $this->assertContains('quux1', $refined);
    }

    /**
     * @test
     * @expectedException \Michaeljennings\Refinery\Exceptions\AttachmentClassNotFound
     */
    public function it_throws_an_exception_if_attachment_class_is_not_found()
    {
        $refinery = new ArrayRefinery();

        $raw = [
            'foo' => 'foo',
            'bar' => 'bar',
            'foo1' => 'foo1',
            'bar2' => 'bar2',
            'fooBarAttach' => [
                'foobar' => 'foobar',
            ],
            'fooBarEmbed' => [
                'foobar' => 'foobar',
            ],
            'foobar' => [
                'foobar' => 'foobar',
            ],
        ];

        $refinery->bring('classDoesNotExist')->refine($raw);
    }

    /**
     * @test
     * @expectedException \Michaeljennings\Refinery\Exceptions\RefineryMethodNotFound
     */
    public function it_throws_an_exception_if_the_attachment_has_not_been_set()
    {
        $refinery = new ArrayRefinery();

        $raw = [
            'foo' => 'foo',
            'bar' => 'bar',
            'foo1' => 'foo1',
            'bar2' => 'bar2',
            'fooBarAttach' => [
                'foobar' => 'foobar',
            ],
            'fooBarEmbed' => [
                'foobar' => 'foobar',
            ],
            'foobar' => [
                'foobar' => 'foobar',
            ],
        ];

        $refinery->bring('notSet')->refine($raw);
    }

    /**
     * @test
     */
    public function it_attaches_a_raw_attachment()
    {
        $refinery = new ArrayRefinery();

        $raw = [
            'foo' => 'foo',
            'bar' => 'bar',
            'foo1' => 'foo1',
            'bar2' => 'bar2',
        ];

        $refined = $refinery->bring('fooBarRaw')->with(['quux' => 'quux1'])->refine($raw);

        $this->assertArrayHasKey('fooBarRaw', $refined);
        $this->assertEquals('foobar', $refined['fooBarRaw']);
    }

    /**
     * @test
     */
    public function it_attaches_a_raw_attachment_from_an_object()
    {
        $refinery = new ObjectRefinery();

        $raw = new stdClass();

        $raw->foo = 'foo';
        $raw->bar = 'bar';
        $raw->foo1 = 'foo1';
        $raw->bar1 = 'bar1';

        $refined = $refinery->bring('fooBarRaw')->with(['quux' => 'quux1'])->refine($raw);

        $this->assertArrayHasKey('fooBarRaw', $refined);
        $this->assertEquals('foobar', $refined['fooBarRaw']);
    }
}