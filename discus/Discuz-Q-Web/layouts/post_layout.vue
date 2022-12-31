<template>
  <div class="global">
    <Header
      :key="this.$route.path"
    />
    <div class="app-container">
      <div class="app-cont">
        <Nuxt />
      </div>
    </div>
    <Footer />
    <BackTop />
  </div>
</template>

<script>
export default {
  data() {
    return {
    };
  },
  computed: {
    forums() {
      return this.$store.state.site.info.attributes || {};
    }
  },
  // 设置head信息
  head() {
    return {
      link: [{
        rel: 'icon',
        type: 'image/x-icon',
        href: this.forums
          && this.forums.set_site
          && this.forums.set_site.site_favicon
      }]
    };
  }
};
</script>
<style lang="scss" scoped>
  .app-container {
    /*IFTRUE_default*/
    width:1005px;
    /*FITRUE_default*/
    /*IFTRUE_pay*/
    width:1200px;
    /*FITRUE_pay*/
    background: #fff;
    margin: 28px auto 0;
    @media screen and ( max-width: 1005px ) {
      width: 100%;
      min-width: 768px;
      padding: 0 14px;
    }
    .app-cont {
      width: 100%;
      min-height: calc(100vh - 65px - 80px - 15px);
      display: flex;
      justify-content: center;
      box-shadow: 0 3px 3px rgba(0, 0, 0, 0.03);
      border-radius: 5px;
    }
  }

</style>
