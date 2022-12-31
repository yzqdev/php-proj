<template>
  <div class="info">
    <div class="info-icon">
      <svg-icon
        v-if="errorCode === 'validate_reject'"
        type="userLocking"
        class="icon-userLocking"
      />
      <svg-icon
        v-else
        type="userWait"
        class="icon-userWait"
      />
    </div>
    <h2 class="info-title">
      {{ errorCode === 'validate_reject' ? $t('core.validateReject') : $t('core.registerValidate') }}
    </h2>
    <div class="payinfo">
      <p v-if="errorCode === 'validate_reject'" class="payinfo-title">
        {{ username && `${username}` }} ，您好。您的账户未能通过审核 原因：{{ errorReson && `${errorReson}` }}。
      </p>
      <p v-else class="payinfo-title">
        {{ username && `${username}` }}，您好。您已注册，请耐心等待审核。
      </p>
    </div>
  </div>
</template>

<script>
import head from '@/mixin/head';
export default {
  name: 'Warning',
  mixins: [head],
  data() {
    return {
      username: '',
      errorReson: '',
      errorCode: ''
    };
  },
  mounted() {
    const { username, reason, errorCode } = this.$store.state.session.auditInfo;
    if (username) {
      this.username = username;
    }
    if (reason) {
      this.errorReson = reason;
    }
    if (errorCode) {
      this.errorCode = errorCode;
    }
  },
  methods: {
    // home() {
    //   this.$router.push('/');
    // }
  }
};
</script>
<style lang='scss' scoped>
.info {
  display: flex;
  width: 276px;
  margin-top: 103px;
  align-items: center;
  flex-direction: column;
  .info-icon {
    font-size: 75px;
    .icon-userLocking{
      color: #DDDDDD;
      cursor: pointer;
    }
    .icon-userWait {
      color: #008DF0;
      cursor: pointer;
    }
  }
  .info-title {
    margin: 29px 0 20px;
    height: 45px;
    font-size: 34px;
    font-weight: bold;
    color: #333333;
  }
  .payinfo {
    color: #AAAAAA;
    .payinfo-title {
      font-size: 16px;
      line-height: 21px;
      font-weight: normal;
    }
  }
  .h-button1 {
    width: 250px;
    margin-top: 17px;
    height: 45px;
    color: #8590A6;
    background: #ffffff !important;
    border: 1px solid #D0D4DC !important;
    font-size: 16px;
    border-radius: 2px;
  }
}
</style>
