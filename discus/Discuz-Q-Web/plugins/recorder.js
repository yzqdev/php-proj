import Vue from 'vue';
import Recorder from 'js-audio-recorder';
import Lamejs from 'lamejs';
import 'aplayer/dist/APlayer.min.css';
import APlayer from 'aplayer';
import WaveSurfer from 'wavesurfer.js';
import Timeline from 'wavesurfer.js/dist/plugin/wavesurfer.timeline';
import Region from 'wavesurfer.js/dist/plugin/wavesurfer.regions';
import Cursor from 'wavesurfer.js/dist/plugin/wavesurfer.cursor';
import DPlayer from 'dplayer';
import config from '@/config';

const recorder = new Recorder({
  sampleBits: 16, // 采样位数，支持 8 或 16，默认是16
  sampleRate: 48000, // 采样率，支持 11025、16000、22050、24000、44100、48000，根据浏览器默认值，我的chrome是48000
  numChannels: 2 // 声道，支持 1 或 2， 默认是1
  // compiling: false,(0.x版本中生效,1.x增加中)  // 是否边录边转换，默认是false
});

Vue.prototype.$recorder = recorder; // 给vue原型加上
Vue.prototype.$lamejs = Lamejs; // 给vue原型加上
Vue.prototype.$aplayer = APlayer; // 给vue原型加上
Vue.prototype.$WaveSurfer = WaveSurfer; // 给vue原型加上
Vue.prototype.$Timeline = Timeline; // 给vue原型加上
Vue.prototype.$cursor = Cursor; // 给vue原型加上
Vue.prototype.$Region = Region; // 给vue原型加上
Vue.prototype.$dplayer = DPlayer; // 给vue原型加上
Vue.prototype.$api = config.DEV_API_URL;
