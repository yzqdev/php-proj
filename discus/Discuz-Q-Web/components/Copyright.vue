<template>
  <div class="copyright">
    <div class="info">
      <span>Powered By</span>
      <a class="site" href="https://discuz.com" target="_blank">Discuz! Q</a>
      <span class="block">© {{ forums && forums.set_site && forums.set_site.version }}</span>
    </div>
    <div
      v-if="
        forums &&
          forums.set_site &&
          (forums.set_site.site_record || forums.set_site.site_record_code)
      "
      class="id"
    >
      <div class="site-record-code">
        <a href="https://beian.miit.gov.cn/" target="_blank">{{
          forums.set_site ? forums.set_site.site_record : ""
        }}</a>
      </div>
      <div
        v-if="forums && forums.set_site && forums.set_site.site_record_code"
        class="site-record-code"
      >
        <a
          :href="`http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=${code}`"
          target="_blank"
        >
        <img src="@/assets/pay_imgs/beian.png">{{ forums && forums.set_site && forums.set_site.site_record_code }}</a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Copyright',
  data() {
    return {
      year: '2019'
    };
  },
  computed: {
    forums() {
      return this.$store.state.site.info.attributes || {};
    },
    code() {
      return this.$store.state.site.info.attributes.set_site.site_record_code.replace(/[^0-9]+/g, '') || '';
    }
  },
  mounted() {
    const date = (process.client && window.currentTime) || new Date();
    this.year = date.getFullYear();
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
/*FITRUE_pay*/
.copyright {
  margin-top: 0;
  color: $font-color-grey;
  line-height: 20px;
  padding: 20px 20px 20px 10px;
  @media screen and (max-width: 1005px) {
    padding: 14px;
    font-size: 12px;
    .block {
      display: block;
    }
  }
  > .info {
    width: 100%;
    display: flex;
    white-space: nowrap;
    font-size: 12px;
    > span {
      margin-right: 10px;
      &:last-child {
        margin-right: 0;
      }
    }
    > .site {
      margin-right: 5px;
      font-weight: bold;
      white-space: nowrap;
    }
  }

  > .id {
    border-top: 1px solid $border-color-base;
    padding-top: 10px;
    margin-top: 13px;
    line-height: 25px;
    > span {
      &:last-child {
        margin-left: 20px;
      }
    }
  }
  .site-record-code {
    a:hover {
      color: $color-blue-deep;
    }
  }
}
</style>
