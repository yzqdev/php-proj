<template>
  <message
    v-if="userInfo"
    :title="$t('profile.towithdrawal')"
    @close="$emit('close')"
  >
    <div class="top">
      <div class="row">
        <div v-if="cashtype" class="head">微信号</div>
        <div v-else class="head">{{ $t("modify.wechatpayee") }}</div>
        <div v-if="cashtype" class="body reward">
          <label v-if="userInfo.wechat && userInfo.wechat.nickname">
            <input
              v-model="userInfo.wechat.nickname"
              placeholder="微信号"
              type="text"
              class="rinput ript"
            >
          </label>
          <span v-else style="font-size:12px;">
            您的用户账号未绑定微信号,请绑定微信号后再进行提现
          </span>
        </div>
        <div v-else class="body reward">
          <label>
            <input
              v-model="withdrawlNumber"
              :placeholder="$t('modify.enterwechat')"
              type="text"
              class="rinput ript"
            >
          </label>
          <div class="cashtext">{{ $t("pay.cashtext") }}</div>
        </div>
      </div>
      <div class="row">
        <div class="head">{{ $t("modify.withdrawable") }}</div>
        <div class="body product-information">
          <span class="title2"> ￥ {{ balance }}</span>
        </div>
      </div>
      <div class="row row2">
        <div class="head reward">
          {{ $t("modify.withdratitle") + $t("pay.sumOfMoney") }}
        </div>
        <div class="body reward">
          <label>
            <span>￥</span>
            <input
              v-model="canAmount"
              :placeholder="$t('modify.enteramount')"
              type="text"
              class="rinput"
              @input="settlement"
            >
          </label>
        </div>
      </div>
      <div class="row">
        <div class="head">{{ $t("modify.actualamout") }}</div>
        <div class="body product-information">
          <span class="title3"> ￥ {{ contint || 0 }}</span>
          <span class="title4">
            {{ $t("modify.servicechaege") }}{{ procedures
            }}{{ $t("modify.percentage") }} {{ percentage }}%)</span>
        </div>
      </div>
      <div class="row">
        <div class="head reward">{{ $t("user.verificationCode2") }}</div>
        <div class="body reward">
          <!-- 发送验证码 -->
          <label>
            <span />
            <input
              v-model="verifycode"
              :placeholder="$t('modify.placeEnterCode')"
              type="text"
            >
            <el-button
              size="medium"
              :disabled="!canClick || !usertestphon"
              @click="sendsms"
            >{{ content }}</el-button>
          </label>
          <span v-if="usertestphon" class="phone">
            {{ $t("profile.codesend") }}
            <span style="font-weight:bold;color:#000000; ">{{
              usertestphon
            }}</span>
            {{ $t("profile.phonesms") }}
          </span>
          <span v-else class="phone">
            {{ $t("profile.withoutbind") }}
            <span class="phonei" @click="tomy">
              {{ $t("profile.personalhomepage") }}
            </span>
            {{ $t("profile.tobindphone") }}
          </span>
        </div>
      </div>
    </div>
    <div class="bottom">
      <span v-if="cashtype" style="font-size:14px">
        ￥{{ contint || 0
        }}{{
          $t("pay.rmb") +
            $t("profile.withdrawalto") +
            (userInfo && userInfo.wechat && userInfo.wechat.nickname
              ? userInfo.wechat.nickname
              : "")
        }}{{ $t("pay.ofAccount") }}
      </span>
      <span v-else style="font-size:14px">
        ￥{{ contint || 0
        }}{{ $t("pay.rmb") + $t("profile.withdrawalto") + withdrawlNumber || ""
        }}{{ $t("pay.ofAccount") }}
      </span>
      <el-button size="medium" type="primary" class="border" @click="btncash">{{
        $t("profile.comfirmwithdrawal")
      }}</el-button>
    </div>
  </message>
</template>

<script>
import { status } from '@/store/modules/jsonapi-vuex/index';
import handleError from '@/mixin/handleError';
import countDown from '@/mixin/countDown';
const tcaptchs = process.client ? require('@/utils/tcaptcha') : '';

