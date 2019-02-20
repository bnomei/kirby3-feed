<?php

    $pages = $items;
    $items = [];
    foreach ($pages as $item) {
        $items[] = [
            'id'             => $item->url(),
            'url'            => $item->url(),
            'title'          => $item->title()->value(),
            'content_html'   => $item->{$textfield}()->kirbytext()->value(),
            'date_published' => date('c', $item->{$datefield}()->toTimestamp()),
            'date_modified'  => $item->modified('Y-m-d\TH:i:sP', 'date'),
        ];
    }

    $feed = [
        'version'       => 'https://jsonfeed.org/version/1',
        'title'         => $title,
        'home_page_url' => url(),
        'feed_url'      => $link,
        'items'         => $items,
    ];
    echo json_encode($feed);
