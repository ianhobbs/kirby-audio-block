import AudioPlayer from "./components/AudioPlayer.vue";

panel.plugin("ianhobbs/audio-block", {
  blocks: {
    "audio-player": AudioPlayer,
    // legacy alias for content created with ianhobbs/song-block
    song: AudioPlayer,
  },
});
