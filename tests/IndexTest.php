<?php

use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        $this->setOutputCallback(function () {
        });
    }

    public function testFindsHomePage()
    {
        $response = kirby()->render('/');
        $this->assertIsInt($response->code(), 200);
        $this->assertStringContainsString('Home', $response->body());
    }

    public function testFindsFeedRoute()
    {
        $response = kirby()->render('/feed');
        $this->assertIsInt($response->code(), 200);
        $this->assertTrue('application/rss+xml' === $response->type());
    }

    public function testFindsFeedRouteJSON()
    {
        $response = kirby()->render('/feed-json');
        $this->assertIsInt($response->code(), 200);
        $this->assertTrue('application/json' === $response->type());
    }

    public function testFindsFeedRouteYAML()
    {
        $response = kirby()->render('/feed-yaml');
        $this->assertIsInt($response->code(), 200);
        $this->assertTrue('text/html' === $response->type());

        // TODO: kirby render sends text/html and not type set by plugin
        $this->assertFalse('text/plain' === $response->type());
    }
}
