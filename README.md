# Kirby 3 Feed

![GitHub release](https://img.shields.io/github/release/bnomei/kirby3-feed.svg?maxAge=1800) ![License](https://img.shields.io/github/license/mashape/apistatus.svg) ![Kirby Version](https://img.shields.io/badge/Kirby-3%2B-black.svg)

Generate a RSS-Feed from a Pages-Collection.

This plugin is free but if you use it in a commercial project please consider to [make a donation 🍻](https://www.paypal.me/bnomei/5).

## Usage

You can use this in a template for a dedicated feed page, in a template controller or a route.

```php
<?php
$options = [
    'title'       => 'Latest articles',
    'description' => 'Read the latest news about our company',
    'link'        => 'blog'
];
echo page('blog')->children()->visible()->flip()->limit(10)->feed($options);
```

**options array defaults**
```php
[
    'url'         => site()->url(),
    'title'       => 'Feed',
    'description' => '',
    'link'        => site()->url(),
    'datefield'   => 'date',
    'textfield'   => 'text',
    'modified'    => time(),
    'snippet'     => 'feed/json',
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
                $feed = page('blog')->children()->visible()->flip()->limit(10)->feed($options);
                return $feed;
            }
        ]
    ]
];
```

## Settings

**expires**
- default: `60*24*7` in minutes

> The plugin will automatically devalidate the cache if any of the Page-Objects were modified. The plugin uses minutes not seconds since K3 Cache does that as well.

**debugforce**
- default: `true`
force refresh if Kirbys global debug options is active


## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-feed/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.

## Credits

based on V2 version of
- https://github.com/getkirby-plugins/feed-plugin
