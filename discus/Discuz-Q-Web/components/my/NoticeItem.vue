<template>
  <div class="notice-item">
    <div class="avatar">
      /*IFTRUE_default*/
      <div
        v-if="item.type === 'system'"
        class="system-avatar"
      >
        <!-- <svg-icon
          type="system-notice"
          class="icon"
        /> -->
        <img
          class="img-icon"
          :src="
            forums
              && forums.set_site
              && forums.set_site.site_favicon
              ? forums.set_site.site_favicon :require('../../assets/favicon_blue.png')
          "
          alt="站点logo"
        >
      </div>
      <avatar
        v-else
        :user="{ id: item.user_id,username: item.user_name, avatarUrl: item.user_avatar}"
        :size="50"
      />
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      <div
        v-if="item.type === 'system'"
        class="system-avatar"
      >
        <!-- <svg-icon
          type="system-notice"
          class="icon"
        /> -->
        <img
          class="img-icon"
          :src="
            forums
              && forums.set_site
              && forums.set_site.site_favicon
              ? forums.set_site.site_favicon :require('../../assets/favicon_red.png')
          "
          alt="站点logo"
        >
      </div>
      <avatar
        v-else
        :user="{ id: item.user_id,username: item.user_name, avatarUrl: item.user_avatar}"
        :size="40"
        :round="true"
      />
      /*FITRUE_pay*/
    </div>
    <div class="detail">
      <div class="top">
        <template v-if="item.type !== 'system'">
          <a
            v-if="item.user_name"
            :href="item.user_id === -1 ? 'javascript:void(0)' : `/user/${item.user_id}`"
            class="user-name"
          >{{ item.user_name }}
          </a>
          <span class="text">
            <span v-if="item.type === 'questioned'">
              <span v-if="item.is_answer === 0">
                <span style="color: #0000ff;">￥{{ item.amount }}</span>
                {{ $t('notice.questions') }}
              </span>
              <span v-if="item.is_answer === 1">
                {{ $t('notice.answersMe') }}
              </span>
            </span>
            <template v-else-if="item.type === 'receiveredpacket'">
              <template>
                来自{{ item.thread_username }}的红包
              </template>
            </template>
            <template v-else-if="item.type === 'threadrewarded'">
              <template>
                来自{{ item.thread_username }}的悬赏
              </template>
            </template>
            <template v-else-if="item.type === 'rewarded'">
              <template v-if="item.isScale">
                {{ item.order_type === 1
                  ? typeMap['registerScale'] : item.order_type === 2
                    ? typeMap['rewardScale'] : item.order_type === 3
                      ? typeMap['payScale'] : typeMap[item.type] }}
              </template>
              <template v-else-if="item.order_type === 3">{{ typeMap['payMe'] }}</template>
              <template v-else-if="item.order_type === 5">{{ typeMap['questions'] }}</template>
              <template v-else-if="item.order_type === 6">{{ typeMap['watchedMe'] }}</template>
              <template v-else-if="item.order_type === 7">{{ typeMap['payMe'] }}</template>
              <template v-else>{{ typeMap[item.type] }}</template>
            </template>
            <template v-else>{{ typeMap[item.type] }}</template>
          </span>
        </template>
        <div
          v-if="item.title && item.type === 'system'"
          class="user-name"
        >{{ item.title }}</div>
        <div class="time">{{ timerDiff(item.created_at) + $t('topic.before') }}</div>
      </div>
      <nuxt-link
        v-if="item.type === 'liked'"
        :to="`/thread/${item.thread_id}`"
        class="post-content"
        v-html="$xss(item.post_content)"
      />
      <nuxt-link
        v-if="item.post_content && item.type !== 'liked'"
        :to="`/thread/comment?threadId=${item.thread_id}&commentId=${item.reply_post_id !== 0
          ? item.reply_post_id : item.post_id}`"
        class="post-content"
        v-html="$xss(item.post_content)"
      />
      <nuxt-link
        v-if="item.answer_content"
        :to="`/thread/${item.thread_id}`"
        class="post-content"
        v-html="$xss(item.answer_content)"
      />
      <nuxt-link
        v-if="(item.thread_title || item.content) && item.type !== 'system'"
        :to="`/thread/${item.thread_id}`"
        class="thread"
        target="_blank"
      >
        <div class="thread-user-name">{{ item.thread_username }}:</div>
        <div class="thread-title">
          <div
            v-if="item.thread_title"
            v-html="$xss(item.thread_title)"
          />
          <div
            v-else-if="item.content"
            v-html="$xss(item.content)"
          />
        </div>
        <div class="to-detail">{{ $t('notice.toDetail') }}</div>
      </nuxt-link>
      <div
        v-if="item.type === 'system'"
        class="post-content"
        @click="toDetail"
        v-html="$xss(item.content)"
      />
      <div
        v-if="item.type === 'withdrawal'"
        class="thread"
      >
        <template v-if="item.cash_status === 2">{{ $t('notice.approved') }}</template>
        <template v-if="item.cash_status === 3">{{ $t('notice.unapproved') }}</template>
        <template v-if="item.cash_status === 5">{{ $t('notice.applicationHasBeenPaid') }}</template>
      </div>
      <div
        v-if="item.type === 'withdrawal' && item.cash_actual_amount"
        class="amount green"
      >
        - {{ $t('post.yuanItem') + item.cash_actual_amount }}
      </div>
      <div
        v-if="
          (item.type === 'rewarded'
            || item.type === 'receiveredpacket'
            || item.type === 'threadrewarded'
          ) && item.amount"
        class="amount"
      >
        + {{ $t('post.yuanItem') + item.amount }}
      </div>
    </div>
  </div>
</template>
<script>
import handleError from '@/mixin/handleError';
import timerDiff from '@/mixin/timerDiff';
export default {
  name: 'NoticeItem',
  mixins: [timerDiff, handleError],
  props: {
    item: {
      type: Object,
      default: () => { }
    }
  },
  data() {
    return {
      typeMap: {
        related: this.$t('notice.relatedMe'),
        replied: this.$t('notice.repliedMe'),
        liked: this.$t('notice.likedMe'),
        rewarded: this.$t('notice.rewardedMe'),
        payMe: this.$t('notice.payMe'),
        registerScale: this.$t('notice.registerScale'),
        rewardScale: this.$t('notice.rewardScale'),
        payScale: this.$t('notice.payScale'),
        system: this.$t('notice.system'),
        questions: this.$t('notice.payQuestions'),
        watchedMe: this.$t('notice.watchedMe'),
        payedMe: this.$t('notice.payedMe'),
        answersMe: this.$t('notice.answersMe'),
        questioned: this.$t('notice.questions')
      }
    };
  },
  computed: {
    forums() {
      return this.$store.state.site.info.attributes || {};
    }
  },
  methods: {
    toDetail() {
      console.log(this.item);
      if (!this.item.thread_id) return;
      if (this.item.raw.tpl_id === 4) return this.$message.warning(this.$t('notice.underReview'));
      if (this.item.raw.tpl_id === 6) return this.$message.warning(this.$t('notice.threadDeleted'));
      this.$router.push(`/thread/${this.item.thread_id}`);
    }
  }
};
</script>
<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_pay*/
@import '@/assets/css/variable/color_pay.scss';
/*FITRUE_pay*/
@import "@/assets/css/variable/mixin.scss";
.notice-item {
  padding: 30px;
  display: flex;
  position: relative;
  @media screen and (max-width: 1005px) {
    padding: 15px;
  }
  &:after {
    content: "";
    position: absolute;
    bottom: 0;
    right: 0;
    width: calc(100% - 30px);
    height: 1px;
    background: $line-color-base;
    @media screen and (max-width: 1005px) {
      width: calc(100% - 15px);
    }
  }
  .avatar {
    margin-right: 15px;
    .system-avatar {
      padding: 10px;
      border: 1px solid #ededed;
      border-radius: 5px;
      /*IFTRUE_pay*/
      border-radius: 50%;
      /*FITRUE_pay*/
      .icon {
        font-size: 34px;
      }
      .img-icon {
        width: 30px;
        height: 30px;
      }
    }
  }
  .detail {
    flex: 1;
    /*IFTRUE_pay*/
    margin-right: 35px;
    /*FITRUE_pay*/
    font-size: 16px;
    @media screen and (max-width: 1005px) {
      font-size: 14px;
    }
    .top {
      /*IFTRUE_pay*/
      display: flex;
      align-items: center;
      height: 40px;
      /*FITRUE_pay*/
      .user-name {
        font-weight: bold;
      }
      .text {
        color: #6d6d6d;
        margin-left: 10px;
      }
      .time {
        /*IFTRUE_pay*/
        flex: 1;
        text-align: right;
        margin-right: 10px;
        /*FITRUE_pay*/
        color: #b5b5b5;
        font-size: 14px;
      }
    }
    ::v-deep p {
      font-size: 16px;
    }

    ::v-deep img {
      height: 22px;
    }
    @media screen and (max-width: 1005px) {
      font-size: 14px;
      ::v-deep p {
        font-size: 14px;
      }
      ::v-deep img {
        height: 20px;
      }
    }
    .post-content {
      padding: 10px 0 0;
      display: block;
      word-break: break-all;
      cursor: pointer;
      @include text-hidden(3);
      &:hover {
        color: $color-blue-base;
      }
    }
    .thread {
      background: #f5f5f5;
      padding: 13px 15px;
      display: flex;
      align-items: center;
      margin-top: 10px;
      .thread-user-name {
        font-weight: bold;
        margin-right: 10px;
      }
      .thread-title {
        flex: 1;
        @include text-hidden();
        &:hover {
          color: $color-blue-base;
        }
      }
      .to-detail {
        color: $color-blue-base;
      }
    }
    .amount {
      font-size: 18px;
      margin-top: 12px;
      font-weight: bold;
      color: #fa5151;
    }
    .green{
      color: #09BB07;
    }
  }
}
</style>
