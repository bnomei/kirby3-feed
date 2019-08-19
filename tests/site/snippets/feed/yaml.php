<?php
    $entries = [];
    foreach ($items as $item) {
        $entries[] = [
            'id'             => $item->url(),
            'url'            => $item->{$urlfield}(),
            'title'          => $item->title()->value(),
            'content_html'   => $item->{$textfield}()->kirbytext()->value(),
            'date_published' => date('c', $item->{$datefield}()->toTimestamp()),
            'date_modified'  => $item->modified('Y-m-d\TH:i:sP', 'date'),
        ];
    }

    $feed = [
        'version'       => 'YAML',
        'title'         => $title,
        'description'   => $description,
        'home_page_url' => $url,
        'feed_url'      => $feedurl,
        'items'         => $entries,
    ];


    echo Kirby\Data\Yaml::encode($feed);
