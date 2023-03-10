module.exports = {
  methods: {
    handleLogin(url, code) {
      this.toregister(url, code);
    },
    toregister(url = '/', code) {
      const loginPath = ['/user/wechat-bind-phone',
        '/user/login-bind-phone', '/user/login-bind',
        '/user/phone-login-register', '/user/phone-login',
        '/user/register-bind-phone', '/user/register-bind', '/user/wechat', '/user/login', '/user/register'
      ];
      let preurl;
      if (loginPath.includes(this.$route.path)) {
        if (this.preurl) {
          preurl = this.preurl;
        }
      } else {
        preurl = this.$route.fullPath;
      }
      if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 0) {
        // 用户名模式
        this.$router.push(`/user/register?code=${code}&preurl=${preurl}`);
      }
      if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 1) {
        // 手机模式
        this.$router.push(`/user/phone-login-register?code=${code}&preurl=${preurl}`);
      }
      if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 2) {
        //  微信模式
        if (this.forums && this.forums.passport
          && this.forums.passport.oplatform_close && this.forums.passport.offiaccount_close) {
          this.$router.push(`/user/wechat?code=${code}&preurl=${preurl}`);
        } else if (this.forums && this.forums.qcloud && this.forums.qcloud.qcloud_sms) {
          this.$router.push(`/user/phone-login-register?code=${code}&preurl=${preurl}`);
        } else {
          this.$router.push(`/user/register?code=${code}&preurl=${preurl}`);
        }
      }
    },
    headerTologin() {
      const loginPath = ['/user/wechat-bind-phone',
        '/user/login-bind-phone', '/user/login-bind',
        '/user/phone-login-register', '/user/phone-login',
        '/user/register-bind-phone', '/user/register-bind', '/user/wechat', '/user/login', '/user/register'
      ];
      let preurl;
      if (loginPath.includes(this.$route.path)) {
        if (this.preurl) {
          preurl = this.preurl;
        }
      } else {
        preurl = this.$route.fullPath;
      }
      if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 0) {
        // 用户名模式
        this.$router.push(`/user/login?preurl=${preurl}`);
      } else if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 1) {
        // 手机模式
        this.$router.push(`/user/phone-login-register?preurl=${preurl}`);
      } else if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 2) {
        //  微信模式
        if (this.forums && this.forums.passport
          && this.forums.passport.oplatform_close
          && this.forums.passport.offiaccount_close) {
          this.$router.push(`/user/wechat?preurl=${preurl}`);
        } else if (this.forums && this.forums.qcloud && this.forums.qcloud.qcloud_sms) {
          this.$router.push(`/user/phone-login-register?preurl=${preurl}`);
        } else {
          this.$router.push(`/user/login?preurl=${preurl}`);
        }
      } else {
        this.$router.push(`/user/login?preurl=${preurl}`);
      }
    },
    logind(loginInfo) {
      // open_ext_fields 是否开启注册审核 1 0 是 否
      const ext_fields = this.forums.set_site.open_ext_fields;
      // 新用户如果开启了拓展配置去填拓展信息，老用户直接登陆
      if (loginInfo && loginInfo.data.data.attributes.new_user && ext_fields === '1') {
        this.$router.push(`/user/supple-mentary?id=${loginInfo.data.data.id}`);
        return;
      }
      const userId = this.$store.getters['session/get']('userId');
      const preurl = this.preurl.indexOf('user/warning') !== -1 ? '/' : this.preurl;
      if (!userId) return;
      const params = {
        include: 'groups'
      };
      // 登录成功重新获取一下站点信息
      this.$store.dispatch('site/getSiteInfo');

      this.$store.dispatch('jv/get', [`users/${userId}`, {
        params
      }]).then((val) => {
        this.user = val;
        if (this.user && this.user.paid) {
          this.isPaid = this.user.paid;
        }
        if (loginInfo && loginInfo.data.data.attributes.new_user) {
          if (ext_fields !== '1') {
            this.$message.success(this.$t('user.registerSuccess'));
          } 
        } else {
          this.$message.success(this.$t('user.loginSuccess'));
        } 
        if (this.site_mode !== 'pay' || this.isPaid) {
          this.$router.push(`${preurl}`);
        }
        if (this.site_mode === 'pay' && !this.isPaid) {
          this.$router.push('/site/info');
        }
      }).catch((e) => {
        const { response: { data: { errors }}} = e;
        if (Array.isArray(errors) && errors.length > 0) {
          const errorCode = errors[0].code;
          console.log(errors, 'errorCode');
          if (errorCode === 'register_validate') {
            this.$store.commit('session/SET_AUDIT_INFO', {
              errorCode: 'register_validate',
              username: errors[0].data.userName
            });
            this.$router.push('/user/warning');
          }
          if (errorCode === 'validate_reject') {
            this.$store.commit('session/SET_AUDIT_INFO', {
              errorCode: 'validate_reject',
              reason: errors[0].data.rejectReason,
              username: errors[0].data.userName
            });
            this.$router.push('/user/warning');
          }
        }
        this.handleError(e);
      });

    },
    headerTologin2() {
      const loginPath = ['/user/wechat-bind-phone',
        '/user/login-bind-phone', '/user/login-bind',
        '/user/phone-login-register', '/user/phone-login',
        '/user/register-bind-phone', '/user/register-bind', '/user/wechat', '/user/login', '/user/register'
      ];
      let preurl;
      if (loginPath.includes(this.$route.path)) {
        if (this.preurl) {
          preurl = this.preurl;
        }
      } else {
        preurl = this.$route.fullPath;
      }
      if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 0) {
        // 用户名模式
        location.href = `/user/login?preurl=${preurl}`;
      } else if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 1) {
        // 手机模式
        location.href = `/user/phone-login-register?preurl=${preurl}`;
      } else if (this.forums && this.forums.set_reg && this.forums.set_reg.register_type === 2) {
        //  微信模式
        if (this.forums && this.forums.passport
          && this.forums.passport.oplatform_close
          && this.forums.passport.offiaccount_close) {
          location.href = `/user/wechat?preurl=${preurl}`;
        } else if (this.forums && this.forums.qcloud && this.forums.qcloud.qcloud_sms) {
          location.href = `/user/phone-login-register?preurl=${preurl}`;
        } else {
          location.href = `/user/login?preurl=${preurl}`;
        }
      } else {
        location.href = `/user/login?preurl=${preurl}`;
      }
    }
  }
};
