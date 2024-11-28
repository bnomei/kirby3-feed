<?php

use Kirby\Cms\Pages;
use Kirby\Http\Response;

@include_once __DIR__.'/vendor/autoload.php';

if (! function_exists('feed')) {
    function feed(Pages|Closure $pages, array $options = []): Response
    {
        $response = \Bnomei\Feed::feed($pages, $options);
        kirby()->response()->type($response->type());

        return $response;
    }
}

if (! function_exists('sitemap')) {
    function sitemap(Pages|Closure $pages, array $options = []): Response
    {
        $response = \Bnomei\Feed::feed($pages, $options + [
            'feedurl' => site()->url().'/sitemap.xml',
            'snippet' => 'feed/sitemap',
            'dateformat' => 'c',
            'datefield' => 'modified',
        ]);
        kirby()->response()->type($response->type());

        return $response;
    }
}

Kirby::plugin('bnomei/feed', [
    'options' => [
        'defaults' => function () {
            $site = site();

            return [
                // atom/json/rss
                'url' => $site->url(),
                'feedurl' => $site->url().'/feed/',
                'title' => 'Feed',
                'description' => '',
                'link' => $site->url(),
                'urlfield' => 'url',
                'titlefield' => 'title',
                'idfield' => 'id',
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
        },
        'cache' => true,
        'expires' => (60 * 24 * 7), // minutes
    ],
    'snippets' => [
        'feed/atom' => __DIR__.'/snippets/feed/atom.php',
        'feed/json' => __DIR__.'/snippets/feed/json.php',
        'feed/rss' => __DIR__.'/snippets/feed/rss.php',
        'feed/sitemap' => __DIR__.'/snippets/feed/sitemap.php',
        'feed/sitemapxsl' => __DIR__.'/snippets/feed/sitemap.xsl.php',
    ],
    'pagesMethods' => [
        'feed' => function (array $options = []): Response {
            return feed(fn () => $this, $options);
        },
        'sitemap' => function (array $options = []): Response {
            return sitemap(fn () => $this, $options);
        },
    ],
    'hooks' => [
        'page.*:after' => function ($event, $page) {
            if ($event->action() !== 'render') {
                \Bnomei\Feed::flush();
            }
        },
        'file.*:after' => function ($event, $file) {
            if ($event->action() !== 'render') {
                \Bnomei\Feed::flush();
            }
        },
    ],
]);
