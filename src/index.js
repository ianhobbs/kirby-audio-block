import AudioPlayer from "./components/AudioPlayer.vue";

panel.plugin("ianhobbs/audio-block", {
  blocks: {
    "audio-player": AudioPlayer,
  },
});
