<template>
  <div v-if="forums" v-loading="loading" class="register">
    /*IFTRUE_default*/
    <el-tabs v-model="activeName" type="border-card" class="register-select">
      <!-- 用户名登录 login register phone-login   -->
      <el-tab-pane :label="$t('user.userlogin')" name="0">
        <form>
          <span class="title">{{ $t("user.usrname") }}</span>
          <el-input
            v-model="userName"
            :placeholder="$t('user.username')"
            class="reg-input"
          />
          <span class="title2">{{ $t("user.pwd") }}</span>
          <el-input
            v-model="passWord"
            :placeholder="$t('user.password')"
            type="password"
            class="reg-input"
            show-password
            @keyup.enter.native="UserLogin"
          />
          <div class="agreement">
            <el-checkbox v-model="checked" />
            <span class="agree">{{ $t("user.status") }} </span>
          </div>
          <el-button type="primary" class="r-button" @click="UserLogin">
            {{ $t("user.login") }}
          </el-button>
          <div class="logorreg">
            <span
              v-if="
                canReg &&
                  forums &&
                  forums.set_reg &&
                  forums.set_reg.register_type === 0
              "
            >
              {{ $t("user.noexist") }}
              <span class="agreement_text" @click="toRegister">
                {{ $t("user.register") }}
              </span>
            </span>
            <nuxt-link
              v-if="forums && forums.qcloud && forums.qcloud.qcloud_sms"
              to="/modify/findpwd"
              :class="['findpass', iscanReg()]"
            >
              {{ $t("modify.findpawdtitle") }}
            </nuxt-link>
          </div>
          <div class="otherlogin">
            <svg-icon
              v-if="
                forums &&
                  forums.passport &&
                  forums.passport.oplatform_close &&
                  forums.passport.offiaccount_close
              "
              class="wechat-icon"
              type="wechatlogin"
              @click="toWechat"
            />
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
              v-if="
                forums &&
                  forums.qcloud &&
                  forums.qcloud.qcloud_sms &&
                  forums.set_reg &&
                  forums.set_reg.register_type === 2 &&
                  forums.passport &&
                  !forums.passport.oplatform_close
              "
              class="phone-icon"
              type="phonelogin"
              @click="toPhonelogin"
            />
          </div>
        </form>
      </el-tab-pane>
    </el-tabs>
    /*FITRUE_default*/
    /*IFTRUE_pay*/
    <div class="register-header">{{ $t('user.userlogin') }}</div>
    <div class="register-content">
      <form>
        <div class="input-box">
          <span class="title">{{ $t('user.usrname') }}：</span>
          <el-input v-model="userName" />
        </div>
        <div class="input-box">
          <span class="title">{{ $t('user.pwd') }}：</span>
          <el-input
            v-model="passWord"
            type="password"
            show-password
            @keyup.enter.native="UserLogin"
          />
        </div>
        <el-button type="primary" class="r-button" @click="UserLogin">
          {{ $t('user.login') }}
        </el-button>
        <div class="desc">
          <div class="agreement">
            <el-checkbox v-model="checked" class="checkbox-btn" />
            <span class="agree">{{ $t('user.status') }} </span>
          </div>
          <div class="logorreg">
            <span
              v-if="
                canReg
                  && forums
                  && forums.set_reg
                  && (forums.set_reg.register_type === 0 )
              "
            >
              {{ $t('user.noexist') }}
              <span class="agreement_text" @click="toRegister">
                {{ $t('user.register') }}
              </span>
            </span>
            <nuxt-link
              v-if="forums && forums.qcloud && forums.qcloud.qcloud_sms"
              to="/modify/findpwd"
              :class="['findpass',iscanReg()]"
            >
              {{ $t('modify.findpawdtitle') }}
            </nuxt-link>
          </div>
        </div>
        <div class="otherlogin">
          <div
            v-if="
              (forums
                && forums.passport
                && forums.passport.oplatform_close
                && forums.passport.offiaccount_close)
                || (forums
                  && forums.qcloud
                  && forums.qcloud.qcloud_sms
                  && forums.set_reg
                  && forums.set_reg.register_type !== 2)
                || (forums
                  && forums.qcloud
                  && forums.qcloud.qcloud_sms
                  && forums.set_reg
                  && forums.set_reg.register_type === 2
                  && forums.passport
                  && !forums.passport.oplatform_close)
            "
            class="otherlogin-title"
          />
          <svg-icon
            v-if="
              forums
                && forums.passport
                && forums.passport.oplatform_close
                && forums.passport.offiaccount_close
            "
            class="wechat-icon"
            type="wechatlogin"
            @click="toWechat"
          />
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
          <svg-icon
            v-if="
              forums
                && forums.qcloud
                && forums.qcloud.qcloud_sms
                && forums.set_reg
                && forums.set_reg.register_type === 2
                && forums.passport
                && !forums.passport.oplatform_close
            "
            class="phone-icon"
            type="phonelogin"
            @click="toPhonelogin"
          />
        </div>
      </form>
    </div>
    /*FITRUE_pay*/
  </div>
