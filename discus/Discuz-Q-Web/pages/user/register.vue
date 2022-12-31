<template>
  <div v-if="forums" v-loading="loading" class="register">
    /*IFTRUE_default*/
    <el-tabs v-model="activeName" type="border-card" class="register-select">
      <!-- 用户名注册 -->
      <el-tab-pane label="用户名注册" name="0">
        <form>
          <div>
            <span class="title">{{ $t("profile.username") }}</span>
            <el-input
              v-model="userName"
              :placeholder="$t('user.username')"
              class="reg-input"
            />
          </div>
          <div>
            <span class="title2">{{ $t("user.passwd") }}</span>
            <el-input
              v-model="passWord"
              :placeholder="$t('user.password')"
              type="password"
              class="reg-input"
              show-password
              @input="passwordCheck"
              @keyup.enter.native="register"
            />
          </div>
          <div :class="passerror ? 'rep passerr' : 'rep'">
            <span class="title2">{{ $t("user.repeatpasswd") }}</span>
            <el-input
              v-model="repeatPassWord"
              :placeholder="$t('user.password')"
              type="password"
              :class="passerror ? 'reg-input inputerr' : 'reg-input'"
              show-password
              @input="notsame"
              @keyup.enter.native="register"
            />
            <div v-if="passerror" class="passerror">
              {{ $t("modify.reenter") }}
            </div>
          </div>
          <div class="agreement">
            <reg-agreement @check="check" />
          </div>
          <el-button type="primary" class="r-button" @click="register">{{
            forums && forums.set_site && forums.set_site.open_ext_fields === '1'
              ? $t("profile.registernext")
              : $t("profile.register")
          }}</el-button>
          <div class="tologin">
            <span
              @click="jump2Login"
            >{{ $t("user.exist")
            }}<nuxt-link to="/user/login">{{ $t("user.login") }}</nuxt-link>
            </span>
          </div>
        </form>
      </el-tab-pane>
    </el-tabs>
    /*FITRUE_default*/
    /*IFTRUE_pay*/
    <div class="register-header">用户名注册</div>
    <div class="register-content">
      <form class="register-form">
        <div class="input-box">
          <span class="title">{{ $t('profile.username') }}：</span>
          <el-input v-model="userName" :placeholder="$t('user.username')" />
        </div>
        <div class="input-box">
          <span class="title">{{ $t('user.passwd') }}：</span>
          <el-input
            v-model="passWord"
            :placeholder="$t('user.password')"
            type="password"
            show-password
            @input="passwordCheck"
            @keyup.enter.native="register"
          />
        </div>
        <div class="input-box" :class="passerror ?'rep passerr':'rep'">
          <span class="title">{{ $t('user.repeatpasswd') }}：</span>
          <el-input
            v-model="repeatPassWord"
            :placeholder="$t('user.password')"
            type="password"
            :class="passerror ? 'reg-input inputerr':'reg-input'"
            show-password
            @input="notsame"
            @keyup.enter.native="register"
          />
          <div v-if="passerror" class="passerror">{{ $t('modify.reenter') }}</div>
        </div>
        <el-button type="primary" class="r-button" @click="register">
          {{ $t('profile.registernext') }}
        </el-button>
        <div class="agreement"><reg-agreement @check="check" /></div>
        <!-- 其它登录方式 -->
        <div class="tologin">
          <span @click="jump2Login">
            {{ $t('user.exist') }}
            <nuxt-link to="/user/login">{{ $t('user.login') }}</nuxt-link>
          </span>
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
const tcaptchs = process.client ? require('@/utils/tcaptcha') : '';
const passWordReg = new RegExp(/[^x00-xff]/, 'ig');

