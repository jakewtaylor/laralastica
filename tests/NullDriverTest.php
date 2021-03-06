<?php

namespace Michaeljennings\Laralastica\Tests;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Michaeljennings\Laralastica\Drivers\NullDriver;

class NullDriverTest extends TestCase
{
    /** @test */
    public function it_implements_the_driver_interface()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Driver::class, $driver);
    }

    /** @test */
    public function it_gets_an_empty_result_collection()
    {
        $driver = $this->makeDriver();
        $results = $driver->get('test', []);

        $this->assertEquals(0, $results->maxScore());
        $this->assertEquals(0, $results->totalHits());
        $this->assertEquals(0, $results->totalTime());
        $this->assertEquals(0, $results->count());
    }

    /** @test */
    public function it_gets_an_empty_length_aware_paginator()
    {
        $driver = $this->makeDriver();
        $results = $driver->paginate('test', [], 0, 15, 0);

        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
        $this->assertEquals(0, $results->count());
    }

    /** @test */
    public function it_returns_null_when_creating_a_common_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->common('foo', 'bar', 1.0));
    }

    /** @test */
    public function it_returns_null_when_creating_a_fuzzy_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->fuzzy('foo', 'bar'));
    }

    /** @test */
    public function it_returns_null_when_creating_a_match_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->match());
    }

    /** @test */
    public function it_returns_null_when_creating_a_match_phrase_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->matchPhrase());
    }

    /** @test */
    public function it_returns_null_when_creating_a_match_phrase_prefix_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->matchPhrasePrefix());
    }

    /** @test */
    public function it_returns_null_when_creating_a_match_all_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->matchAll());
    }

    /** @test */
    public function it_returns_null_when_creating_a_multi_match_all_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->multiMatch());
    }

    /** @test */
    public function it_returns_null_when_creating_a_query_string_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->queryString('testing'));
    }

    /** @test */
    public function it_returns_null_when_creating_a_range_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->range());
    }

    /** @test */
    public function it_returns_null_when_creating_a_regexp_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->regexp());
    }

    /** @test */
    public function it_returns_null_when_creating_a_term_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->term());
    }

    /** @test */
    public function it_returns_null_when_creating_a_terms_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->terms());
    }

    /** @test */
    public function it_returns_null_when_creating_a_wildcard_query()
    {
        $driver = $this->makeDriver();

        $this->assertNull($driver->wildcard());
    }

    /** @test */
    public function it_returns_the_driver_instance_when_adding_a_document()
    {
        $driver = $this->makeDriver();
        $result = $driver->add('test', '1', []);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Driver::class, $result);
    }

    /** @test */
    public function it_returns_the_driver_instance_when_adding_multiple_documents()
    {
        $driver = $this->makeDriver();
        $result = $driver->addMultiple('test', [
            '1' => [],
            '2' => [],
        ]);

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Driver::class, $result);
    }

    /** @test */
    public function it_returns_the_driver_instance_when_deleting_a_document()
    {
        $driver = $this->makeDriver();
        $result = $driver->delete('test', '1');

        $this->assertInstanceOf(\Michaeljennings\Laralastica\Contracts\Driver::class, $result);
    }

    protected function makeDriver()
    {
        return new NullDriver();
    }
}