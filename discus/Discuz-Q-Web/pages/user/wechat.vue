<template>
  <div v-if="forums" class="register">
    /*IFTRUE_default*/
    <el-tabs v-model="activeName" type="border-card" class="register-select">
      <!-- 快捷登录 -->
      <el-tab-pane
        v-if="forums && forums.passport && forums.passport.oplatform_close"
        :label="
          forums && forums.set_reg && forums.set_reg.register_type !== 2
            ? $t('user.quicklogin')
            : $t('user.quicklogin') + '/注册'
        "
        name="0"
      >
        <div class="quick">
          <div class="quick-container">
            <div class="qrcode">
              <img :src="wechatLogin.base64_img">
            </div>
            <div class="quick-title">
              <img src="@/assets/wechat.png">
              <span>
                {{
                  forums && forums.set_reg && forums.set_reg.register_type !== 2
                    ? $t("user.wechatlogin")
                    : $t("user.wechatlogin") + "/注册"
                }}
              </span>
            </div>
            <div class="agreement">
              <reg-agreement
                v-if="
                  forums && forums.set_reg && forums.set_reg.register_type === 2
                "
                :check="false"
                @check="check"
              />
            </div>
            <div class="otherlogin">
              <svg-icon
                v-if="
                  forums &&
                    forums.qcloud &&
                    forums.qcloud.qcloud_sms &&
                    forums.set_reg &&
                    forums.set_reg.register_type !== 2
                "
                class="phone-icon"
                type="phonelogin"
                @click="toPhonelogin"
              />
              <svg-icon
                class="wechat-icon"
                type="userlogin"
                @click="toUserlogin"
              />
            </div>
          </div>
        </div>
      </el-tab-pane>
    </el-tabs>
    /*FITRUE_default*/
    /*IFTRUE_pay*/
    <div v-if="forums && forums.passport && forums.passport.oplatform_close">
      <div class="register-header">{{
        forums && forums.set_reg && forums.set_reg.register_type !== 2
          ? $t('user.quicklogin')
          : $t('user.quicklogin')+'/注册'
      }}
      </div>
      <!-- 快捷登录 -->
      <div class="register-content">
        <div class="quick-container">
          <div class="qrcode">
            <img :src="wechatLogin.base64_img">
          </div>
          <div class="quick-title">
            <img src="@/assets/wechat.png">
            <span>{{
              forums && forums.set_reg && forums.set_reg.register_type !== 2
                ? $t('user.wechatlogin')
                : $t('user.wechatlogin')+'/注册'
            }}
            </span>
          </div>
        </div>
        <div class="agreement">
          <reg-agreement
            v-if="forums && forums.set_reg && forums.set_reg.register_type === 2"
            :check="false"
            @check="check"
          />
        </div>
        <!-- 其它登录方式 -->
        <div class="otherlogin">
          <div class="otherlogin-title" />
          <svg-icon
            v-if="
              forums
                && forums.qcloud
                && forums.qcloud.qcloud_sms
                && forums.set_reg
                && forums.set_reg.register_type !== 2
            "
            class="phone-icon"
            type="phonelogin"
            @click="toPhonelogin"
          />
          <svg-icon class="wechat-icon" type="userlogin" @click="toUserlogin" />
        </div>
      </div>
    </div>
    /*FITRUE_pay*/
  </div>
</template>

