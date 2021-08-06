<?xml version="1.0" encoding="utf-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        <?php if($images): ?>xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"<?php endif; ?>
        <?php if($videos): ?>xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"<?php endif; ?>
>
  <?php foreach($items as $item): ?>
  <url>
    <loc><?= $item->url() ?></loc>
    <?php foreach(kirby()->languages() as $lang): ?>
    <xhtml:link
      rel="alternate"
      hreflang="<?= $lang->code() ?>"
      href="<?= $item->url($lang->code()) ?>"/>
      <?php if($lang->isDefault()): ?><link rel="alternate" href="<?= $item->url() ?>" hreflang="x-default" /><?php endif; ?>
    <?php endforeach; ?>
    <lastmod><?= $modified ?></lastmod>
    <?php if($images): ?>
    <?php foreach($item->{$imagesfield}() as $image): ?>
    <image:image>
      <image:loc><?= $image->url() ?></image:loc>
      <?php if($image->{$imagetitlefield}()->isNotEmpty()): ?><image:title><?= $image->{$imagetitlefield}() ?></image:title><?php endif; ?>
      <?php if($image->{$imagecaptionfield}()->isNotEmpty()): ?><image:caption><?= $image->{$imagecaptionfield}() ?></image:caption><?php endif; ?>
      <?php if($image->{$imagelicensefield}()->isNotEmpty()): ?><image:license><?= $image->{$imagelicensefield}() ?></image:license><?php endif; ?>
    </image:image>
    <?php endforeach; ?>
    <?php endif; ?>
    <?php if($videos): ?>
    <?php foreach($item->{$videosfield}() as $video): ?>
    <video:video>
      <?php if($image->{$videothumbnailfield}()->isNotEmpty()): ?><video:thumbnail><?= $video->{$videothumbnailfield}() ?></video:thumbnail><?php endif; ?>
      <?php if($image->{$videotitlefield}()->isNotEmpty()): ?><video:title><?= \Kirby\Toolkit\Xml::encode($item->{$videotitlefield}()) ?></video:title><?php endif; ?>
      <?php if($image->{$videodescriptionfield}()->isNotEmpty()): ?><video:description><?= \Kirby\Toolkit\Xml::encode($item->{$videodescriptionfield}()) ?></video:description><?php endif; ?>
      <?php if(Str::contains($video->{$videourlfield}(), site()->url())): ?><video:content_loc><?= $video->{$videourlfield}() ?></video:content_loc>
      <?php else: ?><video:player_loc><?= $video->{$videourlfield}() ?></video:player_loc><?php endif; ?>
    </video:video>
    <?php endforeach; ?>
    <?php endif; ?>
    </url>
  <?php endforeach; ?>
</urlset>
