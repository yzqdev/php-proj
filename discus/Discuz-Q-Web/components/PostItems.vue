<template>
  <div class="post-container" :style="{ padding: padding }">
    <div v-if="item.thread.isEssence" class="essence">
      <svg-icon type="index-essence" />
    </div>
    <avatar
      v-if="
        item.thread.type === 5 &&
          item.thread.extension.question &&
          item.thread.extension.question.isAnswer === 1
      "
      :user="{
        id: item.thread.extension.question.beUserId,
        username: item.thread.extension.question.beUserName
      }"
      :prevent-jump="canDetail"
      class="avatar"
    />
    <avatar
      v-else-if="item.user"
      :user="{
        id: item.user.pid,
        username: item.user.userName,
        avatarUrl: item.user.avatar,
        isReal: item.user.isRealName,
      }"
      :prevent-jump="canDetail"
      class="avatar"
    />
    <div class="main-cont">
      <div class="top-flex">
        <div class="top-user-info">
          <nuxt-link
            v-if="
              item.thread.type === 5 &&
                item.thread.extension.question &&
                item.thread.extension.question.isAnswer === 1
            "
            :to="item.user.pid > 0 ? `/user/${item.thread.extension.question.beUserId}` : ''"
            class="user-info"
          >
            <span class="user-name">{{ item.thread.extension.question.beUserName }}</span>
            <span
              v-if="
                item.thread.extension &&
                  item.thread.extension.question &&
                  item.thread.extension.question.group &&
                  item.thread.extension.question.group.isDisplay
              "
              class="admin"
            >
              ({{ item.thread.extension.question.group.groupName }})
            </span>
          </nuxt-link>
          <nuxt-link
            v-else-if="item.user"
            :to="item.user.pid > 0 ? `/user/${item.user.pid}` : ''"
            class="user-info"
          >
            <span class="user-name">{{ item.user.userName }}</span>
            <span
              v-if="item.group"
              class="admin"
            >
              {{ item.group.isDisplay ? `(${item.group.groupName})` : "" }}
            </span>
          </nuxt-link>
          <div
            v-if="
              item.thread.type === 5 && item.thread.extension.question && item.thread.extension.question.isAnswer === 1
            "
            class="answered"
          >
            {{ $t("topic.answered") }}
          </div>
        </div>
        <div class="time">
          <template
            v-if="item.thread.createdAt"
          >{{ $t("topic.publishAt") }}
            {{ item.thread.createdAt | formatDate }}</template>
        </div>
      </div>
      <!-- 问答贴 -->
      <div v-if="item.thread.type === 5" class="question-user">
        <!-- 未回答 -->
        <template v-if="item.thread.extension.question && item.thread.extension.question.isAnswer === 0">
          {{ $t("topic.be") }}
          <nuxt-link
            v-if="item.thread.extension.question"
            :to="
              item.thread.extension.question.beUserId > 0
                ? `/user/${item.thread.extension.question.beUserId}`
                : ''
            "
            class="grey"
          >@{{ item.thread.extension.question.beUserName }}</nuxt-link>
          {{ $t("topic.question") }}
        </template>
        <!-- 已回答 -->
        <template v-if="item.thread.extension.question && item.thread.extension.question.isAnswer === 1">
          {{ $t("topic.answer") }}
          <nuxt-link
            v-if="item.user"
            :to="item.user.pid > 0 ? `/user/${item.user.pid}` : ''"
            class="grey"
          >@{{ item.user.userName }}</nuxt-link>
          {{ $t("topic.of") }}{{ $t("topic.question") }}
        </template>
      </div>
      <template>
        <div class="first-post" @click.self="toDetail">
          <a
            :href="`/thread/${item && item.thread.pid}`"
            @click.prevent="onClickContent"
          >
            <div v-if="item.thread.type === 1" class="title">
              {{ $t("home.released") }}
              <svg-icon
                v-show="
                  parseFloat(item.thread.price) > 0 ||
                    parseFloat(item.thread.attachmentPrice) > 0
                "
                type="pay-yuan"
                class="blue icon-pay-yuan-inline"
              />
              <span class="blue">{{ item.thread.title }}</span>
            </div>
            <div v-else class="content">
              <svg-icon
                v-if="item.thread.type === 5"
                type="question-icon"
                class="icon-pay-yuan blue"
              />
              <svg-icon
                v-else-if="item.thread.type === 6"
                type="product-icon"
                class="icon-pay-yuan blue"
              />
              <svg-icon
                v-else-if="
                  parseFloat(item.thread.price) > 0 ||
                    parseFloat(item.thread.attachmentPrice) > 0
                "
                type="pay-yuan"
                class="icon-pay-yuan grey"
              />
              <div
                :class="{
                  'content-block':
                    item.thread.type === 5 ||
                    item.thread.type === 6 ||
                    parseFloat(item.thread.price) > 0,
                  blue: item.thread.type === 5,
                }"
                v-html="$xss(formatTopicHTML(item.thread.summary))"
              />
            </div>
          </a>
          <!-- 图片 -->
          <div
            v-if="item.attachment && item.attachment.length > 0"
            v-viewer="{ url: 'data-source' }"
            class="images"
            @click.self="toDetail"
          >
            <el-image
              v-for="(image, index) in item.attachment.slice(0, 3)"
              :key="index"
              :class="{ image: true, infoimage: infoimage }"
              :src="image.thumbUrl"
              :data-source="unpaid ? '' : image.url"
              :alt="image.filename"
              fit="cover"
              :lazy="lazy"
              @click.self="onClickImage"
            >
              <div slot="placeholder" class="image-slot">
                <i class="el-icon-loading" />
              </div>
            </el-image>
          </div>
          <div
            v-if="item.attachment && item.attachment.length > 3"
            class="image-count"
            @click="toDetail"
          >
            {{ $t("home.total") }} {{ item.attachment.length }}
            {{ $t("home.seeAllImage") }}
          </div>
          <!-- 视频 -->
          <div
            v-if="item.thread.type === 2 && item.thread.extension.video"
            class="video-main"
            @click.stop="openVideo"
          >
            <el-image
              v-if="item.thread.extension.video.coverUrl"
              class="video-img-cover"
              :src="item.thread.extension.video.coverUrl"
              :alt="item.thread.extension.video.fileName"
              fit="cover"
              :lazy="lazy"
            />
            <div v-else class="no-cover">{{ $t("home.noPoster") }}</div>
            <svg-icon type="video-play" class="video-play" />
          </div>
          <!-- 附件 -->
          <div
            v-if="
              item.attachments &&
                item.attachments.length > 0
            "
            class="attachment"
            @click="toDetail"
          >
            <svg-icon
              :type="extensionValidate(item.attachments[0].extension)"
            />
            <div class="name text-hidden">
              {{ item.attachments[0].fileName }}
            </div>
            <div v-if="item.attachments.length > 1" class="total">
              {{
                $t("home.etc") +
                  item.attachments.length +
                  $t("home.attachmentTotal")
              }}
            </div>
          </div>
        </div>
        <!-- 商品 -->
        <product-items
          v-if="item.thread.type === 6"
          :item="item.thread.extension && item.thread.extension.goods"
        />
        <!-- 语音 -->
        <div v-if="item.thread.type === 4" @click.self="toDetail">
          <APlayers
            :file="item.thread && item.thread.extension && item.thread.extension.video"
            :current-audio="currentAudio"
            @play="play"
            @pause="pause"
            @seek="seek"
            @seeking="seeking"
          />
          <audio
            :id="`audio-players${item.thread && item.thread.pid}`"
            class="audio-players"
            :src="currentAudio.url"
            style="display: none"
          />
        </div>
        <!-- 位置 -->
        <nuxt-link
          v-if="item.thread.location"
          :to="`/topic/position?longitude=${item.thread.longitude}&latitude=${item.thread.latitude}`"
          class="location"
        >
          <span class="flex">
            <svg-icon type="location" class="icon" />
            {{ item.thread.location }}
          </span>
        </nuxt-link>
        <!-- 操作 -->
        <div v-if="!canDetail" class="bottom-handle">
          <div class="left">
            <div
              v-if="showLike"
              class="btn like"
              :class="{ liked: isLiked }"
              @click="handleLike"
            >
              <svg-icon type="like" class="icon" @click="handleLike" />
              {{ isLiked ? $t("topic.liked") : $t("topic.like") }}
              {{ likeCount > 0 ? likeCount : "" }}
            </div>
            <div v-if="showComment" class="btn comment" @click="toDetail">
              <svg-icon type="post-comment" class="icon" />
              {{ $t("topic.comment") }}
              {{ item.thread.postCount > 0 ? item.thread.postCount : "" }}
            </div>
            <share-popover
              v-if="item.thread && item.thread.pid && showShare"
              :threads-id="item.thread.pid"
            >
              <div class="btn share">
                <svg-icon type="link" class="icon" />
                {{ $t("topic.share") }}
              </div>
            </share-popover>
            <slot name="btn-edit" />
            <slot name="btn-delete" />
          </div>
          <slot name="bottom-right" />
        </div>
      </template>
    </div>
    <!-- 视频播放弹窗 -->
    <dplayer-pop
      v-if="showVideoPop"
      :cover-url="item.thread.extension.video.coverUrl"
      :url="item.thread.extension.video.mediaUrl"
      @remove="showVideoPop = false"
    />
  </div>
