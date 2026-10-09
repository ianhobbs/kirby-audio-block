<?php

use Kirby\Cms\App;
use Kirby\Data\Data;

Kirby::plugin('ianhobbs/audio-block', [
  'options' => [
    // Front-end stylesheet handling:
    //   true   – inject the shipped assets/audio-player.css automatically
    //            (only on pages that actually render the block)
    //   false  – inject nothing, style the block classes yourself
    //   string – URL of your own stylesheet to inject instead
    'css' => true,
  ],
  'assets' => [
    'audio-player.css' => __DIR__ . '/assets/audio-player.css',
  ],
  'blueprints' => [
    'blocks/audio-player' => __DIR__ . '/blueprints/blocks/audio-player.yml',
    // legacy alias: content created with ianhobbs/song-block stores the
    // block type `song` with the same fields
    'blocks/song'         => fn () => array_merge(
      Data::read(__DIR__ . '/blueprints/blocks/audio-player.yml'),
      ['name' => 'Song']
    ),
    'files/audio'         => __DIR__ . '/blueprints/files/audio.yml',
    'files/poster'        => __DIR__ . '/blueprints/files/poster.yml',
  ],
  'snippets' => [
    'blocks/audio-player' => __DIR__ . '/snippets/blocks/audio-player.php',
    'blocks/song'         => __DIR__ . '/snippets/blocks/audio-player.php',
  ],
  'hooks' => [
    'page.render:after' => function (string $contentType, string $html) {
      if ($contentType !== 'html') {
        return $html;
      }

      $kirby  = App::instance();
      $option = $kirby->option('ianhobbs.audio-block.css', true);

      if ($option === false || $option === null) {
        return $html;
      }

      // no block on this page, no stylesheet needed
      if (str_contains($html, 'audio-wrapper') === false) {
        return $html;
      }

      if ($option === true) {
        $url = $kirby->plugin('ianhobbs/audio-block')?->asset('audio-player.css')?->url();
      } else {
        $url = $option;
      }

      if ($url === null) {
        return $html;
      }

      // already included manually in the template
      $needle = $option === true ? 'audio-player.css' : $url;

      if (str_contains($html, $needle) === true) {
        return $html;
      }

      $head = stripos($html, '</head>');

      if ($head === false) {
        return $html;
      }

      return substr($html, 0, $head) . css($url) . PHP_EOL . substr($html, $head);
    },
  ],
]);
