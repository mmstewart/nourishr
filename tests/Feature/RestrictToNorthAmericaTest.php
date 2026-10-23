<?php

namespace Tests\Feature;

use Tests\TestCase;

class RestrictToNorthAmericaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app['env'] = 'production';
    }

    public function test_visitors_outside_north_america_are_blocked(): void
    {
        $this->get('/up', ['x-vercel-ip-country' => 'GB'])->assertForbidden();
    }

    public function test_visitors_without_a_country_header_are_blocked(): void
    {
        $this->get('/up')->assertForbidden();
    }

    public function test_us_and_canadian_visitors_are_allowed(): void
    {
        $this->get('/up', ['x-vercel-ip-country' => 'US'])->assertOk();
        $this->get('/up', ['x-vercel-ip-country' => 'CA'])->assertOk();
    }
}