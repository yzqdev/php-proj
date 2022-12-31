<template>
  <div id="video-pop" class="video-pop">
    <div id="videoPlayer" class="dplayer" />
  </div>
</template>

<script>

export default {
  name: 'DplayerPop',
  props: {
    tid: {
      type: String,
      default: ''
    },
    username: {
      type: String,
      default: ''
    },
    url: {
      type: String,
      default: ''
    },
    coverUrl: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      options: {
        autoplay: true,
        controls: true
      },
      player: null
    };
  },
  mounted() {
    document.addEventListener('click', this.removeVideoPop);
    console.log(this.username);
    console.log(this.tid);
    this.initVideoJs();
  },
  beforeDestroy() {
    if (this.player) {
      this.player.destroy();
    }
  },
  methods: {
    initVideoJs() {
      const self = this;
      this.player = new this.$dplayer({
        container: document.getElementById('videoPlayer'),
        autoplay: true,
        loop: false,
        volume: 1,
        mutex: true,
        screenshot: true,
        hotkey: true,
        // danmaku: {
        //   id: this.tid,
        //   api: `/api/danmu`,
        //   token: 'tokendemo',
        //   user: this.username,
        //   maximum: 1000, // 弹幕最大数量
        //   bottom: '15%', // 弹幕距离播放器底部的距离，防止遮挡字幕，取值形如: '10px' '10%'
        //   unlimited: false // 海量弹幕模式，即使重叠也展示全部弹幕，请注意播放器会记忆用户设置，用户手动设置后即失效
        // },
        video: {
          url: self.url, // 单清晰度视屏播放
          pic: self.coverUrl, // 单清晰度视屏播放
          defaultQuality: 0
        }
      });

      this.player.on('error', function() {
        self.player.pause();
        self.player.notice(self.$t('core.videoError'));
      });
    },
    removeVideoPop(e) {
      let pass = true;
      const path = e.path || (e.composedPath && e.composedPath());
      path.forEach(item => {
        if (item.id === 'video-pop') pass = false;
        if (item.classList && item.classList.contains('svg-icon-video-play')) pass = false;
      });
      if (!pass) return;
      this.$emit('remove');
      this.player.destroy();
      document.removeEventListener('click', this.removeVideoPop);
    }
  }
};
</script>

<style lang="scss" scoped>
.video-pop {
  z-index: 100;
  background: #ffffff;
  padding: 15px;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  max-width: 830px;
  max-height: 630px;
}
.dplayer {
  width: 800px;
  height: 600px;
}
::v-deep input {
  box-shadow: none !important;
}
::v-deep .dplayer-danmaku {
  position: absolute;
  left: 0;
  right: 0;
  top: 10px !important;
  bottom: 10px !important;
  font-size: 22px;
  color: #fff;
  font-family: SimHei, "Microsoft JhengHei", Arial, Helvetica, sans-serif;
  font-weight: bold;
}
::v-deep .dplayer.dplayer-arrow .dplayer-danmaku {
  font-size: 25px;
}
</style>
