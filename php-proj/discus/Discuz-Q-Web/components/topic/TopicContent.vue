<template>
  <article class="global">
    <div class="title">{{ title }}</div>
    <viewer
      :trigger="$xss(formatTopicHTML(article.contentHtml || ''))"
      :options="options"
    >
      <div
        v-highlight
        class="content-html"
        v-html="$xss(formatTopicHTML(article.contentHtml || ''))"
      />
    </viewer>
    <div v-if="video.coverUrl" class="container-video-img-cover">
      <div class="warp-video-img-cover">
        <img
          class="video-img-cover"
          :src="video.coverUrl"
          :alt="video.fileName"
          @click.stop="openVideo"
        >
        <svg-icon
          type="video-play"
          class="icon-play"
          style="font-size: 50px"
          @click="openVideo"
        />
      </div>
    </div>
    <!--帖子只展示图文混排的图片-->
    <div
      v-if="thread && thread.images && thread.images.length > 0"
      v-viewer="{ url: 'data-source' }"
      class="images"
      @click="unpaid ? openVideo() : ''"
    >
      <el-image
        v-for="(image, index) in thread.images"
        :key="index"
        class="image"
        :data-source="unpaid ? '' : image.url"
        :src="image.thumbUrl"
        :alt="image.fileName"
        fit="cover"
      >
        <div slot="placeholder" class="image-slot">
          <i class="el-icon-loading" />
        </div>
      </el-image>
    </div>
    <div
      v-if="article.images && article.images.length > 0"
      v-viewer="{ url: 'data-source' }"
      class="images"
      @click="unpaid ? openVideo() : ''"
    >
      <el-image
        v-for="(image, index) in article.images"
        :key="index"
        class="image"
        :data-source="unpaid ? '' : image.url"
        :src="image.thumbUrl"
        :alt="image.filename"
        fit="cover"
      >
        <div slot="placeholder" class="image-slot">
          <i class="el-icon-loading" />
        </div>
      </el-image>
    </div>
    <div
      v-if="(thread && thread.attachments || []).length > 0"
      class="container-attachment"
    >
      <h3 class="name">{{ $t("topic.attachment") }}</h3>
      <div>
        <template v-for="(file, index) in thread.attachments">
          <attachment-list
            :key="index"
            :file="file"
            :price="paidInformation.price"
            :is-paid="paidInformation.paid"
            :is-paid-attachment="paidInformation.isPaidAttachment"
            :attachment-price="paidInformation.attachmentPrice"
          />
        </template>
      </div>
    </div>
    <div
      v-if="(article.attachments || []).length > 0"
      class="container-attachment"
    >
      <h3 class="name">{{ $t("topic.attachment") }}</h3>
      <div>
        <template v-for="(file, index) in article.attachments">
          <attachment-list
            :key="index"
            :file="file"
            :price="paidInformation.price"
            :is-paid="paidInformation.paid"
            :is-paid-attachment="paidInformation.isPaidAttachment"
            :attachment-price="paidInformation.attachmentPrice"
          />
        </template>
      </div>
    </div>
    <div v-if="unpaid && threadType === 1" class="hide-content-tip">
      {{ $t("pay.contentHide", {percent: (1 - paidInformation.freeWords).toFixed(2) * 100}) }}
    </div>
    /*IFTRUE_default*/
    <!-- <nuxt-link
      v-if="category.name"
      :to="{ path: `/category/${category._jv.id}` }"
      class="tag"
    >{{ category.name }}</nuxt-link> -->
    <div
      v-if="category.name"
      class="tag"
      @click="toCategoryPage"
    >{{ category.name }}</div>
    /*FITRUE_default*/
    <div v-if="location && location.location">
      <nuxt-link
        :to="
          `/topic/position?longitude=${location.longitude}&latitude=${location.latitude}`
        "
        class="location"
      >
        <span class="flex">
          <svg-icon type="location" class="icon" />
          <span> {{ location.location }} </span>
        </span>
      </nuxt-link>
    </div>
    <div v-if="threadType === 6" class="product">
      <product-item :item="thread && thread.postGoods" />
    </div>
    <div v-if="threadType === 4" class="audio">
      <template v-for="(file, index) in [...audio]">
        <APlayer
          :key="index"
          :file="file"
          :current-audio="currentAudio"
          @play="play"
          @pause="pause"
          @seek="seek"
          @seeking="seeking"
        />
      </template>
    </div>
    <dplayer-pop
      v-if="showVideoPop"
      :cover-url="video.coverUrl"
      :url="video.mediaUrl"
      @remove="showVideoPop = false"
    />
    <audio id="audio-player" :src="currentAudio.url" style="display: none" />

  </article>
