# Kirby Atom/JSON/RSS-Feed and XML-Sitemap

[![Kirby 5](https://flat.badgen.net/badge/Kirby/5?color=ECC748)](https://getkirby.com)
![PHP 8.2](https://flat.badgen.net/badge/PHP/8.2?color=4E5B93&icon=php&label)
![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-feed?color=ae81ff&icon=github&label)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-feed?color=272822&icon=github&label)
[![Coverage](https://flat.badgen.net/codeclimate/coverage/bnomei/kirby3-feed?icon=codeclimate&label)](https://codeclimate.com/github/bnomei/kirby3-feed)
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-feed?icon=codeclimate&label)](https://codeclimate.com/github/bnomei/kirby3-feed/issues)
[![Discord](https://flat.badgen.net/badge/discord/bnomei?color=7289da&icon=discord&label)](https://discordapp.com/users/bnomei)
[![Buymecoffee](https://flat.badgen.net/badge/icon/donate?icon=buymeacoffee&color=FF813F&label)](https://www.buymeacoffee.com/bnomei)

Generate a Atom/JSON/RSS-Feed and XML-Sitemap from Pages-Collection.

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-feed/archive/master.zip) as folder `site/plugins/kirby3-feed` or
- `git submodule add https://github.com/bnomei/kirby3-feed.git site/plugins/kirby3-feed` or
- `composer require bnomei/kirby3-feed`

## Usage Feed

You can use this in a template for a dedicated feed page, in a template controller or a route.

```php
<?php
$options = [
    'title' => 'Latest articles',
    'description' => 'Read the latest news about our company',
    'link' => 'blog'
];
echo page('blog')->children()->listed()->flip()->limit(10)->feed($options);
```

### options array defaults

If you use these defaults you need to provide the fields `date (type: date)` and `text (type: text)`.

```php
[
    'datefield' => 'date',
    'dateformat' => 'r',
    'description' => '',
    'feedurl' => site()->url() . '/feed/',
    'link' => site()->url(),
    'mime' => null,
    'modified' => time(),
    'snippet' => 'feed/rss', // 'feed/json', 'feed/atom'
    'sort' => true,
    'textfield' => 'text',
    'title' => 'Feed',
    'titlefield' => 'title',
    'url' => site()->url(),
    'urlfield' => 'url',
]
```

### virtual page in site/config/config.php

```php
return [
    'routes' => [
        [
            'pattern' => 'feed',
            'method' => 'GET',
            'action'  => function () {
                $options = [
                    'title' => 'Latest articles',
                    'description' => 'Read the latest news about our company',
                    'link' => 'blog',
                    'feedurl' => site()->url() . '/feed/', // matches pattern above
                ];
                
                // while this would be possible
                // return page('blog')->children()->listed()->flip()->limit(10)->feed($options);
                
                // using a closure allows for better performance on a cache hit
                return feed(fn() => page('blog')->children()->listed()->flip()->limit(10), $options);
            }
        ],
    ],
];
```

### HTML head element

rss xml
```php
<link rel="alternate" type="application/rss+xml" title="Latest articles" href="<?= site()->url() ?>/feed"/>
```
and/or rss json
```php
<link rel="alternate" type="application/json" title="Latest articles" href="<?= site()->url() ?>/feed"/>
```

> TIP: Having multiple feed links is still valid html. So you can have both rss and json if you want and setup the routes properly.

### Sorting

The Plugin applies a default sorting for the pages by date/modified in descending order (newest first). 

- If you do not want this you have to set the `datefield` setting to another Field name or PageMethod name.
- If you want to disable sorting by the plugin and add your own you can set the option `sort` to `false`.

### Pitfalls when presorting by date and limit

Using `sortBy('date', 'desc')` will **not** yield expected results! In K3 sorting by date needs a callback.
```php
$feed = page('blog')->children()->listed()->sortBy(function ($page) {
 return $page->date()->toDate();
}, 'desc')->limit(10)->feed($options);
```

## Usage Sitemap

### options array defaults

If you use these defaults you need to provide the fields `date (type: date)` and `text (type: text)`.

```php
[
    'dateformat' => 'c',
    'feedurl' => site()->url().'/sitemap.xml',
    'imagecaptionfield' => 'caption',
    'imagelicensefield' => 'license',
    'images' => false,
    'imagesfield' => 'images',
    'imagetitlefield' => 'title',
    'mime' => null,
    'modified' => time(),
    'snippet' => 'feed/sitemap',
    'sort' => true,
    'urlfield' => 'url',
    'videodescriptionfield' => 'description',
    'videos' => false,
    'videosfield' => 'videos',
    'videothumbnailfield' => 'thumbnail',
    'videotitlefield' => 'title',
    'videourlfield' => 'url',
    'xsl' => true,
]
```

### virtual page in site/config.php

```php
return [
    'routes' => [
        // ... other routes,
        [
            'pattern' => 'sitemap.xml',
            'method' => 'GET',
            'action'  => function () {
                // while this would be possible
                // return site()->index()->listed()->limit(50000)->sitemap();
                
                // using a closure allows for better performance on a cache hit
                return sitemap(fn() => site()->index()->listed()->limit(50000));
            }
        ],
        // (optional) Add stylesheet for human readable version of the xml file.
        // With that stylesheet visiting the xml in a browser will per-generate the images.
        // The images will NOT be pre-generated if the xml file is downloaded (by google).
        [
            'pattern' => 'sitemap.xsl',
            'method' => 'GET',
            'action'  => function () {
                snippet('feed/sitemapxsl');
                die;
            }
        ],
    ],
];
```

### example for excluding pages from sitemap

see the official Kirby documentation: [Filtering compendium](https://getkirby.com/docs/cookbook/content/filtering)

```php
return sitemap(fn() => site()->index()->listed()
    ->filterBy('template', '!=', 'excludeme')
    ->limit(50000)
);
```

## Settings

| bnomei.feed.              | Default        | Description                                          |            
|---------------------------|----------------|------------------------------------------------------|
| expires |`60*24*7` | expire cache in minutes, or on any change to content |


## Cache

> [!Warning]
> If the **global** debug option is set to `true` the plugin will automatically flush its own cache. The plugin will automatically in-validate the cache if any of the Page objects in given Pages-Collection were modified.

If you need to flush the cache manually, like after automated deployments, you can use the following code:

```php
\Bnomei\Feed::flush();
```

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-feed/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.

## Credits

based on K2 versions of
- https://github.com/getkirby-plugins/feed-plugin
- https://github.com/stefanzweifel/kirby-json-feed
