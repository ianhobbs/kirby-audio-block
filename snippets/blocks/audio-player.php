<?php

// Only emit inline colour overrides when the editor picked a colour,
// so the site-wide --ap-* tokens apply otherwise.
$styles = [];

if ($block->bgcolor()->isNotEmpty()) {
  $styles[] = '--colBG: ' . $block->bgcolor();
}

if ($block->textcolor()->isNotEmpty()) {
  $styles[] = '--colTX: ' . $block->textcolor();
}

?>
<?php if ($file = $block->source()->toFile()): ?>
<div class="audio-wrapper"<?= $styles !== [] ? ' style="' . implode('; ', $styles) . '"' : '' ?>>
  <?php if ($poster = $block->poster()->toFile()): ?>
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
