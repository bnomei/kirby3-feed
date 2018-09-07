<?php

Kirby::plugin('bnomei/feed', [
    'options' => [
        'cache' => true,
        'debugforce' => true,
        'expires' => (60*24), // minutes
    ],
    'snippets' => [
        'feed/rss' => __DIR__ . '/snippets/feed/rss.php',
        // 'feed/json' => __DIR__ . '/snippets/feed/json.php', // TODO: json feed
    ],
    'pagesMethods' => [ // PAGES not PAGE
        'feed' => function ($options = [], $force = null) {
            return \Bnomei\Feed::feed($this, $options, $force);
        },
    ],
]);