</template>

<script>
import s9e from '@/utils/s9e';
import isLogin from '@/mixin/isLogin';
import 'viewerjs/dist/viewer.css';
import Viewer from 'v-viewer/src/component.vue';
import stringToLink from '@/utils/stringToLink';

export default {
  name: 'TopicContent',
  components: { Viewer },
  mixins: [isLogin],
  props: {
    article: {
      type: Object,
      default: () => {}
    },
    title: {
      type: String,
      default: () => ''
    },
    video: {
      type: Object,
      default: () => {}
    },
    audio: {
      type: Object,
      default: () => {}
    },
    vurl: {
      type: String,
      default: () => ''
    },
    wavurl: {
      type: String,
      default: () => ''
    },
    bvid: {
      type: String,
      default: () => ''
    },
    suburl: {
      type: String,
      default: () => ''
    },

    paidInformation: {
      type: Object,
      default: () => {}
    },
    threadType: {
      type: Number,
      default: 0
    },
    category: {
      type: Object,
      default: () => {}
    },
    location: {
      type: Object,
      default: () => {}
    },
    topicType: {
      type: String,
      default: () => ''
    },
    thread: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      /* IFTRUE_default*/
      search_ids: '',
      /* FITRUE_default*/
      showVideoPop: false,
      wavesurfer: null,
      ap: null,
      dp: null,
      speed: 1,
      loaddp: null,
      loadap: null,
      timer: null,
      isapseek: null,
      options: {
        // 过滤表情
        filter(image) {
          return [...image.classList].indexOf('qq-emotion') < 0;
        },
        // 实际查看的图片链接
        url: thumbImage => {
          const image = this.article.imageSource.filter(
            item => decodeURI(item.fileName) === decodeURI(thumbImage.alt)
          );
          return image.length > 0 ? image[0].url : thumbImage.src;
        }
      },
      currentAudio: {
        id: '',
        url: '',
        currentTime: '',
        duration: '',
        audio: '',
        seeking: false,
        isPlay: false,
        isLoading: false,
      }
    };
  },
  computed: {
    unpaid() {
      if (process.client && this.paidInformation.paid) {
        this.removeTextHideCover();
      }
      return !(
        this.paidInformation.paid
        || parseFloat(this.paidInformation.price) === 0
      );
    }
  },
  watch: {
    article: {
      handler() {
        this.addTextHideCover();
      },
      deep: true
    }
  },
  mounted() {
    this.currentAudio.audio = document.getElementById('audio-player');

    this.timer = setInterval(() => {
      this.loaddp = document.querySelector('#dplayer');
      this.loadap = document.querySelector('#aplayer');
      // this.initAliplayer();
      if (this.loaddp || this.loadap) {
        this.$nextTick(() => {
          this.initAliplayer();
        });
        clearInterval(this.timer);
        this.timer = null;
      }
    }, 2000);
  },
  beforeDestroy() {
    if (this.wavurl != null && this.ap) {
      this.ap.destroy();
    }
    if (this.vurl != null && this.dp) {
      this.dp.destroy();
    }
  },
  methods: {
    initAliplayer() {
      console.log('initAliplayer')
      if (this.loadap && this.wavurl != null) {
        this.ap = new this.$aplayer({
          container: document.getElementById('aplayer'),
          // mini: true,
          mutex: false,
          listFolded: false,
          listMaxHeight: 90,
          lrcType: 3,
          volume: 1,
          loop: 'none',
          theme: '#139b86',
          audio: [{
            url: this.wavurl
          }]
        });
        // const _this = this;
        // this.wavesurfer = this.$WaveSurfer.create({
        //   container: this.$refs.waveform,
        //   audioRate: this.speed,
        //   barWidth: 3,
        //   waveColor: '#54595F',
        //   progressColor: 'hsla(200, 100%, 30%, 0.5)',
        //   plugins: [
        //     _this.$cursor.create({
        //       showTime: true,
        //       opacity: 1,
        //       customShowTimeStyle: {
        //         'background-color': '#000',
        //         color: '#fff',
        //         padding: '2px',
        //         'font-size': '10px'
        //       }
        //     })
        //   ]
        // });
        // this.wavesurfer.load(this.wavurl);
        // this.wavesurfer.setVolume(0);
        // this.wavesurfer.on('seek', function(e) {
        //   console.log(4);
        //   const wavecurrentTime = _this.wavesurfer.getCurrentTime();
        //   console.log(wavecurrentTime);
        //   _this.isapseek = false;
        //   _this.ap.seek(wavecurrentTime);
        // });
        // this.ap.on('play', function() {
        //   _this.wavesurfer.play();
        // });
        // this.ap.on('pause', function() {
        //   _this.wavesurfer.pause();
        // });
        // this.ap.on('suspend', function() {
        //   _this.wavesurfer.stop();
        // });
        // this.ap.on('seeked', function(v) {
        //   let currentTime = v.path[0].currentTime;
        //   const wavecurrentTime = _this.wavesurfer.getCurrentTime();
        //   currentTime = currentTime - wavecurrentTime;
        //   if (_this.isapseek) {
        //     _this.wavesurfer.skip(currentTime);
        //   } else {
        //     _this.isapseek = true;
        //   }
        // });
      }
      console.log(this.vurl)
      if (this.loaddp && this.vurl != null) {

        console.log(this.vurl)
        let live = false;
        if (/rtmp/.test(this.vurl)) {
          live = true;
        }
        this.dp = new this.$dplayer({
          container: document.getElementById('dplayer'),
          autoplay: true,
          live: live,
          volume: 1,
          mutex: true,
          screenshot: true,
          hotkey: true,
          // danmaku: {
          //   id: this.tid,
          //   api: `/api/danmu`,
          //   token: 'tokendemo',
          //   user: this.author.username,
          //   maximum: 1000, // 弹幕最大数量
          //   bottom: '15%', // 弹幕距离播放器底部的距离，防止遮挡字幕，取值形如: '10px' '10%'
          //   unlimited: false, // 海量弹幕模式，即使重叠也展示全部弹幕，请注意播放器会记忆用户设置，用户手动设置后即失效
          //   addition: [`https://www.fireself.cn/api/bdm?bvid=${this.bvid}`]
          // },
          // subtitle: {
          //   url: this.suburl,
          //   type: 'webvtt',
          //   fontSize: '25px',
          //   bottom: '10%',
          //   color: '#b7daff'
          // },
          video: {
            url: this.vurl, // 单清晰度视屏播放
            defaultQuality: 0
          }
        });
      }
    },
    /* IFTRUE_default*/
    async toCategoryPage() {
      await this.getSearchIds();
      if (this.search_ids === '') {
        this.search_ids = this.category.id;
      }
      const searchIds = this.search_ids.split(',');
      const query = [];
      if (searchIds.length > 0) {
        searchIds.forEach(item => {
          query.push(`search_ids=${item}`);
        });
        this.$router.push(`/category/${this.category.id}?${query.join('&')}`);
      }

      this.$router.push({
        path: `/category/${this.category.id}`,
        query: { search_ids: this.search_ids }
      });
    },
    async getSearchIds() {
      await this.$store.dispatch('jv/get', ['categories', {}]).then((res) => {
        const resData = [...res] || [];
        this.handleData(resData);
      }, (e) => {
        this.handleError(e);
      });
    },
    handleData(data) {
      data.forEach((item) => {
        if (this.category.id === item.id) {
          this.search_ids = item.search_ids;
        }
      });
    },
    /* FITRUE_default*/
    formatTopicHTML(html) {
      // return s9e.parse(html);
      const _html = stringToLink(html);
      return s9e.parse(_html);
    },
    addTextHideCover() {
      if (this.threadType !== 1) return;
      if (!this.unpaid) return;
      this.$nextTick(() => {
        const contentHtml = document.querySelector('.content-html');
        if (parseInt(contentHtml.offsetHeight) > 100) {
          contentHtml.classList.add('hide-cover');
        }
      });
    },
    removeTextHideCover() {
      const contentHtml = document.querySelector('.content-html');
      if (contentHtml) contentHtml.classList.remove('hide-cover');
    },
    openVideo() {
      if (!this.isLogin()) return;
      if (this.unpaid) return this.$emit('payForThread');
      this.showVideoPop = true;
    },
    play(file) {
      if (!this.isLogin()) return;
      if (this.unpaid) return this.$emit('payForThread');
      if (this.currentAudio.id !== file.id) {
        this.resetAudio(this.currentAudio.audio);
        this.currentAudio.url = file.mediaUrl;
        this.currentAudio.id = file.id;
        this.currentAudio.audio.src = this.currentAudio.url;
        this.currentAudio.isLoading = true;
        this.currentAudio.audio.load();
      }
      window.setTimeout(() => {
        this.currentAudio.audio.play();
        this.currentAudio.isPlay = true;
        this.currentAudio.audio.addEventListener(
          'timeupdate',
          this.onProgressing
        );
        this.currentAudio.audio.addEventListener('ended', this.onEnded);
      }, 0);
    },
    onProgressing() {
      if (this.currentAudio.seeking) return;
      this.currentAudio.isLoading = false;
      this.currentAudio.duration = this.currentAudio.audio.duration;
      this.currentAudio.currentTime = this.currentAudio.audio.currentTime;
    },
    onEnded() {
      this.resetAudio(this.currentAudio.audio);
    },
    resetAudio(audio) {
      audio.removeEventListener('timeupdate', this.onProgressing);
      audio.removeEventListener('ended', this.onEnded);
      this.currentAudio.isPlay = false;
      this.currentAudio.duration = '';
      this.currentAudio.currentTime = '';
    },
    pause() {
      this.currentAudio.isLoading = false;
      this.currentAudio.isPlay = false;
      this.currentAudio.audio.pause();
    },
    seek(time) {
      this.currentAudio.seeking = false;
      this.currentAudio.currentTime = time;
      this.currentAudio.audio.currentTime = time;
    },
    seeking(time) {
      this.currentAudio.seeking = true;
      this.currentAudio.currentTime = time;
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
@import "@/assets/css/highlight.scss";
/*IFTRUE_pay*/
@import '@/assets/css/variable/color_pay.scss';
/*FITRUE_pay*/

article {
  margin-top: 20px;

  > .title {
    font-weight: bold;
    font-size: 20px;
    // word-break: break-all;
    word-wrap: break-word;
    word-break: normal;
    max-width: 650px;
    /*IFTRUE_pay*/
    max-width: 100% !important;
    /*FITRUE_pay*/
  }

   ::v-deep .content-html {
    max-width: 650px;
    margin-top: 22px;
    /*IFTRUE_pay*/
    max-width: 880px !important;
    code {
      white-space: pre-wrap;
    }
    /*FITRUE_pay*/

    &.hide-cover {
      position: relative;

      &:after {
        position: absolute;
        display: block;
        left: 0;
        bottom: 0;
        content: "";
        width: 100%;
        height: 100px;
        background: linear-gradient(
          rgba(255, 255, 255, 0),
          rgba(255, 255, 255, 1)
        );
      }
    }
  }

  @media screen and (max-width: 1005px) {
    .title {
      font-size: 18px;
    }
  }

  > .images {
    cursor: pointer;
    margin-top: 30px;
    /*IFTRUE_pay*/
    margin: 30px auto 0;
    /*FITRUE_pay*/
    width: 660px;
    > .image {
      width: 200px;
      height: 200px;
      border-radius: 5px;
      margin-right: 20px;
      margin-bottom: 20px;
      > .image-slot {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
        background: #f5f7fa;
        color: #909399;
        font-size: 22px;
      }
    }
  }

  @media screen and (max-width: 1005px) {
    > .images {
      max-width: 470px;
      width: 100%;
      > .image {
        width: 150px;
        height: 150px;
        border-radius: 5px;
        margin-right: 5px;
        margin-bottom: 5px;
      }
    }
  }

  > .container-attachment {
    margin-top: 30px;
    > .name {
      color: #6d6d6d;
      font-weight: bold;
      font-size: 16px;
    }
  }

  > .container-video-img-cover {
    margin-top: 30px;
    > .warp-video-img-cover {
      cursor: pointer;
      position: relative;
      display: inline-block;
      > .video-img-cover {
        display: block;
        max-width: 400px;
      }
      > .icon-play {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
      }
    }
  }

  > .hide-content-tip {
    margin-top: 20px;
    font-size: 16px;
    text-align: center;
  }

  > .tag {
    height: 25px;
    padding: 0 8px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border-radius: 12.5px;
    margin-right: 15px;
    margin-top: 30px;
    background: $background-color-grey;
    border: 1px solid $background-color-grey;
    color: #777;
    font-size: 12px;
    cursor: pointer;
    &:hover {
      color: $color-blue-base;
      background: #fff;
      border: 1px solid $color-blue-base;
    }
  }
  .location {
    display: inline-block;
    background: #f7f7f7;
    border-radius: 13px;
    font-size: 12px;
    color: #777777;
    padding: 4px 10px;
    line-height: 16px;
    margin-top: 10px;
    background: $background-color-grey;
    border: 1px solid $background-color-grey;
    &:hover {
      color: $color-blue-base;
      background: #fff;
      border: 1px solid $color-blue-base;
      .icon {
        fill: $color-blue-base;
      }
    }
    .flex {
      display: flex;
      align-items: center;
    }
    .icon {
      margin-right: 4px;
      fill: #777;
    }
  }
  .product {
    margin: 45px auto 0;
    width: 100%;
  }
  .audio {
    margin: 45px auto 0;
  }
}
</style>
