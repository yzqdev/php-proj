<template>
  <div v-if="dataInfo" class="mywallet">
    <!-- 钱包信息 -->
    <div class="mywallet-top">
      /*IFTRUE_default*/
      <div class="mywallet-topitem">
        <div class="margbtm">{{ $t('profile.availableamount') }}</div>
        <div
          v-if="dataInfo.available_amount > 0"
          class="amount"
          style="color:#1878F3;"
        >{{ `¥ ${dataInfo && dataInfo.available_amount || 0.0}` }}
          <span
            class="availmount"
            @click="showWithdraw"
          >{{ $t('modify.withdratitle') }}</span>
        </div>
        <div
          v-else
          class="amount"
        >{{ `¥ ${dataInfo && dataInfo.available_amount || 0.0}` }}
          <span
            class="availmount"
            @click="showWithdraw"
          >{{ $t('modify.withdratitle') }}</span>
        </div>
      </div>
      <div class="mywallet-topitem mymarg">
        <div class="margbtm">{{ $t('profile.freezeamount') }}</div>
        <div
          v-if="dataInfo.freeze_amount > 0"
          class="amount"
          style="color:#FA5151;margin-right: 13px;"
        >{{ `¥ ${dataInfo && dataInfo.freeze_amount || 0.0}` }}</div>
        <div
          v-else
          class="amount"
          style="color:#d0d4dc;margin-right: 13px;"
        >{{ `¥ ${dataInfo && dataInfo.freeze_amount || 0.0}` }}</div>
      </div>
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      <div class="mywallet-topitem">
        <div class="margbtm">{{ $t('profile.availableamount') }}</div>
        <div
          v-if="dataInfo.available_amount > 0"
          class="amount"
          style="color:#1878f3;"
        >{{ `¥ ${dataInfo && dataInfo.available_amount || 0.0}` }}
          <span
            class="availmount"
            @click="showWithdraw"
          >{{ $t('modify.withdratitle') }}</span>
        </div>
        <div
          v-else
          class="amount"
        >{{ `¥ ${dataInfo && dataInfo.available_amount || 0.0}` }}
          <span
            class="availmount"
            @click="showWithdraw"
          >{{ $t('modify.withdratitle') }}</span>
        </div>
      </div>
      <div class="mywallet-topitem mymarg">
        <div class="margbtm">{{ $t('profile.freezeamount') }}</div>
        <div
          v-if="dataInfo.freeze_amount > 0"
          class="amount"
          style="color:#888888;"
        >{{ `¥ ${dataInfo && dataInfo.freeze_amount || 0.0}` }}</div>
        <div
          v-else
          class="amount"
        >{{ `¥ ${dataInfo && dataInfo.freeze_amount || 0.0}` }}</div>
      </div>
      /*FITRUE_pay*/
      <div class="mywallet-topitem mywallet-r">
        <div class="margbtm">{{ $t('pay.payPassword') }}</div>
        <div v-if="hasPassword">
          <span class="changepas" @click="changePassword">
            <svg-icon
              type="shield"
              class="shield-icon"
            />{{ $t('profile.walletpasset') }}
          </span>
        </div>
        <div
          v-else
          style="cursor: pointer;"
          @click="setPassword"
        >
          <svg-icon
            type="warning"
            class="shield-icon"
          />{{ $t('profile.setpaypassword') }}
        </div>
      </div>
      <!-- 设置钱包密码 -->
      <div v-if="hasPassword">
        <wallet-password
          v-if="showPasswordInput"
          ref="walletpass"
          :error="passError"
          :password-error-tip="passwordErrorTip"
          @close="showPasswordInput = false"
          @password="validatePass"
          @findpaypwd="findPaypwd"
        />
        <set-newpassword
          v-if="showNewverify"
          @close="showNewverify = false"
          @password="checkpass"
        />
        <repeat-newpassword
          v-if="showNewverify2"
          ref="repeatnewpass"
          :error="codeError"
          @close="repeatclose"
          @password="checkpass2"
        />
      </div>
      <div v-else>
        <set-password
          v-if="setPasswordInput"
          @close="setPasswordInput = false"
          @password="setPass"
        />
        <repeat-password
          v-if="repPasswordInput"
          ref="repeatpass"
          :error="codeError"
          @close="repPasswordInput = false"
          @password="setPass2"
        />
      </div>
      <find-paypwd
        v-if="isfindpwd"
        :mobile="dataInfo.user.mobile"
        :phonenum="dataInfo.user.originalMobile"
        @close="isfindpwd = false"
      />
      <without-phone v-if="isWithoutphone" @close=" isWithoutphone = false" />
    </div>
    <el-tabs
      type="border-card"
      class="register-select"
    >
      <!-- 提现记录 -->
      <el-tab-pane :label="$t('profile.withdrawalslist')">
        <cash-dialog ref="cash" />
      </el-tab-pane>
      <!-- 冻结金额 -->
      <el-tab-pane :label="$t('profile.freezeamount')">
        <freeze-amount />
      </el-tab-pane>
      <!-- 钱包明细 -->
      <el-tab-pane :label="$t('profile.walletlist')">
        <wallet-detail />
      </el-tab-pane>
      <!-- 订单明细 -->
      <el-tab-pane :label="$t('profile.orderlist')">
        <order-detail />
      </el-tab-pane>
    </el-tabs>
    <withdrawal
      v-if="isWithdraw"
      @close="updateWithdraw"
    />
  </div>
