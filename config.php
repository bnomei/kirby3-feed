<?php

Kirby::plugin('bnomei/feed', [
    'options' => [
        'cache' => true,
        'debugforce' => true,
        'expires' => (60*24*7), // minutes
    ],
    'snippets' => [
        'feed/rss' => __DIR__ . '/snippets/feed/rss.php',
        'feed/json' => __DIR__ . '/snippets/feed/json.php',
    ],
    'pagesMethods' => [ // PAGES not PAGE
        'feed' => function ($options = [], $force = null) {
            $string = \Bnomei\Feed::feed($this, $options, $force);

            if(\Bnomei\Feed::isJson($string)) {
                return new Response($string, 'application/json');

            } else if (\Bnomei\Feed::isXml($string)) {
                return new Response($string, 'text/xml');
            }
            return $return;
        },
    ],
]);
