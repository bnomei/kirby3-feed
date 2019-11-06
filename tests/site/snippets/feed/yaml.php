<?php
    // NOTE: unless values are explicit cast yaml ties to resolve kirbys recursive tree
    $entries = [];
    foreach ($items as $item) {
        $entries[] = [
            'id'             => (string) $item->url(),
            'url'            => (string) $item->{$urlfield}(),
            'title'          => (string) $item->{$titlefield}()->value(),
            'content_html'   => (string) $item->{$textfield}()->kirbytext()->value(),
            'date_published' => (string) date('c', $item->{$datefield}()->toTimestamp()),
            'date_modified'  => (string) $item->modified('Y-m-d\TH:i:sP', 'date'),
        ];
    }

    $feed = [
        'version'       => 'YAML',
        'title'         => (string) $title,
        'description'   => (string) $description,
        'home_page_url' => (string) $url,
        'feed_url'      => (string) $feedurl,
        'items'         => (array) $entries,
    ];

    echo Kirby\Data\Yaml::encode($feed);
