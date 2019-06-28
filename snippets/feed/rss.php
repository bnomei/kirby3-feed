<?php use \Kirby\Toolkit\Xml;

echo '<?xml version="1.0" encoding="utf-8"?>';
?><rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">

  <channel>
    <title><?php echo Xml::encode($title) ?></title>
    <link><?php echo Xml::encode($link) ?></link>
    <lastBuildDate><?php echo $modified ?></lastBuildDate>
    <atom:link href="<?php echo Xml::encode($url) ?>" rel="self" type="application/rss+xml" />

    <?php if (!empty($description)): ?>
    <description><?php echo Xml::encode($description) ?></description>
    <?php endif ?>

    <?php foreach ($items as $item): ?>
    <item>
      <title><?php echo Xml::encode($item->title()) ?></title>
      <link><?php echo Xml::encode($item->{$urlfield}()) ?></link>
      <guid><?php echo Xml::encode($item->id()) ?></guid>
      <pubDate><?php echo $datefield == 'modified' ? $item->modified('r', 'date') : date('r', $item->{$datefield}()->toTimestamp()) ?></pubDate>
      <description><![CDATA[<?php echo $item->{$textfield}()->kirbytext() ?>]]></description>
    </item>
    <?php endforeach ?>

  </channel>
</rss>
