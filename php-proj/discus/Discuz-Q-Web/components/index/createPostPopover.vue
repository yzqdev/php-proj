<template>
  <div>
    <el-popover
      v-model="visible"
      placement="bottom"
      width="120"
      min-width="120"
      trigger="manual"
      popper-class="custom-popover-width"
    >
      <ul class="type-container">
        <li v-if="can_create_thread" @click.stop="toRouter(0)">
          {{ $t("home.word") }}
        </li>
        <li v-if="can_create_thread_long" @click.stop="toRouter(1)">
          {{ $t("home.invitation") }}
        </li>
        <li v-if="can_create_thread_image" @click.stop="toRouter(3)">
          {{ $t("home.picture") }}
        </li>
        <li v-if="can_create_thread_video" @click.stop="toRouter(2)">
          {{ $t("home.video") }}
        </li>
        <li v-if="can_create_thread_question" @click.stop="toRouter(5)">
          {{ $t("home.question") }}
        </li>
        <li v-if="can_create_thread_goods" @click.stop="toRouter(6)">
          {{ $t("home.product") }}
        </li>
        <li v-if="can_create_thread_ask" @click.stop="toRouter(14)">
          {{ $t("home.ask") }}
        </li>
      </ul>
      <template slot="reference">
        <slot name="button">
          <el-button type="primary" class="new-post" @click="showAndHidePopover">
            /*IFTRUE_default*/
            <span class="add-icon">+</span>{{ $t("profile.post") }}
            /*FITRUE_default*/
            /*IFTRUE_pay*/
            {{ $t("profile.post") }}<span class="add-icon">+</span>
            /*FITRUE_pay*/
          </el-button>
        </slot>
      </template>
    </el-popover>
  </div>
</template>
<script>
import loginAbout from '@/mixin/loginAbout';

export default {
  name: 'CreatePostPopover',
  mixins: [loginAbout],
  data() {
    return {
      userId: this.$store.getters['session/get']('userId'), // 获取当前登陆用户的ID
      visible: false,
      noCreateThread: false, // 是否有创建主题的权限
      can_create_thread: true, // 是否有发文字贴权限
      can_create_thread_long: true, // 是否有发帖子权限
      can_create_thread_video: true, // 是否有发视频贴权限
      can_create_thread_image: true, // 是否有发图片贴权限
      can_create_thread_question: true, // 是否有发问答贴权限
      can_create_thread_goods: true,// 是否有发商品贴权限
      can_create_thread_ask: true // 是否有发商品贴权限
    };
  },
  computed: {
    userInfo() {
      return this.$store.getters['jv/get'](`/users/${this.userId}`);
    },
    forums() {
      return this.$store.state.site.info.attributes || {};
    },
    categoryId() {
      return this.$route.query.categoryId;
    },
    types() {
      return this.$route.query.url;
    },
    www() {
      return this.$route.path;
    }
  },
  methods: {
    // 显示and隐藏下拉菜单
    showAndHidePopover() {
      if (!this.$store.getters['session/get']('isLogin')) {
        if (process.client) {
          this.$message.warning('请登录');
          window.setTimeout(() => {
            this.headerTologin();
          }, 1000);
        }
      } else {
        if (!this.visible) {
          const _other = this.forums.other;
          const _userInfo = this.userInfo;
          if (!_other) return;
          // 判断是否有发帖权限
          if (
            !_other.can_create_thread
            && !_other.can_create_thread_long
            && !_other.can_create_thread_video
            && !_other.can_create_thread_image
            && !_other.can_create_thread_question
            && !_other.can_create_thread_goods
            && !_other.can_create_thread_ask
          ) {
            this.$message.error(this.$t('home.noPostingPermission'));
            this.$router.push(
              `/my/vip`
            );
            return;
          }
          // 发布是否需要实名认证
          if (_other.publish_need_real_name && !_userInfo.realname) {
            this.$message.error(this.$t('home.needRealname'));
            return;
          }
          // 发布是否需要手机号
          if (_other.publish_need_bind_phone && !_userInfo.mobile) {
            this.$message.error(this.$t('home.needPhone'));
            return;
          }
          // 判断至少在某个分类下有发帖权限
          if (!_other.can_create_thread_in_category) {
            this.$message.error(this.$t('home.noPostingCategory'));
            return;
          }
          // 当前分类是否有发帖权限
          if (this.categoryId) {
            const category = this.$store.getters['jv/get'](
              `categories/${this.categoryId}`
            );
            if (!category.canCreateThread) {
              this.$message.error(this.$t('home.noPostingCategory'));
            }
          }
          // 判断发帖子权限
          if (!_other.can_create_thread) {
            this.can_create_thread = false;
          }
          // 判断发文章权限
          if (!_other.can_create_thread_long) {
            this.can_create_thread_long = false;
          }
          // 判断发视频帖权限
          if (!_other.can_create_thread_video) {
            this.can_create_thread_video = false;
          }
          // 判断发图片权限
          if (!_other.can_create_thread_image) {
            this.can_create_thread_image = false;
          }
          // 判断发问答贴权限
          if (!_other.can_create_thread_question) {
            this.can_create_thread_question = false;
          }
          // 判断发商品贴权限
          if (!_other.can_create_thread_goods) {
            this.can_create_thread_goods = false;
          }
          // 判断提问贴权限
          if (!_other.can_create_thread_ask) {
            this.can_create_thread_ask = false;
          }
        }
        this.visible = !this.visible;
      }
    },
    // 跳往发帖页
    toRouter(val) {
      this.$router.push(
        // `/thread/post?type=${val}${
        //   this.categoryId ? `&categoryId=${this.categoryId}` : ''
        // }`
        `/thread/postpay?type=${val}${
          this.categoryId ? `&categoryId=${this.categoryId}` : ''
        }`
      );
    }
  }
};
</script>
<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_pay*/
@import '@/assets/css/variable/color_pay.scss';
/*FITRUE_pay*/
// 重置element
/*IFTRUE_default*/
.new-post {
  background-color: $color-blue-base !important;
  border-color: $color-blue-base !important;
  padding: 9.5px 13.5px !important;
  border-radius: 2px;
  &:hover {
    background-color: $color-blue-deep !important;
    border-color: $color-blue-deep !important;
  }
  .add-icon {
    display: inline-block;
    margin-right: 5px;
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
.new-post {
  background-color: $color-blue-base !important;
  border-color: $color-blue-base !important;
  padding: 15px 24px !important;
  font-size: 16px !important;
  border-radius: 23px !important;
  opacity: .9;
  @media screen and (max-width: 1005px){
    padding: 10px 18px !important;
    font-size: 14px !important;
  }
  &:hover {
    opacity: 1;
    background-color: $color-blue-base !important;
    border-color: $color-blue-base !important;
  }
  .add-icon {
    display: inline-block;
    margin-left: 7px;
  }
}
/*FITRUE_pay*/
.type-container {
  padding: 5px 0;
  li {
    position: relative;
    list-style: none;
    width: 120px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    cursor: pointer;
    outline: none;
    &:hover {
      color: $color-blue-base;
    }
    &:before {
      content: "";
      position: absolute;
      left: 0;
      bottom: 0;
      width: 100px;
      height: 1px;
      background: #ededed;
      margin: 0 10px;
    }
    &:last-child:before {
      height: 0;
    }
  }
}
</style>
