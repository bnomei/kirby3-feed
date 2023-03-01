<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bnomei/feed', [
    'options' => [
        'cache' => true,
        'debugforce' => true,
        'expires' => (60*24*7), // minutes
    ],
    'snippets' => [
        'feed/rss' => __DIR__ . '/snippets/feed/rss.php',
        'feed/json' => __DIR__ . '/snippets/feed/json.php',
        'feed/sitemap' => __DIR__ . '/snippets/feed/sitemap.php',
        'feed/sitemapxsl' => __DIR__ . '/snippets/feed/sitemap.xsl.php',
    ],
    'pagesMethods' => [ // PAGES not PAGE
        'feed' => function ($options = [], $force = null) {
            $response = \Bnomei\Feed::feed($this, $options, $force);
            kirby()->response()->type($response->type());
            return $response;
        },
        'sitemap' => function ($options = [], $force = null) {
            if (!A::get($options, 'snippet')) {
                $options['snippet'] = 'feed/sitemap';
                $options['dateformat'] = 'c';
                $options['datefield'] = 'modified';
            }
            $response = \Bnomei\Feed::feed($this->filterBy('intendedTemplate', '!=', 'error'), $options, $force);
            kirby()->response()->type($response->type());
            return $response;
        },
    ],
]);
