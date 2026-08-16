<?php

Kirby::plugin('ianhobbs/audio-block', [
  'blueprints' => [
    'blocks/audio-player' => __DIR__ . '/blueprints/blocks/audio-player.yml',
    'files/audio'         => __DIR__ . '/blueprints/files/audio.yml',
    'files/poster'        => __DIR__ . '/blueprints/files/poster.yml',
  ],
  'snippets' => [
    'blocks/audio-player' => __DIR__ . '/snippets/blocks/audio-player.php',
  ],
]);
