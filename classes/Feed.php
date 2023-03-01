<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cms\Field;
use Kirby\Cms\Pages;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Response;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Mime;

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

    public function __construct(?Pages $pages = null, array $options = [])
    {
        $this->options = $this->optionsFromDefault($pages, $options);

        if (option('debug')) {
            kirby()->cache('bnomei.feed')->flush();
        }
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function option(?string $key = null)
    {
        if ($key) {
            return A::get($this->options, $key);
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
     * @throws InvalidArgumentException
     */
    public function stringFromSnippet($force = null): Feed
    {
        $force = $force ?? option('debug');
        $key = $this->modifiedHashFromKeys();

        $string = null;
        if (!$force) {
            $string = kirby()->cache('bnomei.feed')->get($key);
        }
        if ($string) {
            $this->string = $string;
            return $this;
        }

        $string = trim(snippet(
            A::get($this->options, 'snippet'),
            $this->options,
            true
        ));

        if (!option('debug')) {
            kirby()->cache('bnomei.feed')->set(
                $key,
                $string,
                intval(option('bnomei.feed.expires'))
            );
        }

        $this->string = $string;
        return $this;
    }

    /**
     * @return string
     * @throws DuplicateException
     */
    private function modifiedHashFromKeys(): string
    {
        $keys = [
            kirby()->language() ? kirby()->language()->code() : '',
            str_replace('.', '', kirby()->plugin('bnomei/feed')->version()[0]),
            A::get($this->options, 'snippet'),
        ];
        $pages = A::get($this->options, 'items');
        foreach ($pages as $page) {
            $keys[] = $page->modified();
        }
        return strval(crc32(implode(',', $keys)));
    }

    /**
     * @param Pages|null $pages
     * @param array $options
     * @return array
     */
    public function optionsFromDefault(?Pages $pages = null, $options = []): array
    {
        $defaults = [
            // json/rss
            'url' => site()->url(),
            'feedurl' => site()->url() . '/feed/',
            'title' => 'Feed',
            'description' => '',
            'link' => site()->url(),
            'urlfield' => 'url',
            'titlefield' => 'title',
            'datefield' => 'date',
            'textfield' => 'text',
            'modified' => time(),
            'snippet' => 'feed/rss',
            'mime' => null,
            'sort' => true,
            // sitemap
            'dateformat' => 'r', // rss => r, sitemap => c
            'xsl' => true,
            'images' => false,
            'imagesfield' => 'images',
            'imagetitlefield' => 'title',
            'imagecaptionfield' => 'caption',
            'imagelicensefield' => 'license',
            'videos' => false,
            'videosfield' => 'videos',
            'videotitlefield' => 'title',
            'videothumbnailfield' => 'thumbnail',
            'videodescriptionfield' => 'description',
            'videourlfield' => 'url',
        ];
        $options = array_merge($defaults, $options);

        $items = $pages ?? null;
        if ($items && $options['sort'] === true) {
            $items = $items->sortBy($options['datefield'], 'desc');
        }
        $options['items'] = $items;
        $options['link'] = url($options['link']);

        if ($items && $items->count()) {
            $modified = $items->first()->modified($options['dateformat'], 'date');
            $options['modified'] = $modified;

            $datefield = $items->first()->{$options['datefield']}();
            if ($datefield instanceof Field && $datefield->isNotEmpty()) {
                $options['date'] = date($options['dateformat'], $datefield->toTimestamp());
            }
        } else {
            $options['modified'] = site()->homePage()->modified();
        }

        return $options;
    }

    /**
     * @return Response
     */
    public function response(): Response
    {
        $snippet = A::get($this->options, 'snippet');
        $mime = Mime::fromExtension(A::get($this->options, 'mime', ''));

        if ($mime !== null) {
            return new Response($this->string, $mime);
        } elseif ($snippet === 'feed/sitemap' && Feed::isXml($this->string)) {
            return new Response($this->string, 'xml');
        } elseif ($snippet === 'feed/json' || Feed::isJson($this->string)) {
            return new Response($this->string, 'json');
        } elseif ($snippet === 'feed/rss' || Feed::isXml($this->string)) {
            return new Response($this->string, 'rss');
        }
        return new Response('Error: Feed Response', null, 500);
    }

    /**
     * @param Pages $pages
     * @param array $options
     * @param null $force
     * @return Response
     * @throws InvalidArgumentException
     */
    public static function feed(Pages $pages, array $options = [], $force = null): Response
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
        if (!$content) {
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
