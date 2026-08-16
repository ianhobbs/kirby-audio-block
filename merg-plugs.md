# Merging three Kirby block plugins into one monolithic package — pros & cons

Comparison of bundling `2026-sliderPlugin`, `song-block`, and `ianhobbs-audio-block` into a single Kirby plugin package versus keeping them separate. Focus on CSS tooling and customisation.

## Current state

| | `2026-sliderPlugin` | `song-block` | `ianhobbs-audio-block` |
|---|---|---|---|
| Plugin ID | `ianhobbs/kirby-swiper-block` | `ianhobbs/song-block` | `ianhobbs/audio-block` |
| Block type | `swiper` | `song` | `audio-player` |
| Maturity | High — v1.3.6, block model class (452-line `SwiperBlock.php`), Pest tests + dev Kirby 5 site, GitHub templates | Low — no version, no model, no CSS shipped, no tests | Medium — just refactored: design tokens, shipped CSS, LICENSE/README, no tests |
| Kirby support | **5 only** (`conflict: <5.0.0 \|\| >=6.0.0`, PHP ≥ 8.3) | 4 or 5 (no constraint declared) | 4+ (uses `k-frame`) |
| Frontend CSS | Ships `assets/css/swiper-block.css`; tokens `--swiper-block-ratio/-height/-fixed-height`; overrides Swiper's `--swiper-*` vars; Tailwind class names (`text-4xl`…) stored in content with `:where()` fallbacks | **None** — site must style everything | Ships `assets/audio-player.css`; 17 `--ap-*` tokens with fallbacks; per-block `--colBG`/`--colTX` |
| Frontend JS | Swiper 12 via CDN + shipped init script, auto-injected once per request (`injectAssets`) | none | none |
| Panel CSS | Kirby Panel design tokens (`--spacing-*`, `--color-gray-*`) | hardcoded hex/rem | hardcoded hex/rem |
| Build | kirbyup (`serve`/`build`/`watch`) | kirbyup | kirbyup |

Critical observation: **`song-block` and `ianhobbs-audio-block` are near-duplicates.** Identical field set (poster, source, title, subtitle, description, controls, autoplay), same Vue preview lineage. `audio-block` is the evolved copy. Any merge should confront this duplication, not preserve it.

## Pros of one monolithic package

### Install & maintenance
- One `composer require` / one submodule / one folder in `site/plugins/` instead of three; one changelog, one version stream, one issue tracker.
- Swiper's engineering assets (dev Kirby 5 test site, Pest suite, GitHub issue templates, versioning discipline) become shared infrastructure — the audio blocks inherit a test harness they currently lack.
- One kirbyup entry bundles all Panel components; one `package.json`, one build command. Currently three builds with three slightly different script conventions.

### CSS tooling & customisation (the big win)
- **One token namespace.** Today a site theming all three must learn `--swiper-block-*`, `--ap-*`, and hand-style `song-block` from scratch. Merged: a single documented prefix (e.g. `--ihb-*`) with a shared core scale (spacing, radius, shadow, text sizes, colours) plus per-block extensions (`--ihb-slider-height`, `--ihb-audio-poster-size`). One token reference table in one README feeds the user's CSS build step.
- **Shared PHP utilities.** `SwiperBlock::cssColor()` (regex-allowlisted colour sanitiser) should be guarding the audio block's `--colBG`/`--colTX` inline output today — a merged package makes that natural. Same for the once-per-request `claimAssets()` auto-injection: audio CSS currently needs a manual `css()` include; merged, it rides the same `injectAssets` option.
- **Visual consistency across blocks** — same radius, shadow, spacing scale on slider captions and audio cards, defined once.
- Panel previews can converge on Kirby's Panel design tokens (swiper already does; the two audio previews hardcode hex/rem).

### Consolidation
- Forces the `song` vs `audio-player` dedupe: keep `audio-player`, register `song` as a legacy alias (same snippet/blueprint under both keys) so existing content keeps rendering.
- One vendor namespace, one place for shared file blueprints (`files/audio`, `files/poster`, `files/swiper-image`).

## Cons of one monolithic package

