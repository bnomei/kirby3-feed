<?php

test('finds home page', function () {
    $response = kirby()->render('/');
    expect($response->code() === 200)->toBeTrue();
    $this->assertStringContainsString('Home', $response->body());
});

test('finds feed route', function () {
    $response = kirby()->render('/feed');
    expect($response->code() === 200)->toBeTrue()
        ->and($response->type() === 'application/rss+xml')->toBeTrue()
        ->and($response->body())->toStartWith('<?xml version="1.0" encoding="utf-8"?>');
});

test('finds sitemap route', function () {
    $response = kirby()->render('/sitemap.xml');
    expect($response->code() === 200)->toBeTrue()
        ->and($response->type() === 'text/xml')->toBeTrue()
        ->and($response->body())->toStartWith('<?xml version="1.0" encoding="utf-8"?>');
});

test('finds feed route json', function () {
    $response = kirby()->render('/feed-json');
    expect($response->code() === 200)->toBeTrue()
        ->and($response->type() === 'application/json')->toBeTrue();
});

test('finds feed route yaml', function () {
    $response = kirby()->render('/feed-yaml');
    expect($response->code() === 200)->toBeTrue()
        ->and($response->type() === 'application/yaml')->toBeTrue();
});
