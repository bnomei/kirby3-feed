<?php

declare(strict_types=1);

namespace Bnomei;

final class Feed
{
    /**
     * @var array
     */
    private $options;

    /*
     * @var string
     */
    private $string;

    public function __construct(?\Kirby\Cms\Pages $pages = null, array $options = [])
    {
        $this->options = $this->optionsFromDefault($pages, $options);
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function option(?string $key = null)
    {
        if ($key) {
            return \Kirby\Toolkit\A::get($this->options, $key);
        }
        return $this->options;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->string;
    }

    /**
     * @param null $force
     * @return Feed
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function stringFromSnippet($force = null): Feed
    {
        $force = $force ? $force : (option('debug') && option('bnomei.feed.debugforce'));
        $key = $this->modifiedHashFromKeys();

        $string = null;
        if (! $force) {
            $string = kirby()->cache('bnomei.feed')->get($key);
        }
        if ($string) {
            $this->string = $string;
            return $this;
        }

        $string = trim(snippet(
            \Kirby\Toolkit\A::get($this->options, 'snippet'),
            $this->options,
            true
        ));

        kirby()->cache('bnomei.feed')->set(
            $key,
            $string,
            intval(option('bnomei.feed.expires'))
        );

        $this->string = $string;
        return $this;
    }

    /**
     * @return string
     * @throws \Kirby\Exception\DuplicateException
     */
    private function modifiedHashFromKeys(): string
    {
        $keys = [
            kirby()->language() ? kirby()->language()->code() : '',
            str_replace('.', '', kirby()->plugin('bnomei/feed')->version()[0]),
            \Kirby\Toolkit\A::get($this->options, 'snippet'),
        ];
        $pages = \Kirby\Toolkit\A::get($this->options, 'items');
        foreach ($pages as $page) {
            $keys[] = $page->modified();
        }
        return sha1(implode(',', $keys));
    }

    /**
     * @param \Kirby\Cms\Pages|null $pages
     * @param array $options
     * @return array
     */
    public function optionsFromDefault(?\Kirby\Cms\Pages $pages = null, $options = []): array
    {
        $defaults = [
            'url' => site()->url(),
            'feedurl' => site()->url() . '/feed/',
            'title' => 'Feed',
            'description' => '',
            'link' => site()->url(),
            'urlfield' => 'url',
            'datefield' => 'date',
            'textfield' => 'text',
            'modified' => time(),
            'snippet' => 'feed/rss',
            'mime' => null,
            'sort' => true,
        ];
        $options = array_merge($defaults, $options);

        $items = $pages ?? null;
        if ($items && $options['sort'] === true) {
            $items = $items->sortBy($options['datefield'], 'desc');
        }
        $options['items'] = $items;
        $options['link'] = url($options['link']);

        if ($items && $options['datefield'] === 'modified') {
            $options['modified'] = $items->first()->modified('r', 'date');
        } elseif ($items) {
            $datefieldName = $options['datefield'];
            $options['modified'] = date('r', $items->first()->{$datefieldName}()->toTimestamp());
        } else {
            $options['modified'] = site()->homePage()->modified();
        }

        return $options;
    }

    /**
     * @return \Kirby\Http\Response
     */
    public function response(): \Kirby\Http\Response
    {
        $mime = \Kirby\Toolkit\A::get($this->options, 'mime');
        $snippet = \Kirby\Toolkit\A::get($this->options, 'snippet');

        $allMimeTypes = \Kirby\Toolkit\Mime::types();
        $mime = array_search($mime, $allMimeTypes);
        if ($mime !== false) {
            return new \Kirby\Http\Response($this->string, $mime);
        } elseif ($snippet === 'feed/json' || \Bnomei\Feed::isJson($this->string)) {
            return new \Kirby\Http\Response($this->string, 'json');
        } elseif ($snippet === 'feed/rss' || \Bnomei\Feed::isXml($this->string)) {
            return new \Kirby\Http\Response($this->string, 'rss');
        }
        return new \Kirby\Http\Response('Error: Feed Response', null, 500);
    }

    /**
     * @param \Kirby\Cms\Pages $pages
     * @param array $options
     * @param null $force
     * @return \Kirby\Http\Response
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function feed(\Kirby\Cms\Pages $pages, array $options = [], $force = null): \Kirby\Http\Response
    {
        $feed = new self($pages, $options);
        return $feed->stringFromSnippet($force)->response();
    }

    /**
     * @param $string
     * @return bool
     */
    public static function isJson($string): bool
    {
        json_decode($string);
        $lastError = json_last_error();
        return $lastError === JSON_ERROR_NONE;
    }

    /**
     * @param $content
     * @return bool
     */
    public static function isXml($content): bool
    {
        if (! $content) {
            return false;
        }
        if (is_string($content) && strlen(trim($content)) === 0) {
            return false;
        }
        if (stripos($content, '<!DOCTYPE html>') !== false) {
            return false;
        }
        libxml_use_internal_errors(true);
        simplexml_load_string(trim($content));
        $errors = libxml_get_errors();
        libxml_clear_errors();
        return count($errors) === 0;
    }
}
