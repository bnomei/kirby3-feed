<?php
echo '<?xml version="1.0" encoding="utf-8"?>'; ?><feed xmlns="http://www.w3.org/2005/Atom">
    <title><?= \Kirby\Toolkit\Xml::encode($title) ?></title>
    <link href="<?= $link ?>"/>
    <updated><?= date('r', $modified) ?></updated>
    <id><?= str_replace(site()->url(),'', $link) ?></id>
    <?php foreach ($items as $item): ?>
    <entry>
        <title><?= \Kirby\Toolkit\Xml::encode($item->{$titlefield}()) ?></title>
        <link href="<?= $item->{$urlfield}() ?>"/>
        <id><?= $item->{$idfield}() ?></id>
        <updated><?= $datefield === 'modified' ? $item->modified('r', 'date') : date('r', $item->{$datefield}()->toTimestamp()) ?></updated>
        <summary><![CDATA[<?= $item->{$textfield}()->kirbytext() ?>]]><</summary>
    </entry>
    <?php endforeach; ?>
</feed>