<template>
  <div class="footer">
    <span>
      Powered By &nbsp;
      <a class="bold" href="https://discuz.com" target="_blank">Discuz! Q</a>
      &nbsp;© v3.0.2-{{ year }}1022 &nbsp;&nbsp;</span>
    <a
      v-if="forums && forums.set_site && forums.set_site.site_record"
      class="site-record"
      href="https://beian.miit.gov.cn/"
      target="_blank"
    >{{ forums.set_site.site_record }}</a>
    <a
      v-if="forums && forums.set_site && forums.set_site.site_record_code"
      :href="
        `http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=${forums &&
          forums.set_site &&
          forums.set_site.site_record_code.replace(/[^0-9]/gi, '')}`
      "
      target="_blank"
    >{{ forums.set_site.site_record_code }}</a>
  </div>
</template>
<script>
export default {
  name: 'Footer',
  data() {
    return {
      year: '2019'
    };
  },
  computed: {
    forums() {
      return this.$store.state.site.info.attributes || {};
    }
  },
  mounted() {
    const date = (process.client && window.currentTime) || new Date();
    this.year = date.getFullYear();
  }
};
</script>
<style lang="scss" scoped>
.footer {
  padding: 30px 0 40px;
  text-align: center;
  color: #8590a6;
  font-size: 12px;

  span .bold {
    font-weight: bold;
  }
  .site-record {
    margin-right: 10px;
  }
  a:hover {
    color: #006cd1;
  }
}
</style>
