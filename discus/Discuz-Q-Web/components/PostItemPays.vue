<template>
  <div class="add-padding-line">
    <div class="post-container">
      <!-- 精华帖标志 -->
      <div v-if="item.thread.isEssence" class="essence">
        <img src="../assets/pay_imgs/essenc.png" alt="">
      </div>
      <!-- 帖子头部信息 -->
      <div class="post-header">
        <!-- 头像 -->
        <avatar
          v-if="
            item.thread.type === 5 &&
              item.thread.extension.question &&
              item.thread.extension.question.isAnswer === 1
          "
          :user="{
            id: item.thread.extension.question.beUserId,
            username: item.thread.extension.question.beUserName,
          }"
          size="40"
          :round="true"
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
          size="40"
          :round="true"
          :prevent-jump="canDetail"
          class="avatar"
        />
        <!-- 昵称 -->
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
          <img
            :src="item.user.growup.growupimageUrl"
            class="growup-img"
          >
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
          :to="
            item.user.pid > 0
              ? `/user/${item.user.pid}`
              : ''
          "
          class="user-info"
        >
          <span class="user-name">{{ item.user.userName }}</span>
          <img
            :src="item.user.growup.growupimageUrl"
            class="growup-img"
          >
          <span
            v-if="item.group"
            class="admin"
          >
            {{ item.group.isDisplay ? `(${item.group.groupName})` : "" }}
          </span>
        </nuxt-link>


        <!-- 回答状态 -->
        <div
          v-if="
            item.type === 5
              && item.thread.extension.question
              && item.thread.extension.question.isAnswer === 1
          "
          class="answered"
        >{{ $t('topic.answered') }}</div>
      </div>
      <!-- 帖子主体内容 -->
      <div class="post-content">
        <!-- 首贴-->
        <div class="first-post" @click.self="toDetail">

          <!-- 首帖上部描述区域 -->
          <div class="first-post-desc" @click="onClickContent">
            <div class="first-post-desc__tips">
              <!-- 图标类型 -->
              <img
                v-if="(item.thread.type === 0 || item.thread.type === 1) && item.thread.isRedPacket"
                src="../assets/pay_imgs/post_red.png"
                class="first-post-img"
              >
              <img
                v-if="
                  item.thread.type === 5
                    && item.rewards
                    && item.rewards.type === 0
                "
                src="../assets/pay_imgs/post_reward.png"
                class="first-post-img"
              >
              <img
                v-else-if="item.thread.type === 5"
                src="../assets/pay_imgs/post_question.png"
                class="first-post-img"
              >
              <img
                v-if="item.thread.type === 6"
                src="../assets/pay_imgs/post_goods.png"
                class="first-post-img"
              >
              <img
                v-if="item.thread.type === 7"
                src="../assets/pay_imgs/post_album.png"
                class="first-post-img"
              >
              <!-- 小提示文字 帖子发布了，悬赏问答悬赏金额 -->

              <span
                v-if="
                  item.thread.type === 5
                    && item.rewards
                    && item.rewards.type === 0
                "
                class="first-post-tip"
              >【￥{{ item.rewards.money }}】</span>
              <!-- 付费标志 -->
              <img
                v-if="
                  (parseFloat(item.thread.price) > 0 || parseFloat(item.thread.attachmentPrice) > 0)
                "
                src="../assets/pay_imgs/goldcoin.png"
                class="first-post-coin"
              >
            </div>
            <!-- 仅仅帖子有标题 -->
            <p v-if="item.thread.type === 1" class="content">{{ item.thread.title }}</p>
            <!-- 内容展示 ，没有标题就用内容展示-->
            <p
              v-else
              class="content"
              v-html="$xss(formatTopicHTML(item.thread.summary))"
            />
            <p
              v-if="item.thread.type !== 0 && item.thread.type !== 3"
              class="content"
              v-html="$xss(formatTopicHTML(item.thread.description))"
            />
            <!-- 问答贴回答状态 @人 -->
            <span v-if="item.thread.type === 5">
              <!-- 未回答 -->
              <template
                v-if="
                  item.thread.extension &&
                    item.thread.extension.question && item.thread.extension.question.isAnswer === 0"
              >
                <nuxt-link
                  v-if="
                    item.thread.extension && item.thread.extension.question && item.thread.extension.question.beUserId"
                  :to="
                    item.thread.extension.question.beUserId > 0
                      ? `/user/${item.thread.extension.question.beUserId}`
                      : ''
                  "
                  class="questioner"
                >@{{ item.thread.extension.question.beUserName }}</nuxt-link>
              </template>
              <!-- 已回答 -->
              <template
                v-if="item.thread.extension &&
                  item.thread.extension.question && item.thread.extension.question.isAnswer === 1"
              >
                <nuxt-link
                  v-if="item.user"
                  :to="item.user.pid > 0 ? `/user/${item.user.pid}` : ''"
                  class="questioner"
                >@{{ item.user.userName }}</nuxt-link>
              </template>
            </span>
          </div>
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
      </div>
      <!-- 发布时间 处理按钮 -->
      <div class="post-footer">
        <div class="time">
          <template v-if="item.thread.createdAt">
            {{ item.thread.isDraft ? $t("topic.saveAt") : $t("topic.publishAt") }}
            {{ item.thread.createdAt | formatDate }}
          </template>
        </div>
        <div v-if="!canDetail" class="handle-btn">
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
          <slot name="bottom-right" />
          <slot name="btn-edit" />
          <slot name="btn-delete" />
        </div>
      </div>
      <!-- 视频播放弹窗 -->
      <dplayer-pop
        v-if="showVideoPop"
        :cover-url="item.thread.extension.video.coverUrl"
        :url="item.thread.extension.video.mediaUrl"
        @remove="showVideoPop = false"
      />
    </div>
  </div>