</template>

<script>
import head from '@/mixin/head';
import handleError from '@/mixin/handleError';
import loginAbout from '@/mixin/loginAbout';

export default {
  name: 'Login',
  mixins: [head, handleError, loginAbout],
  data() {
    return {
      title: this.$t('user.login'),
      userName: '',
      passWord: '',
      checked: true,
      activeName: '0', // 默认激活tab
      site_mode: '', // 站点模式
      isPaid: false, // 是否付费
      code: '', // 注册邀请码
      loading: false,
      canReg: false,
      ischeck: true,
      preurl: '/'
    };
  },
  computed: {
    forums() {
      return this.$store.state.site.info.attributes || {};
    }
  },
  watch: {
    forums: {
      handler(val) {
        if (val.set_reg && val.set_reg.register_close) {
          this.canReg = true;
        }
      }
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
    if (
      this.forums
      && this.forums.set_reg
      && this.forums.set_reg.register_close
    ) {
      this.canReg = true;
    }
  },
  methods: {
    check(value) {
      this.ischeck = value;
    },
    // 用户名登录
    UserLogin() {
      this.loading = true;
      if (this.userName === '') {
        this.$message.error('用户名不能为空');
        this.loading = false;
      } else if (this.passWord === '') {
        this.$message.error('密码不能为空');
        this.loading = false;
      } else {
        const params = {
          data: {
            attributes: {
              username: this.userName,
              password: this.passWord
            }
          }
        };
        this.$store
          .dispatch('session/h5Login', params)
          .then(res => {
            this.loading = false;
            if (res && res.data && res.data.data && res.data.data.id) {
              this.logind(res);
              this.userName = '';
              this.passWord = '';
            }
            if (
              res
              && res.data
              && res.data.errors
              && res.data.errors[0].code === 'register_validate'
            ) {
              this.$store.commit('session/SET_AUDIT_INFO', {
                errorCode: 'register_validate',
                username: this.userName
              });
              this.$router.push('/user/warning');
            }
            if (res && res.data && res.data.errors && res.data.errors[0]) {
              const error = res.data.errors[0].detail
                ? res.data.errors[0].detail[0]
                : res.data.errors[0].code;
              const errorText = res.data.errors[0].detail
                ? res.data.errors[0].detail[0]
                : this.$t(`core.${error}`);
              this.$message.error(errorText);
            }
          })
          .catch(err => {
            this.loading = false;
            console.log(err);
          });
      }
    },
    toRegister() {
      this.$router.push(
        `/user/register?code=${this.code}&preurl=${this.preurl}`
      );
    },
    iscanReg() {
      return [
        this.canReg
        && this.forums
        && this.forums.set_reg
        && this.forums.set_reg.register_type === 0
          ? ''
          : 'noreg'
      ];
    },
    toWechat() {
      this.$router.push(`/user/wechat?code=${this.code}&preurl=${this.preurl}`);
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
    .title {
      width: 66px;
      text-align: center;
      display: inline-block;
      color: #606266;
    }
    .title2 {
      margin-right: 7px;
      margin-left:21px;
      color: #606266;
    }
    .title:first-child {
      margin-right: -4px;
      margin-left: 22px;
    }
    .reg-input {
      width: 299px;
      margin-bottom: 20px;
    }
  }
  .agreement {
    margin-left: 89px;
    font-size: 12px;
    .agree {
      color: #6d6d6d;
      font-size: 12px;
      margin-left: 6px;
    }
  }
  .logorreg {
    width: 300px;
    margin-top: 10px;
    margin-left: 89px;
    display: flex;
    // justify-content: space-between;
    .agreement_text {
      color: $color-blue-base;
      cursor: pointer;
      margin-left: -3px;
    }
    .findpass {
      margin-left: 105px;
      color: $color-blue-base;
    }
    .noreg {
      margin-left: 240px;
    }
  }
  .otherlogin {
    font-size: 50px;
    margin-top: 31px;
    margin-left: 89px;
    .wechat-icon {
      cursor: pointer;
    }
    .phone-icon {
      cursor: pointer;
      margin-left:3px;
    }
  }
  .r-button {
    width: 300px;
    margin-left: 88px;
    margin-top: 30px;
    background: $color-blue-base;
    transition: all 0.2s ease-out;
    &:hover {
      border: 1px solid $color-blue-deep;
      background: $color-blue-deep;
    }
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
@import "@/assets/css/pay_scss/user/login.scss";
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
    border: 1px solid #EFEFEF;
  }
  .el-button {
    border-radius: 2px;
  }
}
::v-deep .el-tabs--border-card > .el-tabs__content {
  margin-top: 7px;
  padding: 10px;
}
::v-deep .el-checkbox__input.is-checked .el-checkbox__inner,
.el-checkbox__input.is-indeterminate .el-checkbox__inner {
  background-color: $color-blue-base;
  border-color: $color-blue-base;
}
</style>
