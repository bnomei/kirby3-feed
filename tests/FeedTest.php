<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Bnomei\Feed;
use PHPUnit\Framework\TestCase;

class FeedTest extends TestCase
{
    public function setUp(): void
    {
        kirby()->cache('bnomei.feed')->flush();
    }

    public function testConstruct()
    {
        $feed = new Feed(null);
        $this->assertInstanceOf(Feed::class, $feed);
    }

    public function testPagesAndXML()
    {
        $feed = new Feed(page('blog')->children());
        $options = $feed->option();
        $this->assertIsArray($options);
        $this->assertCount(11, $options['items']);
        $this->assertTrue($options['datefield'] === 'date');

        $this->assertIsArray($feed->option());
        $this->assertEquals(kirby()->site()->url(), $feed->option('url'));
        $this->assertNull($feed->option('does not exist'));

        // test sorting works
        $this->assertTrue($options['items']->first()->title()->value() === 'Aardvark');

        // create cache since setup flushed it
        $xmlString = (string) $feed->stringFromSnippet();
        $this->assertTrue(Feed::isXml($xmlString));
        $this->assertStringStartsWith('<?xml version="1.0" encoding="utf-8"?>', $xmlString);

        // read from cache
        $xmlString = (string) $feed->stringFromSnippet();
        $this->assertTrue(Feed::isXml($xmlString));
        $this->assertStringStartsWith('<?xml version="1.0" encoding="utf-8"?>', $xmlString);

        $xmlString = (string) $feed->stringFromSnippet(true);
        $this->assertTrue(Feed::isXml($xmlString));
        $this->assertStringStartsWith('<?xml version="1.0" encoding="utf-8"?>', $xmlString);
    }

    public function testPagesAndJSON()
    {
        $feed = new Feed(
            page('blog')->children()->flip()->limit(5),
            [
                'snippet' => 'feed/json',
                'sort' => true,
            ]
        );
        $options = $feed->option();
        $this->assertIsArray($options);
        $this->assertTrue($options['snippet'] === 'feed/json');
        $this->assertCount(5, $options['items']);
        $this->assertTrue($options['items']->first()->title()->value() === 'Gar');

        $jsonString = (string) $feed->stringFromSnippet(true);
        $this->assertTrue(Feed::isJson($jsonString));
        $this->assertStringStartsWith('{"version":"https:\/\/jsonfeed.org\/version\/1","title":"Feed",', $jsonString);
    }

    public function testNoSorting()
    {
        $feed = new Feed(
            page('blog')->children()->flip()->limit(5),
            [
                'sort' => false,
            ]
        );
        $options = $feed->option();
        $this->assertCount(5, $options['items']);
        $this->assertTrue($options['items']->first()->title()->value() === 'Kiwi');
    }

    public function testModified()
    {
        $feed = new Feed(
            page('blog')->children(),
            [
                'datefield' => 'modified',
            ]
        );
        $options = $feed->option();
        $this->assertIsArray($options);
        $this->assertTrue($options['datefield'] === 'modified');
    }

    public function testForcedMime()
    {
        $feed = new Feed(
            page('blog')->children(),
            [
                'feedurl' => '/feed-yaml',
                'snippet' => 'feed/yaml',
                'mime' => 'text/html',
            ]
        );
        $yamlString = (string) $feed->stringFromSnippet(true);
        $yaml = Kirby\Data\Yaml::decode($yamlString);
        $this->assertCount(6, $yaml);
        $this->assertArrayHasKey('title', $yaml);
        $this->assertArrayHasKey('items', $yaml);
        $this->assertCount(11, $yaml['items']);

        $response = $feed->response();
        $this->assertTrue($response->type() === 'text/html');
    }

    public function testInvalidOptions()
    {
        $feed = new Feed(
            page('blog')->children(),
            [
                'feedurl' => '/feed-invalid',
                'snippet' => 'feed/invalid',
                'mime' => 'invalid',
                'callable' => function() { return false; },
            ]
        );

        $response = $feed->stringFromSnippet(true)->response();
        $this->assertTrue($response->code() === 500);
        $this->assertTrue($response->type() === 'text/html');
        $this->assertTrue($response->body() === 'Error: Feed Response');
    }

    public function testStaticXMLHelper()
    {
        $this->assertFalse(Feed::isXml(null));
        $this->assertFalse(Feed::isXml(''));
        $this->assertFalse(Feed::isXml(' '));
        $this->assertFalse(Feed::isXml('<!DOCTYPE html><body></body>'));
    }
}