### Coupling
- **Kirby/PHP floor rises for everyone.** The merged package inherits the strictest constraint: Kirby 5 only, PHP ≥ 8.3. Any Kirby 4 site using song/audio blocks is cut off (or the swiper constraint must be loosened and tested on 4 — real work).
- **Version coupling.** A breaking change in the slider forces a major bump on sites that only use the audio block, and vice versa. Release cadence is very different today (slider iterates, audio is quiet).
- All-or-nothing payload: slider-only sites carry audio code and vice versa. Server-side cost is negligible (snippets load on use), but the Panel bundle always ships every block's Vue preview, and the Swiper CDN dependency becomes part of a package audio-only users install.

### CSS tooling & customisation (the big risk)
- **Two customisation philosophies collide.** The slider stores literal Tailwind class names (`text-4xl`, `text-lg`) *in content files* as heading/subtext sizes, with `:where()` fallbacks; the audio block is pure custom-property tokens. Unifying means either pushing Tailwind-shaped values into the audio block or migrating the slider to tokens — and the slider's values live in stored content, so that migration touches content, not just CSS.
- **Token renaming is a breaking change.** Sites already themed against `--ap-*` or `--swiper-block-*` break unless old names ship as aliases for a deprecation window (doable — `--ap-radius: var(--ihb-radius)` shim layer — but it's maintenance surface).
- Swiper CDN (`jsdelivr`) assets are auto-injected; folding audio CSS into the same injection changes behaviour for existing audio-block sites that manually include the stylesheet (double-include or FOUC risk during migration).

### Migration mechanics
- Existing composer installs of three package names need `replace`/`conflict` declarations or abandoned-package forwarding; submodule users must re-point remotes; git history of three repos merges or is dropped.
- Block type names (`swiper`, `song`, `audio-player`) are stored in content JSON — they must all keep working in the merged package or every site needs a content migration.
- Test coverage expectation rises: the Pest suite covers swiper only; a monolith with untested audio blocks is a monolith with a soft half.

## Verdict

The merge is worth it **if** the three blocks are treated as one product (an "ianhobbs blocks suite") targeting Kirby 5, and `song` is retired into `audio-player`. The CSS story is the strongest argument for merging — one token system, one sanitiser, one injection path — and simultaneously the largest piece of work, because the slider's Tailwind-in-content sizing and the audio `--ap-*` names both need compatibility shims.

If Kirby 4 support for the audio block matters, or release cadences should stay independent, keep separate packages and instead extract the shared bits (token core, `cssColor()`, asset-claim injection) into a small shared internal library — most of the consistency win without the coupling.

## Implementation outline (if merge goes ahead)

1. **New package skeleton** — `ianhobbs/kirby-blocks` (installer-name `kirby-blocks`), based on the slider repo (it has the model class, dev site, tests, CI templates). Single `index.php` registering three block types + shared file blueprints + icons.
2. **Block types** — keep `swiper` and `audio-player` as-is; register `song` as a legacy alias pointing at the audio-player blueprint/snippet/preview so old content renders (fields identical, verified).
3. **Shared PHP** — move `SwiperBlock::cssColor()` into a shared helper (e.g. `classes/Support/Css.php`); route audio `--colBG`/`--colTX` output through it. Generalise `claimAssets()` so each block claims its own CSS/JS once per request; audio CSS joins `injectAssets`.
4. **Token unification** — new `--ihb-*` core scale (spacing, radius, shadow, text sizes, colour); per-block tokens namespaced `--ihb-audio-*`, `--ihb-slider-*`. Ship an alias layer mapping every existing `--ap-*` and `--swiper-block-*` name onto the new tokens; document deprecation. Leave the slider's Tailwind size values untouched in v1 (content-stored; migrate in a later major).
5. **Panel build** — one `src/index.js` registering all previews via one `panel.plugin` call; port the two audio previews' hardcoded CSS to Panel design tokens like the swiper preview.
6. **Composer/versioning** — `replace` the three old package names; start at v2.0.0; `conflict` Kirby <5.
7. **Docs** — merged README: install, per-block usage, single token reference table (core + per-block), colour precedence, migration notes from the three old plugins.

### Verification
- Load the merged plugin in the slider repo's `dev/` Kirby 5 site; add all three block types (plus a legacy `song` block pasted into content) to a test page; confirm render + Panel previews.
- Run the existing Pest suite; add smoke tests for the audio snippet (renders with/without poster, colour picked/empty, SVG poster).
- Theme the test page with only `--ihb-*` tokens defined, then with only legacy `--ap-*`/`--swiper-block-*` names — both must restyle the blocks (alias-layer proof).
- Confirm assets inject exactly once with multiple blocks of different types on one page.