<script>
import head from '@/mixin/head';
import handleError from '@/mixin/handleError';
import loginAbout from '@/mixin/loginAbout';
export default {
  name: 'Wechat',
  mixins: [head, handleError, loginAbout],
  data() {
    return {
      title: this.$t('user.quicklogin'),
      code: '', // 注册邀请码
      wechatLogin: {}, // 微信扫码登录信息
      wechatLoginTimer: null, // 微信登录定时器
      activeName: '0',
      ischeck: true,
      preurl: '/'
    };
  },
  computed: {
    forums() {
      return this.$store.state.site.info.attributes || {};
    }
  },
  mounted() {
    const { code, preurl } = this.$route.query;
    if (preurl) {
      this.preurl = preurl;
    }
    if (code !== 'undefined') {
      this.code = code;
    }
    if (this.forums && this.forums.set_site && this.forums.set_site.site_mode) {
      this.site_mode = this.forums.set_site.site_mode;
    }
    // 微信登录初始化
    this.createQRcode();
  },
  destroyed() {
    window.clearInterval(this.wechatLoginTimer);
  },
  methods: {
    check(value) {
      this.ischeck = value;
    },
    // PC扫码登陆-生成二维码
    createQRcode() {
      this.$store.dispatch('jv/get', '/oauth/wechat/pc/qrcode').then(
        res => {
          if (res) {
            this.wechatLogin = res;
            const _this = this;
            this.wechatLoginTimer = setInterval(_this.getLoginStatus, 3000);
          }
        },
        e => this.handleError(e)
      );
    },
    // 轮询查询微信是否登录成功
    getLoginStatus() {
      if (this.wechatLogin && !this.wechatLogin.session_token) return;
      this.$store
        .dispatch(
          'jv/get',
          `/oauth/wechat/pc/login/${this.wechatLogin.session_token}`
        )
        .then(
          res => {
            if (res) {
              const loginRes = res;
              // 用于新用户填拓展信息传用户id,和其他几种模式格式统一
              loginRes.data = {
                data: {
                  id: res._jv.id,
                  attributes: {
                    new_user: !!loginRes.new_user
                  }
                }
              };
              clearInterval(this.wechatLoginTimer);
              this.$store.commit('session/SET_USER_ID', res._jv.id);
              this.$store.commit('session/CHECK_SESSION', true);
              this.$store.commit('session/SET_ACCESS_TOKEN', res.access_token);
              this.logind(loginRes);
            }
          },
          e => {
            const {
              response: {
                data: { errors }
              }
            } = e;
            if (Array.isArray(errors) && errors.length > 0) {
              const error
                = errors[0].detail && errors[0].detail.length > 0
                  ? errors[0].detail[0]
                  : errors[0].code;
              const errorText
                = errors[0].detail && errors[0].detail.length > 0
                  ? errors[0].detail[0]
                  : this.$t(`core.${error}`);
              if (error === 'pc_qrcode_scanning_code') {
                return;
              } else if (error === 'no_bind_user') {
                const nickname = errors[0].user.nickname;
                const headimgurl = errors[0].user.headimgurl;
                const token = errors[0].token;
                if (process.client) localStorage.setItem('wechat', token);
                if (
                  this.forums
                  && this.forums.set_reg
                  && this.forums.set_reg.register_type === 0
                ) {
                  this.$router.push(
                    `/user/register-bind?nickname=${nickname}&headimgurl=${headimgurl}`
                  );
                }
                if (
                  this.forums
                  && this.forums.set_reg
                  && this.forums.set_reg.register_type === 1
                ) {
                  this.$router.push(
                    `/user/wechat-bind-phone?nickname=${nickname}&headimgurl=${headimgurl}`
                  );
                }
                // if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 2) {
                //   this.$message.success(this.$t('user.loginSuccess'))
                //   this.logind()
                // }
              } else {
                clearInterval(this.wechatLoginTimer);
                this.$message.error(errorText);
                this.createQRcode();
              }
            }
          }
        );
    },
    toUserlogin() {
      this.$router.push(`/user/login?code=${this.code}&preurl=${this.preurl}`);
    },
    toPhonelogin() {
      if (
        this.forums
        && this.forums.set_reg
        && this.forums.set_reg.register_type === 1
      ) {
        this.$router.push(
          `/user/phone-login-register?code=${this.code}&preurl=${this.preurl}`
        );
      } else {
        this.$router.push(
          `/user/phone-login?code=${this.code}&preurl=${this.preurl}`
        );
      }
    }
  }
};
</script>
<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
::v-deep input::-ms-reveal {
  display: none;
}
::v-deep.disable {
  pointer-events: none;
}
/*IFTRUE_default*/
.register {
  display: flex;
  width: 415px;
  flex-direction: column;
  .register-title {
    height: 35px;
    font-size: 26px;
  }
  .register-select {
    display: flex;
    flex-direction: column;
    margin-top: 62px;
    border: none;
    background: transparent;
    box-shadow: none;
    .reg-input {
      width: 299px;
      margin-bottom: 20px;
    }
    .quick {
      display: flex;
      flex-direction: row;
      align-content: space-between;
      .quick-container {
        flex: 1;
        text-align: center;
        .agreement {
          margin-top: 5px;
        }
        .qrtext {
          margin-top: 5px;
        }
        span {
          display: inline-block;
        }
      }
      .quick-title {
        font-size: 14px;
        span {
          line-height: 30px;
        }
        img {
          width: 26px;
          height: 26px;
          margin-right: 7px;
          vertical-align: middle;
        }
      }
      .qrcode {
        margin: 0 auto;
        width: 150px;
        height: 150px;
        margin-top: 53px;
        img {
          width: 100%;
        }
      }
      .qrcode2 {
        margin: 0 auto;
        width: 155px;
        height: 155px;
        border: 1px solid #ededed;
        margin-top: 26px;
        img {
          width: 40px;
          margin: 50px;
        }
      }
    }
  }
  .otherlogin {
    font-size: 50px;
    margin-top: 30px;
    .wechat-icon {
      cursor: pointer;
    }
    .phone-icon {
      cursor: pointer;
      margin-right: 5px;
    }
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
@import "@/assets/css/pay_scss/user/wechat.scss";
/*FITRUE_pay*/
::v-deep.el-tabs {
  .el-tabs__header {
    background: transparent;
    border-bottom: 1px solid #EFEFEF;
  }
  .el-tabs__header .el-tabs__item {
    border: none;
    color: #b5b5b5;
    padding: 0 45px 0 10px;
    font-size: 16px;
    transition: none;
  }
  .el-tabs__header .el-tabs__item:last-child {
    padding-right: 0px;
    padding-left: 0px;
  }
  .el-tabs__header .el-tabs__item.is-active {
    color: black;
    background: transparent;
    border: none;
    font-weight: bold;
    font-size: 26px;
    margin-bottom: 19px;
  }
  .el-tabs__header .el-tabs__item:hover {
    color: $color-blue-deep;
  }
  .el-tabs__nav-wrap {
    margin-bottom: 0px;
  }
  .el-input__inner {
    border-radius: 2px;
  }
  .el-button {
    border-radius: 2px;
  }
}
::v-deep .el-tabs--border-card > .el-tabs__content {
  margin-top: 7px;
}
</style>