</template>

<script>
import { status } from '@/store/modules/jsonapi-vuex/index';
import head from '@/mixin/head';
import handleError from '@/mixin/handleError';

export default {
  name: 'Wallet',
  layout: 'center_layout',
  mixins: [
    head,
    handleError
  ],
  data() {
    return {
      title: this.$t('profile.mywallet'),
      isWithdraw: false,
      inputpas: '', // 第一次新密码
      usertokenid: '', // 原密码验证成功id
      showNewverify: false, // 新密码验证码框
      showNewverify2: false, // 新密码验证码框
      setPasswordInput: false, // 设置密码框
      repPasswordInput: false, // 设置重复密码框
      showPasswordInput: false, // 修改密码框
      passError: false, // 修改原密码
      codeError: false, // 再次输入密码错误判断
      loadingType: '', // 读取状态
      dataInfo: '', // 钱包信息
      hasPassword: false,
      issendcode: false,
      isfindpwd: false,
      isWithoutphone: false,
      passwordErrorTip: ''
    };
  },
  computed: {
    forums() {
      return this.$store.state.site.info.attributes || {};
    },
    userId() {
      return this.$store.getters['session/get']('userId');
    }
  },
  watch: {
    userId(id) {
      if (id) this.getInfo();
    }
  },
  mounted() {
    if (this.userId) this.getInfo();
  },
  methods: {
    // 提现成功后的数据更新
    updateWithdraw() {
      this.isWithdraw = false;
      this.$refs.cash.getList();
    },
    // 提现
    showWithdraw() {
      this.isWithdraw = true;
    },
    // 设置密码框展示
    setPassword() {
      this.setPasswordInput = true;
    },
    // 修改密码框展示
    changePassword() {
      this.showPasswordInput = true;
    },
    repeatclose() {
      this.showNewverify2 = false;
      this.codeError = false;
    },
    // 原密码判断
    validatePass(password = '') {
      const params = {
        _jv: {
          type: 'users/pay-password/reset'
        },
        pay_password: password
      };
      const postphon = status.run(() => this.$store.dispatch('jv/post', params));
      postphon
        .then((res) => {
          if (res._jv.json.data.id) {
            this.passError = false;
            this.showPasswordInput = false;
            this.$message.success(this.$t('modify.authensucceeded'));
            this.showNewverify = true;
            this.usertokenid = res._jv.json.data.id;
          }
        })
        .catch((err) => {
          this.$refs.walletpass.empty();
          const { response: { data: { errors }}} = err;
          if (errors[0].code === 'validation_error') {
            this.passError = true;
            // eslint-disable-next-line prefer-destructuring
            this.passwordErrorTip = errors[0].detail[0];
            return;
          }
          this.handleError(err);
        });
    },
    // 新密码
    checkpass(num) {
      if (num.length >= 6) {
        this.inputpas = num;
        this.showNewverify = false;
        this.showNewverify2 = true;
      }
    },
    // 重复输入新密码
    checkpass2(sum) {
      if (sum.length >= 6) {
        this.validateVerify(sum);
      }
    },
    // 获取初始化密码
    setPass(password) {
      if (password.length >= 6) {
        this.inputpas = password;
        this.setPasswordInput = false;
        this.repPasswordInput = true;
      }
    },
    // 重复初始化密码
    setPass2(password) {
      if (password.length >= 6) {
        this.validateVerify(password);
      }
    },
    // 初始密码判断
    validateVerify(password = '') {
      const params = {
        _jv: {
          type: 'users',
          id: this.userId
        },
        pay_password_token: this.usertokenid, // 初始化密码不需要
        payPassword: this.inputpas,
        pay_password_confirmation: password
      };
      const postphon = status.run(() => this.$store.dispatch('jv/patch', params));
      postphon
        .then((res) => {
          this.inputpas = '';
          if (res) {
            this.repPasswordInput = false;
            this.showNewverify2 = false;
            this.$message.success(this.$t('modify.paymentsucceed'));
            // this.$router.go(0)
            this.getInfo();
          }
        })
        .catch((err) => {
          if (this.$refs.repeatnewpass) {
            this.$refs.repeatnewpass.empty();
          }
          if (this.$refs.repeatpass) {
            this.$refs.repeatpass.empty();
          }
          this.codeError = true;
          this.handleError(err);
        });
    },
    // 获取钱包信息
    getInfo() {
      status
        .run(() => this.$store.dispatch('jv/get', `wallet/user/${this.userId}`))
        .then((res) => {
          this.dataInfo = res;
          this.hasPassword = res.user.canWalletPay;
        }, e => this.handleError(e));
    },
    // 找回密码
    findPaypwd() {
      if (this.dataInfo.user.mobile) {
        this.isfindpwd = true;
      } else {
        this.isWithoutphone = true;
      }
    }
  }

};
</script>
<style lang='scss' scoped>
@import "@/assets/css/variable/color.scss";