export default {
  name: 'Register',
  mixins: [head, handleError, tcaptchs, loginAbout],
  data() {
    return {
      title: this.$t('profile.register'),
      userName: '',
      passWord: '',
      repeatPassWord: '',
      activeName: '0', // 默认激活tab
      validate: false, // 默认不开启注册审核
      code: '', // 注册邀请码
      register_captcha: false, // 默认不开启注册验证码
      site_mode: '', // 站点模式
      isPaid: false, // 是否付费
      captcha: null, // 腾讯云验证码实例
      ticket: '',
      randstr: '',
      ischeck: true,
      loading: false,
      passerror: false,
      preurl: '/'
    };
  },
  computed: {
    forums() {
      return this.$store.state.site.info.attributes || {};
    }
  },
  mounted() {
    const { validate, code, preurl } = this.$route.query;
    if (preurl) {
      this.preurl = preurl;
    }
    if (validate) {
      this.validate = JSON.parse(validate);
    }
    if (code !== 'undefined') {
      this.code = code;
    }
    if (
      this.forums
      && this.forums.set_reg
      && this.forums.set_reg.register_captcha
    ) {
      this.register_captcha = this.forums.set_reg.register_captcha;
    }
    if (this.forums && this.forums.set_site && this.forums.set_site.site_mode) {
      this.site_mode = this.forums.set_site.site_mode;
    }

    if (this.forums && this.forums.set_reg) {
      this.validate = this.forums.set_reg.register_validate;
    }
  },
  methods: {
    notsame() {
      this.repeatPassWord = this.repeatPassWord.replace(passWordReg, '');
      if (this.passWord !== this.repeatPassWord) {
        this.passerror = true;
      } else {
        this.passerror = false;
      }
    },
    check(value) {
      this.ischeck = value;
    },
    passwordCheck() {
      this.passWord = this.passWord.replace(passWordReg, '');
    },
    // 用户名注册
    register() {
      if (this.userName === '') {
        this.$message.error('用户名不能为空');
      } else if (this.passWord === '') {
        this.$message.error('密码不能为空');
      } else if (this.passWord !== this.repeatPassWord) {
        this.$message.error('两次输入的密码不一致,请重新输入');
        this.passWord = '';
        this.repeatPassWord = '';
        this.passerror = true;
      } else if (!this.ischeck) {
        this.$message.error('请同意协议');
      } else if (
        this.forums
        && this.forums.set_reg
        && this.forums.set_reg.register_captcha
      ) {
        this.toTCaptcha();
      } else {
        this.registerClick();
      }
    },
    // 验证码
    toTCaptcha() {
      if (
        this.forums
        && this.forums.qcloud
        && this.forums.qcloud.qcloud_captcha_app_id
      ) {
        // eslint-disable-next-line no-undef
        this.captcha = new TencentCaptcha(
          this.forums.qcloud.qcloud_captcha_app_id,
          res => {
            if (res.ret === 0) {
              this.ticket = res.ticket;
              this.randstr = res.randstr;
              this.registerClick();
            }
          }
        );
        // 显示验证码
        this.captcha.show();
      }
    },
    // 用户名注册
    registerClick() {
      this.loading = true;
      const params = {
        data: {
          attributes: {
            username: this.userName,
            password: this.passWord,
            register_type:
              (this.forums && this.forums.set_reg && this.forums.set_reg.register_type) || 0 // 注册需要提供当前登陆方式
          }
        }
      };
      if (this.register_captcha && this.validate) {
        params.data.attributes.captcha_ticket = this.ticket;
        params.data.attributes.captcha_rand_str = this.randstr;
      }
      if (this.register_captcha) {
        params.data.attributes.captcha_ticket = this.ticket;
        params.data.attributes.captcha_rand_str = this.randstr;
      }
      if (this.code !== '') {
        params.data.attributes.code = this.code;
      }
      this.$store
        .dispatch('session/h5Register', params)
        .then(res => {
          this.loading = false;
          if (res && res.data && res.data.data && res.data.data.id) {
            this.logind(res);
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
            return;
          }
          if (
            res
            && res.data
            && res.data.errors
            && res.data.errors[0].code === 'content_banned'
          ) {
            this.$message.error('当前用户名包含禁止注册的词语');
            return;
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
          this.passWord = '';
          this.repeatPassWord = '';
          console.log(err);
        });
    },
    jump2Login() {
      this.$router.push(
        `/user/login?&validate=${this.validate}&code=${this.code}&preurl=${this.preurl}`
      );
    }
  }
};
</script>
<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
::v-deep input::-ms-reveal {
  display: none;
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
    .rep {
      position: relative;
      .passerror {
        position: absolute;
        bottom: 0px;
        left: 85px;
        color: #fa5151;
      }
    }
    .passerr {
      height: 66px;
      margin-bottom: 10px;
    }
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
    .inputerr {
      ::v-deep .el-input__inner {
        border: 1px solid #fa5151;
        color: #fa5151;
      }
    }
  }
  .agreement {
    margin-left: 89px;
    font-size: 12px;
    a {
      color: #1878f3;
    }
    .logorreg {
      margin-top: 28px;
    }
    .agree {
      color: #6d6d6d;
      margin-left: 6px;
    }
    .agreement_text {
      color: #1878f3;
    }
  }
  .r-button {
    width: 300px;
    margin-left: 88px;
    margin-top: 30px;
    background: #1878f3;
  }
  .tologin {
    margin-left: 89px;
    font-size: 12px;
    margin-bottom: 160px;
    margin-top: 14px;
    a {
      color: $color-blue-base;
    }
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
@import "@/assets/css/pay_scss/user/register.scss";
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
    margin-bottom: 6px;
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
