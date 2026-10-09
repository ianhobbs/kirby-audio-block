<?php

// Only emit inline colour overrides when the editor picked a colour,
// so the site-wide --ap-* tokens apply otherwise. Values are checked
// against a colour-safe character allowlist before going into CSS.
$color = function ($field): ?string {
  $value = trim($field->value() ?? '');
  return preg_match('/^[#a-zA-Z0-9(),.%\s\/-]+$/', $value) === 1 ? $value : null;
};

$styles  = [];
$classes = ['audio-wrapper'];

if ($bg = $color($block->bgcolor())) {
  $styles[] = '--colBG: ' . $bg;
}

if ($tx = $color($block->textcolor())) {
  $styles[] = '--colTX: ' . $tx;
}

$poster     = $block->poster()->toFile();
$background = $poster !== null && $block->layout()->value() === 'background';

if ($background === true) {
  // SVG and other non-resizable images are used as-is
  $url       = $poster->isResizable() ? $poster->resize(1200)->url() : $poster->url();
  $classes[] = 'audio-wrapper--background';
  $styles[]  = "background-image: url('" . str_replace("'", '%27', $url) . "')";
}

?>
<?php if ($file = $block->source()->toFile()): ?>
<div <?= attr(['class' => implode(' ', $classes), 'style' => $styles !== [] ? implode('; ', $styles) : null]) ?>>
  <?php if ($poster !== null && $background === false): ?>
  <figure class="audio-poster">
    <?= $poster->isResizable() ? $poster->crop(200, 200) : $poster ?>
  </figure>
  <?php endif ?>
  <div class="audio-info">
    <h1 class="audio-title"><?= $block->title()->html() ?></h1>
    <h2 class="audio-subtitle"><?= $block->subtitle()->html() ?></h2>
    <p class="audio-description text-base">
      <?= $block->description() ?>
    </p>
    <audio class="audio-playbar"
      preload="metadata"
      <?= $block->controls()->isTrue() ? 'controls' : '' ?>
      <?= $block->autoplay()->isTrue() ? 'autoplay' : '' ?>
    >
      <source src="<?= $file->url() ?>" type="<?= $file->mime() ?>">
      Your browser does not support the <code>audio</code> element.
    </audio>
  </div>
</div>
<?php endif ?>
