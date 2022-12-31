<template>
  <div class="global">
    <aside-header
      :author="author"
      :billboard="billboard"
      :follow-status="followStatus"
      :follow-loading="followLoading"
      :can-opera="canOpera"
      @follow="follow"
      @unFollow="unFollow"
    />
    <div
      v-show="threeEssenceThread.length > 0"
      v-loading="loading"
      class="recommend block"
    >
      <div class="title">{{ $t("topic.recommend") }}</div>
      /*IFTRUE_default*/
      <div
        v-for="(item, index) in threeEssenceThread"
        :key="index"
        class="container-post"
      >
        <div
          v-if="item.title && item.firstPost.summaryText"
          class="content-html"
          @click="goToPage(item)"
        >
          {{ item.title || item.firstPost.summaryText }}
        </div>
        <div
          v-else
          class="content-html"
          @click="goToPage(item)"
          v-html="$xss(item.firstPost.summary)"
        />
        <span class="view-count">
          {{ item.viewCount }} {{ $t('topic.readAlready') }}
        </span>
      </div>
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      <div
        v-for="(item, index) in threeEssenceThread"
        :key="index"
        class="container-post"
        @click="goToPage(item)"
      >
        <!-- 小图标展示 -->
        <div class="content-item-tips">
          <img
            v-if="(item.type === 0 || item.type === 1) && item.isRedPacket"
            src="../../assets/pay_imgs/post_red.png"
            class="item-img"
          >
          <img
            v-if="
              item.type === 5 
                && item.questionTypeAndMoney 
                && item.questionTypeAndMoney.type === 0
            " 
            src="../../assets/pay_imgs/post_reward.png"
            class="item-img"
          >
          <img
            v-else-if="item.type === 5"
            src="../../assets/pay_imgs/post_question.png"
            class="item-img"
          >
          <img 
            v-if="item.type === 6"
            src="../../assets/pay_imgs/post_goods.png"
            class="item-img"
          >
          <img 
            v-if="item.type === 7"
            src="../../assets/pay_imgs/post_album.png"
            class="item-img"
          >
          <span
            v-if="
              item.type === 5
                && item.questionTypeAndMoney
                && item.questionTypeAndMoney.type === 0
            "
            class="item-tip"
          >
            【￥{{ item.questionTypeAndMoney.money }}】
          </span>
        </div>
        <!-- 内容 -->
        <p v-if="item.type === 1" class="content">
          {{ handleContent(item.type, item.title) }}
        </p>
        <p v-else class="content">
          {{ handleContent(item.type, item.postContent) }}
        </p>
        <span class="read-count">
          {{ item.viewCount }} {{ $t('topic.readAlready') }}
        </span>
      </div>
      /*FITRUE_pay*/
    </div>
    <advertising style="margin-top: 15px;margin-bottom:3px;" />
    <copyright />
  </div>
</template>

<script>
const include = 'groups,dialog';
import handleError from '@/mixin/handleError';