</template>

<script>
import s9e from '@/utils/s9e';
import { time2MinuteOrHour } from '@/utils/time';
import { extensionList } from '@/constant/extensionList';
import handleError from '@/mixin/handleError';
import loginAbout from '@/mixin/loginAbout';

export default {
  name: 'PostItemPays',
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
    },
    //+积分
    integral() {
      return this.forums && this.forums.set_site  && this.forums.set_site.site_integral && this.forums.set_site.site_integral[3].ivalue;
    },
    unintegral() {
      return this.forums && this.forums.set_site  && this.forums.set_site.site_integral && this.forums.set_site.site_integral[3].unvalue;
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
              if (this.integral == 0) {
                this.$message.success(
                  isLiked
                    ? this.$t('discuzq.msgBox.likeSuccess')
                    : this.$t('discuzq.msgBox.cancelLikeSuccess')
                );
              } else {
                this.$message.success(
                  isLiked
                    ? this.$t('discuzq.msgBox.likeSuccess')+' 奖励积分：+'+this.integral
                    : this.$t('discuzq.msgBox.cancelLikeSuccess')+' 减掉积分：-'+this.unintegral
                );
              }

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
      if (this.item.isDraft) return;
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
@import "@/assets/css/variable/color_pay.scss";
@import "@/assets/css/variable/mixin.scss";
@mixin background(){
  background: #fff;
  border-radius: 5px;
  box-shadow: 0 3px 3px rgba(0, 0, 0, 0.03);
}
.add-padding-line{
  padding-bottom: 10px;
}
.post-container {
  position: relative;
  padding: 16px 20px 0;
  // margin-bottom: 10px;
  overflow: hidden;
  @include background();
  @media screen and (max-width: 1005px){
    padding: 14px 20px 0;
  }
  // 精华标志
  .essence {
    position: absolute;
    top: 0;
    right: 0;
    @media screen and (max-width: 1005px){
      width: 50px;
      height: 50px;
      top: 14px;
      right: 20px;
      img{
        width: 100%;
      }
    }
  }
  // 成长值图标
  .growup-img {
    margin-left: 10px;
  }
  // 帖子头部信息
  .post-header {
    position: relative;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    margin: 15px 0;
    height: 40px;
    line-height: 40px;
    .user-info {
      display: flex;
      align-items: center;
      margin-left: 10px;
      .user-name {
        max-width: 250px;
        font-size: 16px;
        font-weight: bold;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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
    .answered{
      position: absolute;
      top: 50%;
      right: 40px;
      margin-top: -10px;
      padding:0 12px;
      height: 24px;
      line-height: 24px;
      font-size: 13px;
      color: $color-red-pay;
      border-radius: 12px;
      background: rgba(61, 93, 234,0.08);
    }
  }

  // 中部内容
  .post-content {
    padding: 8px 8px;
    background: #fafafa;
    overflow: hidden;
    @media screen and (max-width: 1005px) {
      padding: 8px 20px;
    }
    &:hover{
      background: $color-background-hover;
    }
    // 首帖
    .first-post {
      cursor: pointer;
      // 首帖描述区
      .first-post-desc{
        max-height: 100px;
        min-height: 50px;
        overflow: hidden;
        // 描述小提示
        .first-post-desc__tips{
          float: left;
          clear: both;
          display: flex;
          align-items: center;
          height: 50px;
          .first-post-img{
            height: 30px;
            width: 66px;
            margin-right: 5px;
          }
          .first-post-tip{
            font-size: 16px;
            font-weight: bold;
            color: #600111;
          }
          .first-post-coin{
            padding-right: 3px;
          }
        }
        .contents {
          font-size: 16px !important;
          color: $color-black;
          line-height: 40px;
          word-break: break-all;
        }
        ::v-deep .content {
          font-size: 18px !important;
          color: $color-black;
          line-height: 50px;
          word-break: break-all;
          p {
            line-height: 50px;
            word-break: break-all;
          }
          a {
            color: $color-blue-base;
            &:hover {
              border-bottom: 1px solid $color-blue-base;
            }
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
          @media screen and (max-width: 1005px) {
            p {
              font-size: 15px;
            }
            img {
              max-height: 150px;
            }
            .qq-emotion {
              height: 20px;
              margin-top: -1px;
            }
          }
        }
        .questioner{
          font-size: 18px !important;
          color: $color-red-pay;
          padding: 5px 10px;
        }
      }
    }
    // 图片
    .images {
      padding: 20px 0 10px;
      display: flex;
      .image {
        width: 189px;
        height: 189px;
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
        color: $color-black;
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
        background-color: $color-white;
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
  }
  // 底部时间 操作按钮
  .post-footer{
    display: flex;
    justify-content: space-between;
    padding: 26px 20px;
    font-size: 14px;
    color: $font-color-grey;
    .time {
      flex: 1;
    }
    .handle-btn {
      width: 55%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      .btn {
        flex: 0 1 110px;
        margin-left: 30px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        font-size: 14px;
        color: $font-color-grey;
        cursor: pointer;
        overflow: hidden;
        &:hover {
          color: $color-blue-base;
        }
        .icon {
          margin-right: 5px;
          font-size: 17px;
        }
      }
    }
  }
}
</style>
