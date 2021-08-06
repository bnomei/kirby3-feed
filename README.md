# Kirby 3 Feed and Sitemap

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-feed?color=ae81ff)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-feed?color=272822)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-feed)](https://travis-ci.com/bnomei/kirby3-feed)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-feed)](https://coveralls.io/github/bnomei/kirby3-feed) 
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-feed)](https://codeclimate.com/github/bnomei/kirby3-feed) 
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

Generate a RSS/JSON/Sitemap-Feed from a Pages-Collection.

## Commercial Usage

This plugin is free but if you use it in a commercial project please consider to 
- [make a donation 🍻](https://www.paypal.me/bnomei/3) or
- [buy me ☕](https://buymeacoff.ee/bnomei) or
- [buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/35731?link=1170)


## Similar Plugins

- [kirby3-xmlsitemap](https://github.com/omz13/kirby3-xmlsitemap)
- [kirby3-feeds](https://github.com/omz13/kirby3-feeds)

> both have not seen any updates since April 2019

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-feed/archive/master.zip) as folder `site/plugins/kirby3-feed` or
- `git submodule add https://github.com/bnomei/kirby3-feed.git site/plugins/kirby3-feed` or
- `composer require bnomei/kirby3-feed`

## Usage Feed

You can use this in a template for a dedicated feed page, in a template controller or a route.

```php
<?php
$options = [
    'title'       => 'Latest articles',
    'description' => 'Read the latest news about our company',
    'link'        => 'blog'
];
echo page('blog')->children()->listed()->flip()->limit(10)->feed($options);
```

**options array defaults**

If you use these defaults you need to provide the fields `date (type: date)` and `text (type: text)`.

```php
[
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
    'snippet' => 'feed/rss', // 'feed/json'
    'mime' => null,
    'sort' => true,
]
```

**virtual page in site/config.php**

```php
return [
    'routes' => [
        [
            'pattern' => 'feed',
            'method' => 'GET',
            'action'  => function () {
                $options = [
                    'title'       => 'Latest articles',
                    'description' => 'Read the latest news about our company',
                    'link'        => 'blog'
                ];
                $feed = page('blog')->children()->listed()->flip()->limit(10)->feed($options);
                return $feed;
            }
        ],
    ],
];
```

**HTML head element**

rss xml
```php
<link rel="alternate" type="application/rss+xml" title="Latest articles" href="<?= site()->url() ?>/feed"/>
```
and/or rss json
```php
<link rel="alternate" type="application/json" title="Latest articles" href="<?= site()->url() ?>/feed"/>
```

> TIP: Having multiple feed links is still valid html. So you can have both rss and json if you want and setup the routes properly.

**Sorting**

The Plugin applies a default sorting for the pages by date/modified in descending order (newest first). 

- If you do not want this you have to set the `datefield` setting to another Field name or PageMethod name.
- If you want to disable sorting by the plugin and add your own you can set the option `sort` to `false`.

**Pitfalls when presorting by date and limit**

Using `sortBy('date', 'desc')` will **not** yield expected results! In K3 sorting by date needs a callback.
```php
$feed = page('blog')->children()->listed()->sortBy(function ($page) {
 return $page->date()->toDate();
}, 'desc')->limit(10)->feed($options);
```

## Usage Sitemap

**options array defaults**

If you use these defaults you need to provide the fields `date (type: date)` and `text (type: text)`.

```php
[
    'urlfield' => 'url',
    'modified' => time(),
    'snippet' => 'feed/sitemap'
    'mime' => null,
    'sort' => true,
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
]
```

**virtual page in site/config.php**

```php
return [
    'routes' => [
        // ... other routes,
        [
            'pattern' => 'sitemap.xml',
            'method' => 'GET',
            'action'  => function () {
                $options = [
                    'images'       => false,
                    'videos'       => false,
                ];
                $feed = site()->index()->listed()->limit(50000)->sitemap($options);
                return $feed;
            }
        ],
    ],
];
```

**example for excluding pages from sitemap**

see [Kirby Docs -Filtering compendium](https://getkirby.com/docs/cookbook/content/filtering)

```php
$feed = site()->index()->listed()
    ->filterBy('template', '!=', 'excludeme')
    ->limit(50000)->sitemap($options);
```


## Settings

| bnomei.feed.              | Default        | Description               |            
|---------------------------|----------------|---------------------------|
| mime | `null` | to autodetect json or rss-xml otherwise enforce output with a certain [mime type](https://github.com/k-next/kirby/blob/master/src/Toolkit/Mime.php) |
| expires |`60*24*7` | in minutes |

> The plugin will automatically devalidate the cache if any of the Page-Objects were modified.

## Cache

If the **global** debug option is set to `true` the plugin will automatically flush its own cache and not write to the cache.

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-feed/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.

## Credits

based on K2 versions of
- https://github.com/getkirby-plugins/feed-plugin
- https://github.com/stefanzweifel/kirby-json-feed
