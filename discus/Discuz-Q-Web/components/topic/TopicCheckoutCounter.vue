<template>
  <message-box :title="$t('pay.checkoutCounter')" @close="$emit('close')">
    <div class="top">
      <div class="row">
        <div class="head">{{ $t('pay.payProduct') }}</div>
        <!-- 红包帖 -->
        <div
          v-if="rewardOrPay === 'redpacket'"
          class="body product-information"
        >
          <span class="title">支付红包</span>
          <span>支付人：{{ (user.username || '') }}</span>
        </div>
        <!--悬赏问答帖-->
        <div
          v-else-if="threadType === 5 && rewardOrPay === 'offerReward'"
          class="body product-information"
        >
          <span class="title">支付悬赏</span>
          <span>支付人：{{ (user.username || '') }}</span>
        </div>
        <!-- 指定问答 -->
        <div
          v-else-if="threadType === 5 && rewardOrPay === 'reward'"
          class="body product-information"
        >
          <span class="title">
            {{ askOrWatchAnswer === 'ask' ? $t('post.askHim') : $t('topic.watchAnswer') }}
          </span>
          <span>{{ $t('post.answerer') + beAskedUser.username }}</span>
        </div>
        <!-- 续费 -->
        <div
          v-else-if="threadType === 10 && rewardOrPay === 'renewal'"
          class="body product-information"
        >
          <span class="title">站点续费</span>
        </div>
        <!-- 其它支付 -->
        <div
          v-else
          class="body product-information"
        >
          <span v-if="rewardOrPay === 'reward'" class="title">
            {{ $t('pay.supportAuthor') + $t('pay.keepWriting') }}
          </span>
          <span v-else class="title">
            {{ $t('pay.supportAuthor') + $t(`pay.${text[threadType]}`) + $t('pay.getRight') }}
          </span>
          <span>{{ $t('topic.author') + ': ' + (user.username || '') }}</span>
          <span
            class="thread-content"
            v-html="$xss($t('topic.content') + ': ' + content)"
          />
        </div>
        <div
          v-if="rewardOrPay === 'pay'"
          class="amount"
        >￥{{ showAmount }}</div>
      </div>
      <div v-if="rewardOrPay === 'renewal'" class="row">
        <div class="head reward">
          {{ $t('site.pay') + $t('pay.sumOfMoney') }}
        </div>
        <div class="body reward">
          <label>
            <span>￥</span>
            <input
              :value="lowestPrice"
              :disabled="lowestPrice !== 0"
              maxlength="7"
              type="text"
            >
          </label>
        </div>
      </div>
      <div v-if="rewardOrPay === 'reward'" class="row">
        <div class="head reward">
          {{ $t('topic.reward') + $t('pay.sumOfMoney') }}
        </div>
        <div class="body reward">
          <label>
            <span>￥</span>
            <input
              :placeholder="$t('pay.inputRewardAmount')"
              :value="rewardAmount"
              maxlength="7"
              type="text"
              @input="formatAmount"
            >
          </label>
          <div class="default-amounts">
            <button
              v-for="(item, index) in defaultAmounts"
              :key="index"
              :class="{selected: item === rewardAmount}"
              @click="rewardAmount = item"
            >
              ￥{{ item }}
            </button>
          </div>
        </div>
      </div>
      <div v-if="showAnonymous" class="row">
        <div class="head">{{ $t('pay.hideAvatar') }}</div>
        <div class="body hide-avatar" @click="hideAvatar = !hideAvatar">
          <svg-icon v-if="hideAvatar" style="font-size: 18px" type="checked" />
          <svg-icon v-else style="font-size: 18px;" type="unchecked" />
          <span>{{ $t('pay.hideMyAvatar') }}</span>
        </div>
      </div>
      <div class="row">
        <div class="head">{{ $t('pay.payType') }}</div>
        <div v-if="showWechatPay" class="body pay-way">
          <div
            class="pay-card"
            :class="{'pay-card': true, 'selected': payWay === 'wxPay'}"
            @click="payWay = 'wxPay'"
          >
            <div class="detail">
              <div class="pay-title">
                <svg-icon
                  class="active-svg-wx"
                  style="fill: #8590A6;font-size: 20px"
                  type="wechat"
                />
                <span>{{ $t('pay.wxPay') }}</span>
                <div class="pay-tip">
                  <div>
                    <span>{{ $t('pay.wxPayTipUse') }}</span>
                    <span class="active-tip" style="font-weight: bold">
                      {{ $t('pay.wxPayTipScan') }}
                    </span>
                  </div>
                  <span>{{ $t('pay.wxPayTipPay') }}</span>
                </div>
              </div>
              <div class="pay-amount">￥{{ showAmount }}</div>
            </div>
          </div>
        </div>
        <div class="body pay-way">
          <div
            v-loading="loading"
            class="pay-card"
            :class="{'pay-card': true, 'selected': payWay === 'walletPay'}"
            @click="payWay = 'walletPay'"
          >
            <div class="detail">
              <div class="pay-title">
                <svg-icon
                  class="active-svg-wallet"
                  style="fill: #8590a6;font-size: 20px"
                  type="wallet"
                />
                <span>{{ $t('pay.walletPay') }}</span>
                <div class="pay-tip">
                  <div v-if="enoughBalance">
                    <nuxt-link
                      v-if="!userWallet.canWalletPay"
                      to="/my/wallet"
                      class="not-enough-balance"
                    >
                      {{ $t('pay.needToResetPassword') }}
                    </nuxt-link>
                    <div>{{ $t('pay.walletBalance') }}</div>
                  </div>
                  <div v-else>
                    <nuxt-link
                      v-if="!userWallet.canWalletPay"
                      to="/my/wallet"
                      class="not-enough-balance"
                    >
                      {{ $t('pay.needToResetPassword') }}
                    </nuxt-link>
                    <span v-else class="not-enough-balance">{{ $t('pay.walletBalanceNo') }}</span>
                  </div>
                  <div>
                    <span>￥ {{ userWallet.walletBalance }}</span>
                  </div>
                </div>
              </div>
              <div class="pay-amount">￥{{ showAmount }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="bottom">
      <span v-if="rewardOrPay !== 'redpacket' && rewardOrPay !== 'offerReward' && rewardOrPay !== 'renewal'">￥
        {{
          showAmount +
            $t("pay.rmb") +
            $t("pay.payTo") +
            "，" +
            (beAskedUser.username || user.username)
        }}
        {{
          payWay === 'wxPay' ? $t('pay.ofAccount') : $t('pay.ofWalletAccount')
        }}
      </span>
      <el-button
        v-show="payWay === 'wxPay'"
        type="primary"
        class="checkout-button"
        @click="handlePay"
      >
        {{ $t('pay.scanPay') }}
      </el-button>
      <el-button
        v-show="payWay === 'walletPay'"
        :disabled="!enoughBalance || !userWallet.canWalletPay"
        type="primary"
        class="checkout-button"
        style="margin: 0"
        @click="handlePay"
      >
        {{ $t('pay.surePay') }}
      </el-button>
    </div>
  </message-box>
</template>

<script>
import formatAmount from '@/utils/formatAmount';
export default {
  name: 'TopicCheckoutCounter',
  props: {
    redpacket: {
      type: Object,
      default: () => {}
    },
    offerMoney: {
      type: [String, Number],
      default: 0
    },
    threadType: {
      type: Number,
      default: 0
    },
    user: {
      type: Object,
      default: () => {}
    },
    amount: {
      type: [String, Number],
      default: ''
    },
    content: {
      type: String,
      default: ''
    },
    rewardOrPay: {
      type: String,
      default: ''
    },
    beAskedUser: {
      type: Object,
      default: () => {}
    },
    showAnonymous: {
      type: Boolean,
      default: true
    },
    askOrWatchAnswer: {
      type: String,
      default: 'ask'
    },
    showWechatPay: {
      type: Boolean,
      default: true
    },
    lowestPrice: {
      type: [String, Number],
      default: ''
    }
  },
  data() {
    return {
      hideAvatar: false,
      text: [
        'getRemainingContent',
        'getRemainingContent',
        'getVideo',
        'getPicture',
        'getAudio',
        'getRemainingContent'
      ],
      payWay: 'wxPay',
      rewardAmount: '',
      defaultAmounts: ['1', '2', '5', '10', '20', '50', '88', '128'],
      loading: false
    };
  },
  computed: {
    showAmount() {
      if (this.rewardOrPay === 'redpacket') {
        return this.redpacket.rule === 1
          ? this.formatToFixed(this.redpacket.money)
          : this.formatToFixed(this.redpacket.money * this.redpacket.number);
      }
      if (this.rewardOrPay === 'offerReward') {
        return this.formatToFixed(this.offerMoney);
      }
      if (this.rewardOrPay === 'reward') {
        return this.formatToFixed(this.rewardAmount);
      }
      if (this.rewardOrPay === 'renewal') {
        return this.formatToFixed(this.lowestPrice);
      }
      return this.amount;
    },
    userWallet() {
      const {
        attributes: { walletBalance, canWalletPay }
      } = this.$store.state.user.info;
      return { walletBalance, canWalletPay };
    },
    enoughBalance() {
      if (this.rewardOrPay === 'reward') {
        return (
          parseFloat(this.rewardAmount || '0')
          <= parseFloat(this.userWallet.walletBalance)
        );
      } else if (this.rewardOrPay === 'redpacket' || this.rewardOrPay === 'offerReward') {
        return (this.showAmount <= parseFloat(this.userWallet.walletBalance));
      } else {
        return (
          parseFloat(this.amount) <= parseFloat(this.userWallet.walletBalance)
        );
      }
    }
  },
  watch: {
    showWechatPay: {
      handler(val) {
        this.payWay = val ? 'wxPay' : 'walletPay';
      },
      immediate: true
    }
  },
  created() {
    this.loading = true;
    const userId = this.$store.state.user.info.id;
    if (!userId) {
      this.loading = false;
      return;
    }
    this.$store.dispatch('user/getUserInfo', userId).then(
      () => {
        this.$store.commit('session/SET_USER_ID', userId);
        this.loading = false;
      },
      e => this.$store.commit('user/SET_USER_INFO', {})
    );
  },
  methods: {
    handlePay() {
      // if (Number(this.rewardAmount) < Number(this.lowestPrice)) {
      //   return this.$message.warning(`打赏金额不能低于${this.lowestPrice}元`);
      // }
      if (this.rewardOrPay === 'renewal') {
        this.rewardAmount = this.lowestPrice;
      }
      if (this.rewardOrPay === 'redpacket' || this.rewardOrPay === 'offerReward') {
        this.rewardAmount = +this.showAmount;
      } else {
        this.rewardAmount = +this.formatToFixed(this.rewardAmount);
      }
      if (this.payWay === 'walletPay' && +this.userWallet.walletBalance < this.rewardAmount) {
        return this.$message.warning('钱包余额不足');
      }
      this.$emit('paying', {
        payWay: this.payWay,
        hideAvatar: this.hideAvatar,
        rewardAmount: this.rewardAmount
      });
    },
    formatAmount(e) {
      e.target.value = formatAmount(e.target.value);
      // if (e.target.value < this.lowestPrice) {
      //   this.$message.warning(`打赏金额不能低于${this.lowestPrice}元`);
      // }
      this.rewardAmount = e.target.value;
    },
    formatToFixed(amount) {
      return (parseFloat(amount || '0') + 0.000001).toFixed(2);
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_pay*/
@import '@/assets/css/variable/color_pay.scss';
/*FITRUE_pay*/
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
      width: 65px;
      color: #000000;

      &.reward {
        line-height: 50px;
      }
    }

    > .body {
      flex: 1;
      margin-left: 15px;
      &.product-information {
        span {
          word-break: break-all;
          display: block;
          line-height: 22.5px;
          color: $font-color-grey;

          &.title {
            margin-bottom: 8px;
            color: $color-blue-base;
          }
        }
        ::v-deep.thread-content {
          p {
            display: inline-block;
          }
          img {
            height: 22px;
            margin: 0;
            vertical-align: top;
          }
        }
      }

      &.reward {
        > label {
          width: 430px;
          height: 50px;
          border: 2px solid #ededed;
          display: flex;

          > span {
            text-align: center;
            line-height: 50px;
            font-size: 20px;
            display: block;
            width: 40px;
          }

          > input {
            line-height: 15px;
            flex: 1;
            font-size: 18px;
            border: none;
            color: #c0c4cc;
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
              border: 2px solid $color-blue-base;
            }
          }
        }
      }

      &.hide-avatar {
        .svg-icon {
          cursor: pointer;
        }
        span {
          cursor: pointer;
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
            border: 2px solid $color-blue-base;

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
                font-size: 14px;
                margin-top: 10px;
                color: $font-color-grey;

                .not-enough-balance {
                  color: red;
                }
                .recharge {
                  color: $color-blue-base;
                }
                span,
                a {
                  display: inline-block;
                  line-height: 22px;
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
}

.bottom {
  height: 55px;
  display: inline-flex;
  justify-content: flex-end;
  align-items: center;
  padding: 0 20px;
  background: #f5f6f7;
  margin-top: 30px;

  > span {
    font-size: 14px;
    margin-right: 20px;
  }
}
/*IFTRUE_pay*/
.checkout-button{
  border: 1px solid $color-red-pay !important;
  color: $color-red-pay ;
  background: $color-white !important;
  font-size: 16px;
  transition: all .5s;
  &:hover{
    border: 1px solid $color-red-pay !important;
    color: $color-white;
    background: $color-red-pay !important;
  }
}
/*FITRUE_pay*/
</style>
