<?php

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
        ];

        $refined = $refinery->refine($raw);

        $this->assertArrayHasKey('baz', $refined);
        $this->assertArrayNotHasKey('foo1', $refined);
        $this->assertContains('foo', $refined);
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

        $refined = $refinery->refine($raw);

        $this->assertArrayHasKey('baz', $refined);
        $this->assertContains('foo', $refined);
        $this->assertArrayNotHasKey('foo1', $refined);
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
            ],
            [
                'foo' => 'foo',
                'bar' => 'bar',
                'foo1' => 'foo1',
                'bar2' => 'bar2',
            ],
            [
                'foo' => 'foo',
                'bar' => 'bar',
                'foo1' => 'foo1',
                'bar2' => 'bar2',
            ],
        ];

        $refined = $refinery->refine($raw);

        $this->assertCount(3, $refined);
        $this->assertArrayHasKey('baz', $refined[0]);
        $this->assertArrayNotHasKey('foo1', $refined[0]);
        $this->assertContains('foo', $refined[0]);
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

        $rawCollection = [$raw, $raw, $raw];

        $refined = $refinery->refine($rawCollection);

        $this->assertCount(3, $refined);
        $this->assertArrayHasKey('baz', $refined[0]);
        $this->assertContains('foo', $refined[0]);
        $this->assertArrayNotHasKey('foo1', $refined[0]);
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
        ];

        $refined = $refinery->bring('fooBarAttach', 'fooBarEmbed', 'fooBarNest')->refine($raw);

        $this->assertArrayHasKey('baz', $refined);
        $this->assertArrayNotHasKey('foo1', $refined);
        $this->assertContains('foo', $refined);
        $this->assertArrayHasKey('fooBarAttach', $refined);
        $this->assertArrayHasKey('fooBarEmbed', $refined);
        $this->assertArrayHasKey('fooBarNest', $refined);
        $this->assertArrayHasKey('foobar', $refined['fooBarNest']);
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

        $refined = $refinery->bring('fooBarAttach', 'fooBarEmbed', 'fooBarNest')->refine($raw);

        $this->assertArrayHasKey('baz', $refined);
        $this->assertContains('foo', $refined);
        $this->assertArrayNotHasKey('foo1', $refined);
        $this->assertArrayHasKey('fooBarAttach', $refined);
        $this->assertArrayHasKey('fooBarEmbed', $refined);
        $this->assertArrayHasKey('fooBarNest', $refined);
        $this->assertArrayHasKey('foobar', $refined['fooBarNest']);
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
        ];

        $refinery->bring('notSet')->refine($raw);
    }
}