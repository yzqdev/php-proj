<template>
  <div class="global">
    <Header :key="this.$route.path" />
    <div class="app-container">
      <div class="app-cont">
        <el-menu :default-active="$route.path" :router="true">
          <template v-for="item in menuList">
            <template v-if="item.index === '/manage'">
              <el-menu-item
                v-if="
                  forums &&
                    forums.other &&
                    (forums.other.can_edit_user_group ||
                      forums.other.can_edit_user_status ||
                      forums.other.can_create_invite)
                "
                :key="item.index"
                :index="item.index"
                :class="item.classname"
              >
                <span slot="title" class="menu-title">{{
                  $t(item.content)
                }}</span>
                <span class="arrow"><i class="el-icon-arrow-right icon" /></span>
              </el-menu-item>
            </template>
            <template v-else-if="item.index === '/invite'">
              <el-menu-item
                v-if="
                  forums && forums.other && forums.other.can_invite_user_scale
                "
                :key="item.index"
                :index="item.index"
                :class="item.classname"
              >
                <span slot="title" class="menu-title">{{
                  $t(item.content)
                }}</span>
                <span class="arrow"><i class="el-icon-arrow-right icon" /></span>
              </el-menu-item>
            </template>
            <template v-else>
              <el-menu-item
                v-if="!item.hidden"
                :key="item.index"
                :index="item.index"
                :class="item.classname"
              >
                <span
                  slot="title"
                  class="menu-title"
                >{{ $t(item.content) }}
                  <span
                    v-if="
                      item.index === '/my/notice' &&
                        userInfo.unreadNotifications > 0
                    "
                    class="unread-notice"
                  >
                    {{
                      userInfo.unreadNotifications > 99
                        ? "99+"
                        : userInfo.unreadNotifications
                    }}
                  </span>
                </span>
                <span class="arrow"><i class="el-icon-arrow-right icon" /></span>
              </el-menu-item>
            </template>
          </template>
        </el-menu>
        <Nuxt class="my-main" />
      </div>
    </div>
    <Footer />
    <BackTop />
  </div>
</template>

<script>
export default {
  name: 'CenterLayout',
  data() {
    return {
      currentNumber: 0,
      menuList: [
        {
          /*IFTRUE_default*/
          index: '/my/profile',
          /*FITRUE_default*/
          /*IFTRUE_pay*/
          index: '/my/profilePay',
          /*FITRUE_pay*/
          classname: 'padd',
          content: 'profile.myprofile',
          hidden: false
        },
        {
          index: '/my/wallet',
          classname: 'padd',
          content: 'profile.mywallet',
          hidden: false
        },
        {
          index: '/my/favorite',
          classname: 'padd',
          content: 'profile.myfavorite',
          hidden: false
        },
        {
          index: '/my/shield',
          classname: 'padd',
          content: 'profile.myshield',
          hidden: false
        },
        {
          index: '/my/notice',
          classname: 'padd',
          content: 'profile.notice',
          hidden: false
        },
        {
          index: '/my/mygoods',
          classname: 'padd',
          content: 'profile.mygoods',
          hidden: false
        },
        {
          index: '/my/mydrafts',
          classname: 'padd mydrafts',
          content: 'profile.mydrafts',
          hidden: false
        },
        {
          index: '/my/vip',
          classname: 'padd vip',
          content: 'profile.vip',
          hidden: false
        },
        {
          index: '/site',
          classname: 'padd divided',
          content: 'manage.circleinfo',
          hidden: false
        },
        {
          index: '/manage',
          classname: 'padd',
          content: 'manage.siteManagement',
          hidden: false
        },
        {
          index: '/invite',
          classname: 'padd',
          content: 'invite.invite',
          hidden: false
        }
      ]
    };
  },
  computed: {
    forums() {
      const _forums = this.$store.state.site.info.attributes;
      return _forums;
    },
    userId() {
      return localStorage.getItem('user_id') || '';
    },
    userInfo() {
      return this.$store.state.user.info.attributes || {};
    }
  },
  mounted() {
    this.initMenu();
  },
  methods: {
    initMenu() {
      // 判断是否登录
      if (!this.userId) {
        this.$message.error(this.$t('core.not_authenticated'));
        const _this = this;
        window.setTimeout(() => {
          _this.$router.push('/');
        }, 1000);
      }
    }
  },
  head() {
    return {
      link: [
        {
          rel: 'icon',
          type: 'image/x-icon',
          href:
            this.forums
            && this.forums.set_site
            && this.forums.set_site.site_favicon
        }
      ]
    };
  }
};
</script>
<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_default*/
.app-container {
  width: 1005px;
  background: #f4f5f6;
  margin: 15px auto 0;
  @media screen and (max-width: 1005px) {
    width: 100%;
    min-width: 768px;
    padding: 0 14px;
  }
  .app-cont {
    width: 100%;
    min-height: calc(100vh - 65px - 80px - 15px);
    display: flex;
    background: rgba(255, 255, 255, 1);
    box-shadow: 0 3px 3px rgba(0, 0, 0, 0.03);
    border-radius: 5px;
    .el-menu {
      padding-top: 25px;
      border-radius: 5px 0;
      width: 150px;
      border-right: 1px solid $line-color-base;
    }
    .padd {
      padding-left: 30px !important;
      padding: 0 65px;
      color: #6d6d6d;
      position: relative;
      @media screen and (max-width: 1005px) {
        padding: 0 40px;
      }
      &:hover {
        color: #00479b;
      }
    }
    .divided {
      border-top: 1px solid $line-color-base;
      margin-top: 15px;
      padding-top: 32px !important;
    }
    .arrow {
      position: absolute;
      // top:50%;
      right: 20px;
      // transform: translateY(-50%);
      color: #6d6d6d;
      display: none;
      @media screen and (max-width: 1005px) {
        right: 10px;
      }
      .icon {
        font-size: 13px;
        margin-right: -9px;
      }
    }
    .is-active .arrow {
      display: block;
      // margin-top: 2.5px;
    }
    ::v-deep.el-menu-item {
      height: auto;
      line-height: 1;
      padding-top: 17px;
      padding-bottom: 17px;
    }
    ::v-deep.el-menu-item.is-active {
      color: black;
      font-weight: bold;
      background: white;
    }
    ::v-deep.el-menu-item:focus,
    .el-menu-item:hover {
      background: white;
    }
    .menu-title {
      display: flex;
      align-items: center;
      font-size: 16px;
      .unread-notice {
        font-size: 12px;
        color: #fff;
        background: #ff0000;
        padding: 1px 6px;
        margin-left: 2px;
        border-radius: 6px;
        font-weight: normal;
      }
    }
    .my-main {
      margin-top: 33px;
      width: 100%;
      min-height: 800px;
      flex: 1;
      overflow-x: auto;
      @media screen and (max-width: 1005px) {
        margin-top: 20px;
      }
    }
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
@import "@/assets/css/pay_scss/center_layout.scss";
/*FITRUE_pay*/
</style>
