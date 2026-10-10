<template>
  <k-block-figure
    :is-empty="!source.url"
    empty-icon="file-audio"
    empty-text="No file selected yet …"
    @open="open"
    @update="update"
  >
    <div
      class="k-block-type-audio-player-wrapper"
      :class="{ 'is-background': isBackground }"
      :style="wrapperStyle"
    >
      <div v-if="!isBackground">
        <k-frame ratio="1/1" :cover="true" class="k-block-type-audio-player-poster" >
          <img v-if="poster.url" :src="poster.url" alt="" />
        </k-frame>
      </div>
      <div class="k-block-type-audio-player-info" @dblclick.stop>
        <k-writer
          :inline="true"
          :marks="false"
          :placeholder="field('title').placeholder"
          :value="content.title"
          class="k-block-type-audio-player-title"
          @input="update({ title: $event })"
        />
        <k-writer
          :inline="true"
          :marks="false"
          :placeholder="field('subtitle').placeholder"
          :value="content.subtitle"
          class="k-block-type-audio-player-subtitle"
          @input="update({ subtitle: $event })"
        />
        <k-writer
          :inline="true"
          :marks="false"
          :placeholder="field('description').placeholder"
          :value="content.description"
          class="k-block-type-audio-player-description"
          @input="update({ description: $event })"
        />
        <audio
          ref="audio"
          class="k-block-type-audio-player-playbar"
          controls
          @play="playing = true"
          @pause="playing = false"
        >
          <source :src="source.url" :type="mime" />
        </audio>
        <!-- replaces the native controls when the column is too narrow -->
        <div class="k-block-type-audio-player-compact">
          <k-button
            :icon="playing ? 'audio-pause' : 'play'"
            :title="playing ? 'Pause' : 'Play'"
            size="sm"
            variant="filled"
            @click.stop="toggle"
          />
        </div>
      </div>
    </div>
  </k-block-figure>
</template>

<script>
export default {
  data() {
    return {
      mime: null,
      playing: false,
    };
  },
  computed: {
    poster() {
      return (this.content.poster || [])[0] || {};
    },
    source() {
      return (this.content.source || [])[0] || {};
    },
    // blocks saved before the layout option existed have no stored
    // value, so fall back to the blueprint default
    layout() {
      return this.content.layout || this.field("layout")?.default || "side";
    },
    isBackground() {
      return this.layout === "background" && Boolean(this.poster.url);
    },
    wrapperStyle() {
      return {
        backgroundColor: this.content.bgcolor || null,
        color: this.content.textcolor || null,
        backgroundImage: this.isBackground ? `url("${this.poster.url}")` : null,
      };
    },
  },
  methods: {
    toggle() {
      const audio = this.$refs.audio;

      if (audio.paused) {
        audio.play();
      } else {
        audio.pause();
      }
    },
  },
  watch: {
    "source.link": {
      handler(link) {
        if (link) {
          this.$api.get(link).then((file) => {
            this.mime = file.mime;
          });
        }
      },
      immediate: true,
    },
  },
};
</script>

<style lang="scss">
.k-block-type-audio-player-wrapper {
  display: flex;
  background-color: #333;
  color: white;
}
.k-block-type-audio-player-wrapper.is-background {
  padding: 1rem;
  background-repeat: no-repeat;
  background-size: cover;
  background-position: top right;
}
.k-block-type-audio-player-info {
  flex: 1;
  min-width: 0;
  container: audio-player-info / inline-size;
}
.k-block-type-audio-player-poster {
  width: 12rem;
  margin-right: 1rem;
  background: #333;
}
.k-block-type-audio-player-title,
.k-block-type-audio-player-subtitle {
  font-size: 1.5rem;
}
.k-block-type-audio-player-title {
  font-weight: 400;
}
.k-block-type-audio-player-subtitle {
  margin-bottom: 1rem;
  opacity: 0.6;
}
.k-block-type-audio-player-description {
  line-height: 1.5;
}
.k-block-type-audio-player-playbar {
  display: block;
  width: 100%;
  max-width: 30rem;
  margin-top: 2rem;
  height: 2rem;
}
.k-block-type-audio-player-compact {
  display: none;
  margin-top: 1rem;
}
// the native player needs roughly 20rem to show all of its controls;
// below that swap it for a single play/pause button
@container audio-player-info (width < 20rem) {
  .k-block-type-audio-player-playbar {
    display: none;
  }
  .k-block-type-audio-player-compact {
    display: block;
  }
}
</style>