export default {
  name: 'Withdrawal',
  mixins: [handleError, tcaptchs, countDown],
  props: {
    price: {
      type: [Number, String],
      default: ''
    },
    error: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      userId: this.$store.getters['session/get']('userId'), // 获取当前登陆用户的ID
      userInfo: '',
      name: '',
      balance: '',
      userphon: '',
      usertestphon: '',
      canAmount: '',
      canClick: true,
      disabtype: false,
      verifycode: '',
      content: this.$t('modify.sendVerifyCode'),
      percentage: 0,
      contint: '', // 实际金额
      procedures: 0,
      cost: 0,
      interval: '',
      captcha: null, // 腾讯云验证码实例
      ticket: '', // 腾讯云验证码返回票据
      randstr: '', // 腾讯云验证码返回随机字符串
      captchaResult: {},
      withdrawlNumber: '',
      cashtype: 0
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
        if (val.paycenter && (val.paycenter.wxpay_mchpay_close || val.paycenter.wxpay_mchpay2_close)) {
          this.cashtype = 1;
        } else {
          this.cashtype = 0;
        }
      },
      deep: true,
      immediate: true
    }
  },
  mounted() {
    this.userinfo();
    this.forumsdata();
  },
  methods: {
    userinfo() {
      const params = {
        include: 'groups,wechat'
      };
      this.$store.dispatch('jv/get', [`users/${this.userId}`, { params }]).then(
        res => {
          this.userInfo = res;
          this.setmydata();
          this.userInfo.groupsName = this.userInfo.groups
            ? this.userInfo.groups[0].name
            : '';
        },
        e => {
          const {
            response: {
              data: { errors }
            }
          } = e;
          if (
            errors
            && Array.isArray(errors)
            && errors.length > 0
            && errors[0]
          ) {
            const error
              = errors[0].detail && errors[0].detail.length > 0
                ? errors[0].detail[0]
                : errors[0].code;
            if (error === 'Invalid includes [wechat]') {
              this.$message.error(error);
              localStorage.removeItem('access_token');
              localStorage.removeItem('user_id');
              window.location.replace('/');
            } else {
              const errorText
                = errors[0].detail && errors[0].detail.length > 0
                  ? errors[0].detail[0]
                  : this.$t(`core.${error}`);
              this.$message.error(errorText);
            }
          }
        }
      );
    },
    setmydata() {
      this.name = this.userInfo.username;
      this.balance = this.userInfo.walletBalance;
      this.usertestphon = this.userInfo.mobile;
      this.userphon = this.userInfo.originalMobile;
      this.withdrawlNumber = this.userphon;
      if (!this.usertestphon) {
        this.disabtype = true;
      }
    },
    forumsdata() {
      this.cost = this.forums.set_cash.cash_rate;
      this.percentage = this.forums.set_cash.cash_rate * 100;
      this.cashtype
        = this.forums.paycenter && (this.forums.paycenter.wxpay_mchpay_close || this.forums.paycenter.wxpay_mchpay2_close)
          ? 1
          : 0;
    },
    settlement() {
      setTimeout(() => {
        if (this.canAmount[0] === '.') {
          const num = 0;
          const cun = '.';
          this.canAmount = num + cun;
        }
        this.canAmount = this.canAmount
          .replace(/[^\d.]/g, '')
          .replace(/\.{2,}/g, '.')
          .replace('.', '$#$')
          .replace(/\./g, '')
          .replace('$#$', '.')
          .replace(/^(-)*(\d+)\.(\d\d).*$/, '$1$2.$3')
          .replace(/^\./g, '');
        if (Number(this.canAmount) > Number(this.balance)) {
          this.canAmount = this.canAmount.slice(0, this.canAmount.length - 1);
          this.$message.warning(this.$t('modify.greaterthan'));
        }
        if (this.canAmount.length > 0) {
          this.length = true;
          const number = this.canAmount - (this.canAmount * this.cost);
          if (number) {
            this.contint = `${number.toFixed(2)}`;
            const casnumber = this.canAmount * this.cost;
            this.procedures = casnumber.toFixed(2);
          } else {
            this.contint = '';
            this.procedures = '';
          }
        } else if (this.canAmount.length <= 0) {
          // this.length = false;
          this.contint = '';
          const casnumber = this.canAmount * this.cost;
          this.procedures = casnumber.toFixed(2);
        }
      }, 5);
    },
    // 发送验证码
    sendsms() {
      if (this.forums.qcloud.qcloud_captcha) {
        this.tcaptcha();
      } else {
        this.second = 60;
        this.sendVerifyCode();
      }
    },
    // 腾讯验证码
    tcaptcha() {
      // eslint-disable-next-line no-undef
      this.captcha = new TencentCaptcha(
        this.forums.qcloud.qcloud_captcha_app_id,
        res => {
          if (res.ret === 0) {
            this.ticket = res.ticket;
            this.randstr = res.randstr;
            // 验证通过后发布
            // 修改手机验证码发送
            this.sendVerifyCode();
          }
        }
      );
      // 显示验证码
      this.captcha.show();
    },
    sendVerifyCode() {
      const params = {
        _jv: {
          type: 'sms/send'
        },
        mobile: this.userphon,
        type: 'verify',
        captcha_ticket: this.ticket,
        captcha_rand_str: this.randstr
      };
      const postphon = status.run(() =>
        this.$store.dispatch('jv/post', params)
      );
      postphon.then(
        res => {
          if (res.interval) this.countDown(res.interval);
          this.ticket = '';
          this.randstr = '';
        },
        e => this.handleError(e)
      );
    },
    btncash() {
      this.verifytitle();
    },
    // 验证短信
    verifytitle() {
      const params = {
        _jv: {
          type: 'sms/verify'
        },
        code: this.verifycode,
        type: 'verify'
      };
      this.$store.dispatch('jv/post', params).then(
        res => {
          if (res) {
            this.cashwithdrawal();
          }
        },
        e => {
          // eslint-disable-next-line object-curly-spacing
          const {
            response: {
              data: { errors }
            }
          } = e;
          if (errors[0]) {
            if (errors[0].status === '422') {
              this.$message.error(errors[0].detail[0]);
            } else if (errors[0].status === 500) {
              this.$message.error(this.$t(`core.${errors[0].code}`));
            }
          }
        }
      );
    },
    // 提现申请
    cashwithdrawal() {
      const params = {
        _jv: {
          type: 'wallet/cash',
          include: ['user', 'userWallet']
        },
        cash_apply_amount: this.canAmount,
        cash_mobile: this.withdrawlNumber,
        cash_type: this.cashtype
      };
      const postcash = status.run(() =>
        this.$store.dispatch('jv/post', params)
      );
      postcash.then(
        res => {
          if (res) {
            this.canAmount = '';
            this.contint = '';
            this.procedures = 0;
            // 重新更新数据
            // this.$store.dispatch('user/getUserInfo', this.userId)
            this.$message.success(this.$t('modify.withdrawal'));
            this.$emit('close');
            // this.$router.go(0)
          }
        },
        e => {
          // eslint-disable-next-line object-curly-spacing
          const {
            response: {
              data: { errors }
            }
          } = e;
          if (errors[0]) {
            if (errors[0].status === '422') {
              this.$message.error(errors[0].detail[0]);
              this.canAmount = '';
              this.contint = '';
              this.procedures = 0;
            } else if (errors[0].status === 500) {
              if (errors[0].code === 'unbind_wechat') {
                this.$message.error(errors[0].detail);
              } else if (errors[0].code === 'cash_mch_invalid') {
                this.$message.error(errors[0].detail);
              } else {
                this.$message.error(this.$t(`core.${errors[0].code}`));
                this.canAmount = '';
                this.contint = '';
                this.procedures = 0;
              }
            }
          }
        }
      );
    },
    tomy() {
      /*IFTRUE_default*/
      this.$router.push('/my/profile');
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      this.$router.push('/my/profilePay');
      /*FITRUE_pay*/
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
.top {
  padding: 20px;
  > .row {
    margin-top: 20px;
    padding: 0 0 20px 20px;
    display: flex;
    border-bottom: 1px solid $border-color-base;

    &:last-child {
      border-bottom: none;
    }

    > .head {
      width: 142px;
      color: #000000;

      &.reward {
        line-height: 40px;
      }
    }

    > .body {
      flex: 1;
      margin-left: 15px;
      &.product-information {
        span {
          display: block;
          line-height: 22.5px;
          color: $font-color-grey;

          &.title {
            font-size: 16px;
            color: #000000;
            font-weight: bold;
          }
          &.title2 {
            font-size: 24px;
            color: #000000;
            font-weight: 400;
          }
          &.title3 {
            font-size: 24px;
            /*IFTRUE_default*/
            color: #1878f3;
            /*FITRUE_default*/
            /*IFTRUE_pay*/
            color: #1878f3;
            /*FITRUE_pay*/
            font-weight: 400;
          }
          &.title4 {
            font-size: 12px;
            margin-top: 10px;
          }
        }
      }

      &.reward {
        ::v-deep .el-button {
          border-radius: 0;
          border-top: none;
          border-right: none;
          border-bottom: none;
          color: #000000;
          &:active {
            border-color: #dcdfe6;
          }
        }
        .cashtext {
          font-size: 11px;
          color: #777777;
          width: 395px;
        }
        .phone {
          margin-top: 10px;
          font-size: 12px;
          .phonei {
            font-weight: bold;
            /*IFTRUE_default*/
            color: #1878f3;
            /*FITRUE_default*/
            /*IFTRUE_pay*/
            color: #1878f3;
            /*FITRUE_pay*/
            cursor: pointer;
          }
        }
        > label {
          width: 300px;
          height: 40px;
          border: 1px solid #dcdfe6;
          display: flex;
          margin-bottom: 10px;
          border-radius: 2px;

          > span {
            text-align: center;
            line-height: 38px;
            font-size: 16px;
            display: block;
            width: 30px;
          }

          > input {
            flex: 1;
            border: none;
            // color: #c0c4cc;
          }
          > .rinput {
            font-size: 16px;
            color: #000000;
          }
          > .ript {
            margin-left: 3px;
          }
        }
        > .default-amounts {
          max-width: 660px;
          display: flex;
          flex-wrap: wrap;

          > button {
            font-size: 16px;
            margin-top: 10px;
            margin-right: 10px;
            width: 100px;
            height: 50px;
            border: 1px solid #ededed;
            text-align: center;
            line-height: 50px;

            &.selected {
              border: 1px solid $color-blue-base;
            }
          }
        }
      }

      &.hide-avatar {
        span {
          margin-left: 10px;
        }
      }

      &.pay-way {
        > .pay-card {
          display: flex;
          align-items: center;
          height: 120px;
          width: 320px;
          padding: 10px;
          border: 1px solid #ededed;
          border-radius: 3px;

          &.selected {
            border: 1px solid $color-blue-base;

            .active-tip {
              color: #09bb07;
            }

            .active-svg-wx {
              fill: #09bb07 !important;
            }

            .active-svg-wallet {
              fill: $color-blue-base !important;
            }
          }

          > .qr-code {
            width: 100px;
            height: 100px;
          }

          > .detail {
            width: 100%;
            height: 100%;
            margin-left: 10px;
            font-size: 16px;
            display: flex;

            > .pay-title {
              flex: 1;

              > span {
                margin-left: 10px;
              }

              > .pay-tip {
                margin-top: 10px;
                color: $font-color-grey;

                > span,
                a {
                  line-height: 22px;
                  display: block;
                }
              }
            }

            > .pay-amount {
              width: 140px;
              overflow: auto;
              display: flex;
              align-items: center;
              justify-content: center;
              font-size: 30px;
            }
          }
        }
        @media screen and (max-width: 1005px) {
          .pay-card {
            width: 298px;
            .pay-tip {
              font-size: 15px;
            }
          }
        }
      }
    }

    > .amount {
      color: $font-color-grey;
    }
  }
  > .row2 {
    padding-bottom: 0px;
    margin-top: 10px;
  }
}

.bottom {
  height: 55px;
  display: inline-flex;
  justify-content: flex-end;
  align-items: center;
  padding: 0 20px;
  background: #f5f6f7;
  margin-top: 30px;
  .border {
    border-radius: 2px;
  }
  > span {
    margin-right: 20px;
  }
}
</style>
