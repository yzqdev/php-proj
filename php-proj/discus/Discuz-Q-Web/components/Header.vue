<template>
  <div class="isFixed">
    <div v-if="!hideHeader" ref="head" class="header">
      <div class="container">
        <div class="cont-left">
          <div class="logo" @click="toIndex">
            /*IFTRUE_pay*/
            <img
              v-show="forums && forums.set_site"
              :src="forums
                && forums.set_site
                && forums.set_site.site_logo
                ? forums.set_site.site_logo : require('@/assets/pay_imgs/logo_pay.png')"
              alt="头部logo"
            >
            /*FITRUE_pay*/
          </div>
        </div>
        <div class="cont-center">

          <el-input
            v-model="inputVal"
            autocomplete="off"
            size="medium"
            :placeholder="$t('search.search')"
            class="h-search"
            :disabled="siteClose"
            @keyup.enter.native="onClickSearch"
          >
            <svg-icon slot="suffix" type="search" class="el-input__icon" @click="onClickSearch" />
          </el-input>
          <!--div class="kuq-baifei"-->
          <div class="index-filter">
            <nuxt-link to="/"
              class="kuq-10 kuq-juzhong kuq-font-16 kuq-jiacu kuq-henggao-44 kuq-you">
              <div>首页</div>
            </nuxt-link>
            <a v-for="(item, index) in categoryData"
               :key="index"
              :href="item.url"
              class="filter-btn kuq-jiacu"
              :class="{'active': www === item.url || item.url === '/'+wwwurl}">
              {{item.name}}
            </a>
          </div>
        </div>
        <div class="cont-right">

          <!-- 未登录 -->
          <div v-if="siteClose || !islogin ">
            <el-button
              v-if="siteClose || !userId"
              size="small"
              class="h-button h-button1"
              @click="login"
            >
              {{ $t('user.login') }}
            </el-button>
            <el-button
              v-if="siteClose || !userId"
              :disabled="(forums && forums.set_reg && !forums.set_reg.register_close) || siteClose"
              size="small"
              class="h-button h-button2"
              @click="register"
            >
              {{ $t('user.register') }}
            </el-button>
          </div>
          <!-- 已登录 -->
          /*IFTRUE_pay*/
          <div v-if="userId && JSON.stringify(userInfo) !== '{}' && !siteClose" class="flex">
            <avatar
              :user="{ id: userInfo.id,
                       username: userInfo.username,
                       avatarUrl: userInfo.avatarUrl,
                       isReal: userInfo.isReal
              }"
              :size="45"
              :round="true"
            />
            <!--nuxt-link
              v-if="userInfo.username && userInfo.id"
              :to="`/user/${userInfo.id}`"
              class="menu-item user-name text-hidden"
            >
              {{ userInfo.username }}
            </nuxt-link-->
            <nuxt-link to="/my/notice" class="menu-item">
              <svg-icon type="voice" class="menu-icon menu-icon-voice" />
              <!--{{ $t('home.tabsNews') }}-->
              <span
                v-if="userInfo.unreadNotifications && userInfo.unreadNotifications > 0"
                class="unread-notice"
              >{{ userInfo.unreadNotifications > 99 ? '99+' : userInfo.unreadNotifications }}</span>
            </nuxt-link>
            <nuxt-link to="/my/profilePay" class="menu-item">
              <svg-icon type="user" class="menu-icon" />
              <!--{{ $t('profile.personalhomepage') }}-->
            </nuxt-link>
            <div class="menu-item" @click="logout">
              <svg-icon type="leave" class="menu-icon" />
              <!--{{ $t('user.logout') }}-->
            </div>
          </div>
          /*FITRUE_pay*/
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import loginAbout from '@/mixin/loginAbout';
import apiRequest from '../api/new-api';
export default {
  name: 'Header',
  mixins: [loginAbout],
  props: {
    headImg: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      categoryData: [], // 分类列表
      inputVal: '',
      code: '', // 邀请码
      canReg: false,
      siteClose: false,
      userInfoTimer: null, // 定时器
      offsetTop: 0,
      imgurl: require('@/assets/logo.png'),
      islogin: true,
      hideHeader: false, // 不需要头部右侧部分
      preurl: '/'
    };
  },
  computed: {
    userId() {
      return this.$store.state.user.info.id;
    },
    forums() {
      return this.$store.state.site.info.attributes || {};
    },
    userInfo() {
      return this.$store.state.user.info.attributes || {};
    },
    www() {
      return this.$route.path;
    },
    wwwurl() {
      return this.$route.query.url;
    }
  },
  watch: {
    // 监听路由变化
    $route(to) {
      if (to.path === '/site/close') {
        this.siteClose = true;
      } else {
        this.siteClose = false;
      }
    }
  },
  mounted() {
    if (this.categoryData.length === 0) {
      this.getCategory();
    }
    const { code, preurl } = this.$route.query;
    if (preurl) {
      this.preurl = preurl;
    }
    if (code !== 'undefined') {
      this.code = code;
    }
    if (process.client && this.$route.query.q) {
      this.inputVal = this.$route.query.q;
    }
    if (process.client && this.$route.path !== '/site/close') {
      window.setTimeout(this.reloadUserInfo(), 5000);
    }
    if (this.$route.path === '/site/close') {
      this.siteClose = true;
    }
    if (this.$route.path === '/user/warning' && this.$store.getters['session/get']('isLogin')) {
      // 用户审核页面如果有登录态先清除,从注册拓展过来的会有登录态，避免审核中点击回到首页大量报错审核中情况
      this.$store.dispatch('session/logout');
    }
    this.$nextTick(() => {
      const id = localStorage.getItem('user_id');
      this.islogin = id;
    });
    const items = ['/user/supple-mentary'];
    if (items.indexOf(this.$route.path) !== -1) {
      this.hideHeader = true;
    }
  },
  destroyed() {
    if (process.client) {
      window.clearInterval(this.userInfoTimer);
    }
  },
  methods: {
    getCategory() {
      this.categoryData = [];
      const params = {
        'type': 2
      };
      apiRequest.readCategories({ params }).then((res) => {
        this.categoryData = [...res.Data];
      });
    },
    // 获取分类列表
    /*getCategory() {
      apiRequest.readCategories().then((res) => {
        const resData = [...res.Data] || [];
        this.handleData(resData);
      }, (e) => {
        this.handleError(e);
      });
    },*/
    // 退出
    logout() {
      this.$store
        .dispatch('session/logout')
        .then(() => {
          // reload ，重写 href 不 100% 生效，锚点情况下只会锚点跳转
          location.reload();
        });
    },
    // 轮询获取用户信息，用于判断是否有新消息
    reloadUserInfo() {
      if (this.userInfo && this.userInfo.id) {
        window.clearInterval(this.userInfoTimer);
        this.userInfoTimer = window.setInterval(this.getUserInfo, 60000);
      } else {
        // 解决开发环境中，路由守卫获取不到用户信息的问题 router.js
        const user_id = localStorage.getItem('user_id');
        if (user_id) {
          this.getUserInfo(user_id);
        }
      }
    },
    // 从store里面获取用户信息
    async getUserInfo(user_id = '') {
      try {
        await this.$store.dispatch('user/getUserInfo', this.userId || user_id);
      } catch (err) {
        console.log('header getUserInfo err', err);
      }
    },
    // 跳往注册页面
    register() {
      this.toregister(this.code);
    },
    // 跳转登录页面
    login() {
      this.headerTologin();
    },
    // 跳转搜索页面
    onClickSearch() {
      if (this.inputVal) {
        this.$router.push(`/site/search?q=${this.inputVal}`);
      }
    },
    // 跳转首页
    toIndex() {
      if (this.$route.path === '/') {
        window.location.href = '/';
      } else {
        this.$route.path !== '/site/info' && this.$router.push('/');
      }
    }
  }
};
</script>
<style lang="scss" scoped>
@import '@/assets/css/variable/color_pay.scss';
@import "@/assets/css/pay_scss/kuq.scss";
@import '@/assets/css/variable/color.scss';
@mixin background() {
  background: #fff;
  border-radius: 5px;
  box-shadow: 0 3px 3px rgba(0, 0, 0, 0.03);
}
/*IFTRUE_default*/
.isFixed{
  position: -webkit-sticky;
  position: sticky;
  top: 0;
  width: 100%;
  z-index: 12;
}
.header {
  height: 100px;
  background: rgba(255, 255, 255, 1);
  box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.03);
  opacity: 1;
  @media screen and (max-width: 1005px) {
    height: 50px;
    width: 100%;
    min-width: 768px;
  }
  .header-container {
    margin: 0 auto;
    width: 1005px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    .flex {
      display: flex;
      align-items: center;
    }
    @media screen and (max-width: 1005px) {
      width: 100%;
      min-width: 768px;
      padding: 0 14px;
      height: 50px;
    }
    .logo {
      max-width: 280px;
      max-height: 35px;
      height: 24px;
      cursor: pointer;
      display: flex;
      @media screen and (max-width: 1005px) {
        max-width: 135px;
        max-height: 30px;
      }
      img {
        height: 24px;
        // width: 100%;
      }
    }
    .logo2 {
      background-position: center center;
      background-size: contain;
      background-repeat: no-repeat;
      height: 24px;
      width: 100%;
      max-width: 100%;
      cursor: pointer;
    }
    ::v-deep.h-search {
      margin-left: 42px;
      width: 298px;
      height: 36px;
      @media screen and (max-width: 1005px) {
        width: 180px;
        height: 30px;
      }
      .el-input__inner {
        border-radius: 2px;
        color: #000;
        border-color: rgba(237,237,237,0.66);
        @media screen and (max-width: 1005px) {
          height: 30px;
          line-height: 30px;
        }
      }
      .el-input__icon {
        width:14px;
        margin-right: 8px;
        cursor: pointer;
      }
      .el-input__suffix{
        line-height: 36px;
        @media screen and (max-width: 1005px) {
          line-height: 30px;
        }
      }
    }

    .h-button {
      width: 60px;
      height: 35px;
      border-color: $color-blue-base;
      font-size: 14px;
      border-radius: 2px;
      &.h-button1 {
        background: #ffffff;
        color: $color-blue-base;
        transition: all 0.2s ease-in-out;
        &:hover{
          background: #E5F2FF;
          border:1px solid #D4E6FC;
        }
      }
      &.h-button2 {
        color: #fff;
        background: $color-blue-base;
        color: #fff;
        &:hover{
          background:  $color-blue-deep;
          border-color: $color-blue-deep;
        }
      }
    }
    .menu-item {
      color: #6d6d6d;
      font-size: 16px;
      margin-left: 25px;
      cursor: pointer;
      @media screen and (max-width: 1005px) {
        font-size: 14px;
        margin-left: 15px;
      }
      &:hover{
        color: $color-blue-base;
      }
    }
    .user-name {
      margin-left: 10px;
      max-width: 120px;
    }
    .notice-btn {
      margin-left: 30px;
      .flex {
        margin-right: -1px;
        display: flex;
        align-items: center;
      }
      .unread-notice {
        font-size: 12px;
        color: #fff;
        background: #ff0000;
        padding: 1px 7px;
        border-radius: 6px;
        margin-left: 2px;
        line-height: 12px;
      }
    }
    .profile {
      margin-left: 32px;
    }
    .logout {
      margin-left: 30px;
    }
    .avatar {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      vertical-align: bottom;
      display: inline-block !important;
    }
  }
}