.mywallet {
  padding-left: 30px;
   margin-top: 44px !important;
  @media screen and (max-width: 1005px) {
    padding-left: 0 15px;
  }
  .mywallet-top {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    .mymarg {
      text-align: center;
    }
    .mywallet-topitem {
      flex: 1;
      color: #6d6d6d;
      .shield-icon {
        width: 20px;
        height: 20px;
        margin-right: 5px;
        vertical-align: sub;
      }
      .amount {
        font-size: 20px;
        color: #000000;
        min-width: 115px;
        margin-left: 5px;
      }
      .availmount {
        font-size: 14px;
        color: #1878f3;
        /*IFTRUE_pay*/
        color: #1878f3;
        /*FITRUE_pay*/ 
        line-height: 23px;
        vertical-align: top;
        margin-left: 12px;
        cursor: pointer;
      }
      .margbtm {
        margin-bottom: 10px;
      }
    }
    .mywallet-r {
      margin-right: 30px;
      text-align: right;
      .changepas {
        cursor: pointer;
        color: #8590a6;
        font-size: 16px;
      }
    }
  }
  .register-select {
    margin-top: 56px;
    border: none;
    background: transparent;
    box-shadow: none;
    padding-right: 30px;
  }
  .margleft {
    margin-left: 30px;
    @media screen and (max-width: 850px) {
      margin-left: 0px;
    }
  }
}

::v-deep.el-tabs {
  .el-tabs__header {
    background: transparent;
  }
  .el-tabs__header .el-tabs__item {
    border: none;
    color: #b5b5b5;
    padding: 0 52px 0 2px;
    font-size: 16px;
    transition: none;
  }
  .el-tabs__content {
    padding: 10px 0;
  }
  .el-tabs__header .el-tabs__item:nth-child(2) {
    padding-left: 0px;
  }
  .el-tabs__header .el-tabs__item.is-active {
    /*IFTRUE_default*/ 
    color: black;
    /*FITRUE_default*/
    /*IFTRUE_pay*/ 
    color: rgba(47, 133, 244, 0.8);
    /*FITRUE_pay*/
    background: transparent;
    border: none;
    font-weight: bold;
    font-size: 18px;
  }
  .el-tabs__nav-wrap {
    margin-bottom: 4px;
    height: 40px;
  }
  .el-input__inner {
    border-radius: 2px;
  }
  .el-button {
    border-radius: 2px;
  }
  .el-input__inner:focus {
    border-color: #dcdfe6;
  }
  .el-input__inner:hover {
    border-color: #dcdfe6;
  }
}
::v-deep .el-tabs--border-card > .el-tabs__header .el-tabs__item:hover {
  /*IFTRUE_default*/ 
  color: #00479b;
  /*FITRUE_default*/ 
  /*IFTRUE_pay*/ 
  color: rgba(47, 133, 244, 0.8);
  /*FITRUE_pay*/
}
</style>