</template>
<script>
import s9e from '@/utils/s9e';
import { time2MinuteOrHour } from '@/utils/time';
import { extensionList } from '@/constant/extensionList';
import handleError from '@/mixin/handleError';
import loginAbout from '@/mixin/loginAbout';

export default {
  name: 'PostItems',
  filters: {
    formatDate(date) {
      return time2MinuteOrHour(date);
    }
  },
  mixins: [handleError, loginAbout],
  props: {
    // 主题详情
    item: {
      type: Object,
      default: () => {}
    },
    // 是否显示点赞按钮
    showLike: {
      type: Boolean,
      default: true
    },
    // 是否显示评论按钮
    showComment: {
      type: Boolean,
      default: true
    },
    // 是否显示分享按钮
    showShare: {
      type: Boolean,
      default: true
    },
    // 是否图片懒加载
    lazy: {
      type: Boolean,
      default: true
    },
    infoimage: {
      type: Boolean,
      default: false
    },
    // 付费站点是否可查看详情
    canDetail: {
      type: Boolean,
      default: false
    },
    padding: {
      type: String,
      default: '20.5px 20px 30px'
    }
  },
  data() {
    return {
      loading: false,
      showVideoPop: false, // 视频弹窗
      isLiked: false, // 当前主题是否点赞
      currentAudio: {
        id: '',
        url: '',
        currentTime: '',
        duration: '',
        audio: '',
        seeking: false,
        isPlay: false,
        isLoading: false
      }
    };
  },
  computed: {
    // 当前主题是否付费
    unpaid() {
      return !(this.item.thread.paid || parseFloat(this.item.thread.price) === 0);
    },
    forums() {
      return this.$store.state.site.info.attributes || {};
    }
  },
  watch: {
    item: {
      handler(val) {
        this.isLiked = val.thread && val.thread.isLiked;
        this.likeCount = val.thread && val.thread.likedCount;
      },
      deep: true,
      immediate: true
    }
  },
  mounted() {
    // 初始化audio标签
    this.currentAudio.audio = document.getElementById(
      `audio-players${this.item && this.item.thread && this.item.thread.pid}`
    );
  },
  methods: {
    // 点赞
    handleLike() {
      if (!this.$store.getters['session/get']('isLogin')) {
        if (process.client) {
          this.$message.warning('请登录');
          window.setTimeout(() => {
            this.headerTologin();
          }, 1000);
        }
      } else {
        if (this.loading) return;
        if (!this.item.thread.canLike) {
          this.$message.error(this.$t('topic.noThreadLikePermission'));
          return;
        }
        this.loading = true;
        const isLiked = !this.isLiked;
        const params = {
          _jv: {
            type: 'posts',
            id:
              this.item.thread
              && this.item.thread.firstPostId
          },
          isLiked
        };
        return this.$store
          .dispatch('jv/patch', params)
          .then(
            () => {
              this.$message.success(
                isLiked
                  ? this.$t('discuzq.msgBox.likeSuccess')
                  : this.$t('discuzq.msgBox.cancelLikeSuccess')
              );
              if (isLiked) {
                this.likeCount += 1;
              } else {
                this.likeCount -= 1;
              }
              this.isLiked = isLiked;
              this.$emit('change');
            },
            (e) => {
              this.handleError(e);
            }
          )
          .finally(() => {
            this.loading = false;
          });
      }
    },
    // 跳转详情页
    toDetail() {
      if (this.item.thread.isDraft) return;
      if (!this.canViewPostsFn()) return;
      this.routerLink();
    },
    // 点击图片 判断是否付费， 未付费跳转详情页
    onClickImage() {
      if (!this.unpaid || !this.canViewPostsFn()) return;
      this.routerLink();
    },
    // 点击视频 判断是否付费， 未付费跳转详情页
    openVideo() {
      if (!this.canViewPostsFn()) return;
      if (this.unpaid) {
        this.routerLink();
      } else {
        // 由于使用长列表的优化的插件后，transform里面不能有fixed
        if (
          this.$route.path === '/'
          || this.$route.path === '/site/search'
          || this.$route.name === 'category-id'
        ) {
          this.$emit('showVideoFn', this.item.thread.extension.video);
        } else {
          this.showVideoPop = true;
        }
      }
    },
    // 详情路由
    routerLink() {
      window.open(`/thread/${this.item.thread && this.item.thread.pid}`, '_blank');
    },
    // 点击正文，使用事件委托判断a标签
    onClickContent(e) {
      if (this.item.thread.isDraft) return;
      const event = e || window.event;
      const target = event.target || event.srcElement;
      if (target.nodeName.toLocaleLowerCase() !== 'a') {
        this.toDetail();
      }
    },
    // 是否有查看详情的权限
    canViewPostsFn() {
      if (!this.item.thread.canViewPost) {
        if (!this.$store.getters['session/get']('isLogin')) {
          if (process.client) {
            this.$message.warning(this.$t('core.not_authenticated'));
            window.setTimeout(() => {
              this.headerTologin();
            }, 1000);
          }
        } else {
          this.$message.warning(this.$t('home.noPostingTopic'));
        }
        return false;
      }
      if (this.canDetail) {
        this.$message.warning(this.$t('topic.joinAfterView'));
        return false;
      }
      return true;
    },
    // 格式化html
    formatTopicHTML(html) {
      return s9e.parse(html);
    },
    // 根据附件格式显示不同svg
    extensionValidate(extension) {
      return extensionList.indexOf(extension.toUpperCase()) > 0
        ? extension.toUpperCase()
        : 'UNKNOWN';
    },
    // 语音播放
    play(file) {
      if (this.unpaid) {
        this.routerLink();
        return;
      }
      // 没有查看权限或audio标签没获取到
      if (!this.canViewPostsFn() || !this.currentAudio.audio) return;
      if (this.currentAudio.id !== file.pid) {
        this.resetAudio(this.currentAudio.audio);
        this.currentAudio.url = file.coverUrl || file.mediaUrl;
        this.currentAudio.id = file.pid;
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
        this.$emit('audioPlay', this.currentAudio.id);
      }, 0);
    },
    // 进度的展示
    onProgressing() {
      if (this.currentAudio.seeking) return;
      this.currentAudio.isLoading = false;
      this.currentAudio.duration = this.currentAudio.audio.duration;
      this.currentAudio.currentTime = this.currentAudio.audio.currentTime;
    },
    // 结束
    onEnded() {
      this.resetAudio(this.currentAudio.audio);
    },
    // 初始化audio
    resetAudio(audio) {
      if (!audio) return; // 防止audio不存在
      audio.removeEventListener('timeupdate', this.onProgressing);
      audio.removeEventListener('ended', this.onEnded);
      this.currentAudio.isPlay = false;
      this.currentAudio.duration = '';
      this.currentAudio.currentTime = '';
    },
    // 暂停
    pause() {
      if (!this.currentAudio.audio) return; // 防止audio不存在
      this.currentAudio.isLoading = false;
      this.currentAudio.isPlay = false;
      this.currentAudio.audio.pause();
    },
    // 进度跳拖动完成
    seek(time) {
      if (!this.currentAudio.audio) return; // 防止audio不存在
      this.currentAudio.seeking = false;
      this.currentAudio.currentTime = time;
      this.currentAudio.audio.currentTime = time;
    },
    // 进度跳拖动中
    seeking(time) {
      this.currentAudio.seeking = true;
      this.currentAudio.currentTime = time;
    }
  }
};
</script>
<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
@import "@/assets/css/variable/mixin.scss";
.post-container {
  position: relative;
  display: flex;
  padding: 20.5px 20px 30px;
  border-bottom: 1px solid $line-color-base;
  &:hover {
    background: rgba(229, 242, 255, 0.3);
    border-bottom: 1px solid #e7ecf2;
  }
  .blue {
    color: $color-blue-dark;
  }
  .essence {
    position: absolute;
    top: -5px;
    right: 20px;
    font-size: 23px;
  }
  .main-cont {
    flex: 1;
    margin-left: 15px;
    .top-flex {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
    }
    .first-post {
      cursor: pointer;
      max-width: 585px;
      @media screen and (max-width: 1005px) {
        max-width: 410px;
      }
    }
    .user-info {
      display: inline-flex;
      align-items: center;
      .user-name {
        font-weight: bold;
        font-size: 16px;
        display: flex;
        max-width: 250px;
        @include text-hidden();
        @media screen and (max-width: 1005px) {
          font-size: 14px;
        }
      }
      .admin {
        color: $font-color-grey;
        font-size: 12px;
        margin-left: 10px;
      }
    }
    .top-user-info{
      display: flex;
    }
    .answered {
      border: 1px solid #e7e7e7;
      background: #f7f7f7;
      height: 20px;
      line-height: 18px;
      padding: 0 12px;
      border-radius: 10px;
      margin-right: 10px;
      color: $font-color-grey;
      font-size: 12px;
      margin-left: 10px;
    }
    .time {
      color: $font-color-grey;
      font-size: 12px;
    }
    .title {
      display: inline-block;
      font-size: 16px;
      margin-bottom: 6px;
      word-wrap: break-word;
      word-break: normal;
      width: 100%;
    }
    .icon-pay-yuan-inline{
      font-size: 17px;
      display: inline-block;
      margin-right: 2px;
      vertical-align: middle;
      margin-top: -1px;
    }
    .icon-pay-yuan {
      height: 24px;
      font-size: 17px;
      position: absolute;
      top: 0;
      left: 0;
    }
    .grey {
      color: $font-color-grey;
    }
    .content-block {
      text-indent: 24px;
    }
    .question-user {
      font-size: 16px;
    }
    ::v-deep .content {
      position: relative;
      display: inline-block;
      @include text-hidden(4);
      line-height: 24px;
      font-size: 16px !important;
      color: #000;
      max-width: 585px;
      //max-height: 96px;
      margin-top: 3px;
      p,
      h1,
      h2,
      h3,
      h4,
      h5,
      h6 {
        font-size: 16px !important;
      }
      img {
        max-width: 100%;
        max-height: 200px;
        overflow: hidden;
      }
      .qq-emotion {
        height: 22px;
        vertical-align: middle;
        margin-top: -2px;
      }
      // a {
      //   color: $color-blue-dark;
      //   &:hover {
      //     border-bottom: 1px solid $color-blue-dark;
      //   }
      // }
      @media screen and (max-width: 1005px) {
        max-height: 80px;
        line-height: 20px;
        max-width: 410px;
        .qq-emotion {
          height: 20px;
          margin-top: -1px;
        }
      }
    }
    .images {
      padding: 20px 0 0;
      display: flex;
      .image {
        width: 190px;
        height: 190px;
        margin-right: 8px;
        @media screen and (max-width: 1005px) {
          width: 130px;
          height: 130px;
        }
        &:nth-child(3n) {
          margin-right: 0;
        }
        .image-slot {
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
      .infoimage {
        width: 125px;
        height: 125px;
        margin-right: 10px;
        @media screen and (max-width: 1005px) {
          width: 70px;
          height: 70px;
        }
        &:nth-child(3n) {
          margin-right: 0;
        }
        .image-slot {
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
    .image-count {
      font-size: 12px;
      color: $font-color-grey;
      text-align: right;
      margin-top: 10px;
    }
    .video-main {
      position: relative;
      margin-top: 10px;
      display: inline-block;
      .video-img-cover,
      .no-cover {
        width: 300px;
        height: 200px;
      }
      .no-cover {
        background: #f5f7fa;
        color: #909399;
        line-height: 200px;
        text-align: center;
      }
      .video-play {
        position: absolute;
        font-size: 40px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
      }
    }
    .attachment {
      display: flex;
      align-items: center;
      font-size: 16px;
      margin-top: 15px;
      @media screen and (max-width: 1005px) {
        font-size: 14px;
      }
      .name {
        max-width: 380px;
        max-height: 21px;
        margin-left: 5px;
        @media screen and (max-width: 1005px) {
          max-width: 350px;
          max-height: 19px;
        }
      }
      .total {
        color: #000000;
        margin-left: 16.5px;
      }
    }
    .location {
      display: inline-block;
      background: $background-color-grey;
      border-radius: 13px;
      border: 1px solid $background-color-grey;
      font-size: 12px;
      color: #777777;
      padding: 3px 10px;
      line-height: 16px;
      margin-top: 10px;
      transition: all 0.1s ease-in-out;
      &:hover {
        background-color: #fff;
        color: $color-blue-base;
        border-color: $color-blue-base;
      }
      .flex {
        display: flex;
        align-items: center;
      }
      .icon {
        margin-right: 4px;
      }
    }
    .bottom-handle {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 26px;
      font-size: 14px;
      .left {
        display: flex;
        align-items: center;
        flex: 1;
        justify-content: space-between;
        .btn {
          color: $font-color-grey;
          cursor: pointer;
          min-width: 100px;
          &:hover {
            color: $color-blue-base;
          }
        }
        .icon {
          margin-right: 5px;
        }
        .like {
          // padding: 10px 15px;
          // line-height: 1;
          // border-radius:2px;
          // border:1px solid transparent;
          // transition: all 0.1s ease-in;
          // &.liked{
          //   color:$color-blue-base;
          //   // padding: 10px 15px;
          //   // background: #E5F2FF;
          //   // &:hover{
          //   //   border-color: #D4E6FC;
          //   // }
          // }
          .icon {
            font-size: 15px;
          }
        }
        .comment .icon {
          font-size: 14px;
        }
        .share {
          min-width: auto;
          .icon {
            font-size: 13px;
          }
        }
        .edit{
          vertical-align: top;
        }
      }
      .reply-btn {
        display: inline-block;
        color: $color-blue-base;
        background: #ffffff;
        border: 1px solid $color-blue-base;
        border-radius: 2px;
        font-size: 14px;
        padding: 9.5px 20px;
        line-height: 1;
        outline: none;
        cursor: pointer;
        transition: all 0.1s ease-in-out;
        &:hover {
          background: #e5f2ff;
          border-color: #d4e6fc;
        }
      }
    }
  }
}
</style>
