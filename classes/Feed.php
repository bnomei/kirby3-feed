<?php

declare(strict_types=1);

namespace Bnomei;

use Closure;
use Kirby\Cms\Pages;
use Kirby\Content\Field;
use Kirby\Filesystem\Mime;
use Kirby\Http\Response;
use Kirby\Toolkit\A;

class Feed
{
    private array $options;

    private string $string;

    public function __construct(Pages|Closure $pages, array $options = [])
    {
        $this->options = array_merge((array) option('bnomei.feed.defaults')(), ['items' => $pages], $options); // @phpstan-ignore-line

        // loading of items is delayed in case we have a cache hit, see loadItems()

        if (option('debug')) {
            static::flush();
        }
    }

    public function __toString(): string
    {
        return $this->string;
    }

    public function stringFromSnippet(): Feed
    {
        $key = $this->modifiedHashFromKeys(); // uses options but not items
        $this->string = kirby()->cache('bnomei.feed')->get($key, '');
        if (! empty($this->string)) {
            return $this;
        }

        // cache miss, load items and dynamic options now
        $this->loadItems();
        $this->dynamicOptions();

        // render snippet with items in options
        $this->string = trim(strval(snippet(A::get($this->options, 'snippet'), $this->options, true)));

        kirby()->cache('bnomei.feed')->set($key, $this->string, intval(option('bnomei.feed.expires')));

        return $this;
    }

    private function modifiedHashFromKeys(): string
    {
        $keys = [
            kirby()->language() ? kirby()->language()->code() : '',
            str_replace('.', '', kirby()->plugin('bnomei/feed')?->version()[0] ?? '0.0.0'),
            A::get($this->options, 'snippet'),
            A::get($this->options, 'feedurl'), // this is unique enough
        ];

        // not performant and does not work for lazy loaded pages via closure anymore
        /*
        foreach (A::get($this->options, 'items', []) as $page) {
            $keys[] = $page?->modified();
        }
        */

        return strval(crc32(implode(',', $keys)));
    }

    private function loadItems(): void
    {
        $items = A::get($this->options, 'items');
        if ($items instanceof Closure) {
            $items = $items();
        }
        if (empty($items) || ! ($items instanceof Pages)) {
            throw new \Exception('Feed: items not found or not a Pages Collection.');
        }
        /** @var Pages $items */
        $this->options['items'] = $items;

        if ($this->options['sort'] === true) {
            $items = $items->sortBy($this->options['datefield'], 'desc');
        }
        $this->options['items'] = $items;
    }

    private function dynamicOptions(): void
    {
        $this->options['link'] = url($this->options['link']);

        /** @var Pages $items */
        $items = $this->options['items'];
        if ($items->count()) {
            $modified = $items->first()->modified($this->options['dateformat'], 'date');
            $this->options['modified'] = $modified;

            $datefield = $items->first()->{$this->options['datefield']}();
            if ($datefield instanceof Field && $datefield->isNotEmpty()) {
                $this->options['date'] = $datefield->toDate($this->options['dateformat']);
            }
        } else {
            $this->options['modified'] = site()->homePage()?->modified();
        }
    }

    public function response(): Response
    {
        $snippet = A::get($this->options, 'snippet');
        $mime = Mime::fromExtension(A::get($this->options, 'mime', ''));

        $response = null;
        if ($mime !== null) {
            $response = new Response($this->string, $mime);
        } elseif ($snippet === 'feed/sitemap' && Feed::isXml($this->string)) {
            $response = new Response($this->string, 'xml');
        } elseif ($snippet === 'feed/atom' && Feed::isXml($this->string)) {
            $response = new Response($this->string, 'xml');
        } elseif ($snippet === 'feed/json' && Feed::isJson($this->string)) {
            $response = new Response($this->string, 'json');
        } elseif ($snippet === 'feed/rss' && Feed::isXml($this->string)) {
            $response = new Response($this->string, 'rss');
        }

        return $response ?? new Response('Error: Feed Response', null, 500);
    }

    public static function feed(Pages|Closure $pages, array $options = []): Response
    {
        $feed = new self($pages, $options);

        return $feed->stringFromSnippet()->response();
    }

    public static function flush(): bool
    {
        return kirby()->cache('bnomei.feed')->flush();
    }

    public static function isJson(string $string): bool
    {
        $result = json_decode($string);
        $lastError = json_last_error();

        return $result && $lastError === JSON_ERROR_NONE;
    }

    public static function isXml(?string $content): bool
    {
        if (empty(trim($content ?? ''))) {
            return false;
        }
        if ($content && stripos($content, '<!DOCTYPE html>') !== false) {
            return false;
        }
        libxml_use_internal_errors(true);
        simplexml_load_string(trim($content ?? ''));
        $errors = libxml_get_errors();
        libxml_clear_errors();

        return count($errors) === 0;
    }
}
