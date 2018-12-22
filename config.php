<?php

Kirby::plugin('bnomei/feed', [
    'options' => [
        'cache' => true,
        'debugforce' => true,
        'expires' => (60*24*7), // minutes
        'mime' => null,
    ],
    'snippets' => [
        'feed/rss' => __DIR__ . '/snippets/feed/rss.php',
        'feed/json' => __DIR__ . '/snippets/feed/json.php',
    ],
    'pagesMethods' => [ // PAGES not PAGE
        'feed' => function ($options = [], $force = null) {
            $string = \Bnomei\Feed::feed($this, $options, $force);
            $mime = option('bnomei.feed.mime');
            $snippet = \Kirby\Toolkit\A::get($options, 'snippet');

            if ($mime) {
                return new Response($string, $mime);
            } elseif ($snippet == 'feed/json' || \Bnomei\Feed::isJson($string)) {
                return new Response($string, 'application/json');
            } elseif ($snippet == 'feed/rss' || \Bnomei\Feed::isXml($string)) {
                return new Response($string, 'application/rss+xml');
            }
            return $return;
        }
    ]
]);
