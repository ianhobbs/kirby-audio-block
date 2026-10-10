import AudioPlayer from "./components/AudioPlayer.vue";

panel.plugin("ianhobbs/audio-block", {
  blocks: {
    "audio-player": AudioPlayer,
  },
  icons: {
    // the Panel icon set has no pause icon
    "audio-pause": '<path d="M6 5h2v14H6V5zm10 0h2v14h-2V5z"/>',
  },
});
