<?php
echo '<?xml version="1.0" encoding="utf-8"?>';
?><rss version="2.0">
  <channel>
    <title><?= \Kirby\Toolkit\Xml::encode($title) ?></title>
    <link><?= \Kirby\Toolkit\Xml::encode($link) ?></link>
    <lastBuildDate><?= $modified ?></lastBuildDate>
    <?php if ($description && is_string($description) && strlen(trim($description)) > 0) { ?>
    <description><?= \Kirby\Toolkit\Xml::encode($description) ?></description>
    <?php } ?>
    <?php foreach ($items as $item) { ?>
    <item>
      <title><?= \Kirby\Toolkit\Xml::encode($item->{$titlefield}()) ?></title>
      <link><?= \Kirby\Toolkit\Xml::encode($item->{$urlfield}()) ?></link>
      <guid><?= \Kirby\Toolkit\Xml::encode($item->url()) ?></guid>
      <pubDate><?= $datefield === 'modified' ? $item->modified('r', 'date') : date('r', $item->{$datefield}()->toTimestamp()) ?></pubDate>
      <description><![CDATA[<?= $item->{$textfield}()->kirbytext() ?>]]></description>
    </item>
    <?php } ?>
  </channel>
</rss>
