<?php

return [
    'debug' => true,
    'routes' => [
        [
            'pattern' => 'feed',
            'method' => 'GET',
            'action' => function () {
                $options = [
                    'title' => 'Latest articles',
                    'description' => 'Read the latest news about our company',
                    'link' => 'blog',
                ];
                $feed = page('blog')->children()->listed()->flip()->limit(10)->feed($options);
                return $feed;
            },
        ],
        [
            'pattern' => 'feed-json',
            'method' => 'GET',
            'action' => function () {
                $options = [
                    'title' => 'Latest articles',
                    'description' => 'Read the latest news about our company',
                    'link' => 'blog',
                    'feedurl' => '/feed-json',
                    'snippet' => 'feed/json'
                ];
                $feed = page('blog')->children()->listed()->flip()->limit(10)->feed($options);
                return $feed;
            },
        ],
        [
            'pattern' => 'feed-yaml',
            'method' => 'GET',
            'action' => function () {
                $options = [
                    'title' => 'Latest articles',
                    'description' => 'Read the latest news about our company',
                    'link' => 'blog',
                    'feedurl' => '/feed-yaml',
                    'snippet' => 'feed/yaml',
                    'mime' => 'yaml'
                ];
                $feed = page('blog')->children()->listed()->flip()->limit(10)->feed($options);
                return $feed;
            },
        ],
    ],
];