.search-logo {
  width: 14px;
  height: 14px;
  display: inline-block;
  vertical-align: middle;
}
/*FITRUE_default*/
/*IFTRUE_pay*/
.index-filter{
  display: flex;
  align-items: center;
  line-height: 1;
  .filter-btn{
    position: relative;
    padding:10px 2px;
    margin-right: 15px;
    font-size: 16px;
    color:$color-333;
    line-height: 1;
    cursor: pointer;
    &.active{
      color: $color-blue-base;
      transition: all 0.2s ease-in-out;
      font-weight: bold;
      &::after{
        content: '';
        position: absolute;
        left: 50%;
        bottom: -4px;
        margin-left: -16px;
        width: 32px;
        height: 4px;
        background-color: $color-blue-base;
        border-radius: 2px;
      }
    }
    &:hover{
      color: $color-blue-base;
      background: $color-background-hover;
    }
    @media screen and ( max-width: 1005px ) {
      padding: 8px 5px;
      margin-right: 0;
      font-size: 14px;
    }
  }
  .filter-dropdown{
    margin: 0 0 0 10px;
    padding:8px 14px;
    font-size: 16px;
    color: $color-333;
    cursor: default;
    @media screen and ( max-width: 1005px ) {
      margin: 0;
      padding:8px 5px;
      font-size: 14px;
    }
    &:hover{
      background: $color-background-hover;
    }
  }
}
@import "@/assets/css/variable/color_pay.scss";
@import "@/assets/css/pay_scss/Header.scss";
/*FITRUE_pay*/
</style>
