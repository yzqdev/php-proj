<template>
  <div class="close-info">
    <div class="title">抱歉，论坛暂时关闭</div>
    <div class="text">管理员关闭了论坛，请稍后访问。</div>
    <div class="time">{{ close_tips || tips }}</div>
  </div>
</template>

<script>
export default {
  // layout: 'error_layout',
  name: 'Close',
  data() {
    return {
      close_tips: '',
      forums: ''
    };
  },
  computed: {
    tips() {
      return this.$store.state.forum.error ? this.$store.state.forum.error.detail : '';
    }
  },
  mounted() {
    this.getSiteInfo();
    this.$store.dispatch('jv/get', '/forum').then((res) => {
      this.forums = res;
      if (this.forums && this.forums.set_site && !this.forums.set_site.site_close) {
        window.location.replace('/');
      }
    });
  },
  methods: {
    async getSiteInfo() {
      const error = this.$store.state.forum.error;
      this.close_tips = error ? error.detail : '';
      // try {
      //   await this.$store.dispatch('site/getSiteInfo')
      // } catch (e) {
      //   const { response: { data: { errors }}} = e
      //   if (errors[0].code === 'site_closed') {
      //     this.close_tips = errors[0].detail[0]
      //   }
      // }
    }
  },
  head() {
    return {
      title: this.$t('site.siteClose')
    };
  }
};
</script>
<style lang='scss' scoped>
.close-info{
  padding: 62px 304px;
  .title{
    font-size: 26px;
    font-weight: bold;
    margin-bottom: 51px;
  }
  .text{
    font-size:18px;
    margin-bottom: 20px;
  }
  .time{
    color: #909399;
  }
}
</style>
