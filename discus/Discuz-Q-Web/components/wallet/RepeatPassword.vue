<template>
  <message
    :title="$t('profile.editpaypassword')"
    @close="$emit('close')"
  >
    <div class="container">
      <div class="block show-amount">
        <div class="title">{{ $t('modify.enterpaymentagin') }}</div>
        <div class="amount">
          <span>{{ $t('modify.repeatpasstip') }}</span>
        </div>
      </div>
      <div class="block input-password">
        <wallet-password-input
          ref="walletinput"
          :error="error"
          @password="password => $emit('password', password)"
        />
        <div
          v-if="error"
          class="amount"
        >{{ $t('modify.masstext') }}</div>
      </div>
    </div>
  </message>
</template>

<script>
export default {
  name: 'RepeatPassword',
  props: {
    error: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {

    };
  },
  methods: {
    empty() {
      this.$refs.walletinput.deleat();
    }
  }
};
</script>

<style lang="scss" scoped>
.container {
  height: 370px;
  > .block {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 40px;
    &.show-amount {
      > .title {
        font-size: 30px;
      }
      > .amount {
        height: 52px;
        line-height: 52px;
        position: relative;
        font-size: 14px;
        color: #777777;
        > .icon {
          line-height: 20px;
          position: absolute;
          top: 10px;
          left: -20px;
          font-size: 20px;
        }
      }
    }
    &.input-password {
      > .amount {
        font-size: 14px;
        color:#777777;
      }
    }
  }
}
</style>
