<?php

namespace Bnomei;

class Feed
{
    private static $indexname = null;
    private static $cache = null;
    private static function cache(): \Kirby\Cache\Cache
    {
        if (!static::$cache) {
            static::$cache = kirby()->cache('bnomei.feed');
        }
        // create new index table on new version of plugin
        if (!static::$indexname) {
            static::$indexname = 'index'.str_replace('.', '', kirby()->plugin('bnomei/feed')->version()[0]);
        }
        return static::$cache;
    }

    public static function flush()
    {
        return static::cache()->flush();
    }

    public static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function isXml($content)
    {
        $content = trim($content);
        if (empty($content)) {
            return false;
        }
        if (stripos($content, '<!DOCTYPE html>') !== false) {
            return false;
        }
        libxml_use_internal_errors(true);
        simplexml_load_string($content);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        return empty($errors);
    }

    public static function feed($pages, $options = [], $force = null)
    {
        if ($force == null && option('debug') && option('bnomei.feed.debugforce')) {
            $force = true;
        }
        $key = [];
        foreach ($pages as $p) {
            $key[] = $p->modified();
        }
        $key = md5(\implode(',', $key));
        $response = $force ? null : static::cache()->get($key);
        if (!$response) {
            $snippet = \Kirby\Toolkit\A::get($options, 'snippet', 'feed/rss');
            $response = snippet($snippet, static::data($pages, $options), true);
            static::cache()->set(
                $key,
                $response,
                option('bnomei.feed.expires')
            );
        }
        return $response;
    }

    public static function data($pages, $options = [])
    {
        $defaults = array(
            'url'         => site()->url(),
            'title'       => 'Feed',
            'description' => '',
            'link'        => site()->url(),
            'datefield'   => 'date',
            'textfield'   => 'text',
            'modified'    => time(),
        );
        $options = array_merge($defaults, $options);

        $items = $pages->sortBy($options['datefield'], 'desc');
        $options['items'] = $items;

        $options['link']  = url($options['link']);

        if ($options['datefield'] == 'modified') {
            $options['modified'] = $items->first()->modified();
        } else {
            $options['modified'] = $items->first()->date(null, $options['datefield']);
        }

        return $options;
    }
}