export default {
  name: 'TopicAside',
  mixins: [handleError],
  props: {
    author: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      threeEssenceThread: [],
      EssenceThread: [],
      followStatus: 0,
      followLoading: false,
      loading: true,
      billboard: [
        { key: 'threadCount', count: '', text: this.$t('home.thread') },
        { key: 'questionCount', count: '', text: this.$t('profile.question') },
        { key: 'likedCount', count: '', text: this.$t('topic.getLike') },
        { key: 'followCount', count: '', text: this.$t('home.followed') },
        { key: 'fansCount', count: '', text: this.$t('profile.followers') }
      ]
    };
  },
  computed: {
    forums() {
      return this.$store.state.site.info.attributes || {};
    },
    canOpera() {
      const userId = this.$store.getters['session/get']('userId');
      if (this.author.id && this.author.id > 0) {
        // 作者 = 当前用户 && 不是匿名用户
        return parseInt(userId) > 0 && userId !== this.author.id.toString();
      }
      return false;
    }
  },
  watch: {
    author: {
      handler(val) {
        if (Object.keys(val).length === 0) return;
        this.followStatus = val.follow;
        this.getAuthorInfo();
      },
      deep: true,
      immediate: true
    }
  },
  mounted() {
    this.getEssenceThread();
  },
  methods: {
    getAuthorInfo() {
      if (!this.author.id || (this.author.id && this.author.id === -1)) return;
      return this.$store
        .dispatch('jv/get', [
          `users/${this.author.id}`,
          { params: { include }}
        ])
        .then(
          res => {
            this.billboard.forEach(item => {
              item.count = res[item.key];
            });
          },
          e => this.handleError(e)
        );
    },
    /*IFTRUE_pay*/
    handleContent(type, content) {
      let con = content
        .replace(/:[a-z]+:/g, '')
        .replace(/[!！?？|\d]{6,}/g, ($1) => $1.substr(0, 6));
      if (type === 0) {
        con = con.slice(0, 18);
      } else if (type === 1) {
        con = con.slice(0, 20);
      } else if (type === 5) {
        con = con.slice(0, 10);
      }
      return con.length < 9 ? con : `${con}...`;
    },
    /*FITRUE_pay*/
    getEssenceThread() {
      this.loading = true;
      return this.$store
        .dispatch('jv/get', [
          `threads`,
          {
            params: {
              'filter[isApproved]': 1,
              'filter[isEssence]': 'yes',
              'filter[isDeleted]': 'no'
            }
          }
        ])
        .then(
          data => {
            this.EssenceThread = [...data];
            if (this.EssenceThread.length === 0) return;
            const limit
              = this.EssenceThread.length > 3 ? 3 : this.EssenceThread.length;
            while (this.threeEssenceThread.length < limit) {
              const index = this.getRandom(this.EssenceThread.length);
              this.threeEssenceThread.push(this.EssenceThread[index]);
              this.EssenceThread.splice(index, 1); // 在原数据中除掉已进入推荐的，避免重复
            }
          },
          e => this.handleError(e)
        )
        .finally(() => {
          this.loading = false;
        });
    },
    getRandom(length) {
      return Math.floor(Math.random() * length);
    },
    follow() {
      if (this.followLoading) return;
      this.followLoading = true;
      const params = {
        _jv: { type: `follow` },
        to_user_id: this.author.id.toString()
      };
      return this.$store.dispatch('jv/post', params).then(
        res => {
          this.followLoading = false;
          this.followStatus = res.is_mutual === 0 ? 1 : 2;
        },
        e => this.handleError(e)
      );
    },
    unFollow() {
      if (this.followLoading) return;
      this.followLoading = true;
      this.$store
        .dispatch('jv/delete', `follow/${this.author.id}/1`)
        .then(() => {
          this.followLoading = false;
          this.followStatus = 0;
        });
    },
    goToPage(item) {
      process.client && window.open(`/thread/${item._jv.id}`);
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_default*/
.global {
  width: 300px;
  margin-left: 15px;
  background: transparent;

  > .recommend {
    border-radius: 3px;
    margin-top: 15px;
    background: #fff;
    padding: 20px;
    min-height: 100px;
    box-shadow: 0 3px 3px rgba(0, 0, 0, 0.03);
    > .title {
      font-weight: bold;
      font-size: 16px;
      color: #6d6d6d;
      margin-bottom: 20px;
    }
    > .no-data {
      color: #6d6d6d;
    }
    > .container-post {
      padding-bottom: 20px;
      border-bottom: 1px solid $border-color-base;

      &:last-child {
        padding-bottom: 0;
        border-bottom: none;
      }

      > .content-html {
        word-break: break-all;
        margin-top: 10px;
        margin-bottom: 10px;
        font-size: 16px;
        cursor: pointer;
        &:hover {
          color: $color-blue-base;
        }
      }

      > .view-count {
        line-height: 12px;
        display: block;
        margin-top: 10px;
        color: $font-color-grey;
        font-size: 12px;
      }
    }
  }
}
@media screen and (max-width: 1005px) {
  .global {
    width: 220px;
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
@import '@/assets/css/variable/color_pay.scss';
@import "@/assets/css/pay_scss/TopicAside.scss";
/*FITRUE_pay*/
</style>
