<template>
  <div class="container">
    <svg-icon type="lock" style="font-size: 100px" />
    <h1 class="tip">{{ $t('core.permission_denied3') }}</h1>
    <span class="detail">{{ $t('core.permission_denied4') }}</span>
    <button v-if="!isLogin" @click="login">
      {{ $t('core.back_login') }}
    </button>

    <NuxtLink v-if="isLogin" to="/">
      <button>
        {{ $t('core.back_home') }}
      </button>
    </NuxtLink>
  </div>
</template>

<script>
export default {
  layout: 'error_layout',

  computed: {
    isLogin() {
      return this.$store.getters['session/get']('isLogin');
    },
    forums() {
      return this.$store.state.site.info.attributes || {};
    }
  },

  methods: {
    // 跳转登录页面
    login() {
      if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 0) {
        // 用户名模式
        this.$router.push(`/user/login`);
      } else if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 1) {
        // 手机模式
        this.$router.push(`/user/phone-login-register`);
      } else if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 2) {
        //  微信模式
        if (this.forums && this.forums.passport
          && this.forums.passport.oplatform_close
          && this.forums.passport.offiaccount_close) {
          this.$router.push(`/user/wechat`);
        } else if (this.forums && this.forums.qcloud && this.forums.qcloud.qcloud_sms) {
          this.$router.push(`/user/phone-login-register`);
        } else {
          this.$router.push(`/user/login`);
        }
      } else {
        this.$router.push(`/user/login`);
      }
    }
  }
};
</script>

<style lang="scss" scoped>
  @import '@/assets/css/variable/color.scss';
  /*IFTRUE_pay*/
  @import "@/assets/css/variable/color_pay.scss";
  /*FITRUE_pay*/

  .container {
    text-align: center;

    h1 {
      margin-top: 10px;
    }
    > .detail {
      widows: 320px;
      color: #AAAAAA;
      line-height: 20px;
      font-size: 16px;
      display: block;
      margin-top: 20px;
    }
    button {
      margin-top: 38px;
      width: 250px;
      height: 45px;
      line-height: 45px;
      text-align: center;
      border: 1px solid $color-blue-base;
      color: $color-blue-base;
      border-radius: 3px;
    }
  }
</style>
