<template>
  <div>
    <div class="adv-container">
      <div class="qrcode">
        <div class="kuq-baifen-50 kuq-juzhong">
          <img :src="'@/assets/logo.png' ? forums && forums.set_site && forums.set_site.site_gongzhonghao_logo : ''"/>
          <br>
          微信公众号
        </div>
        <div class="kuq-baifen-50 kuq-juzhong">
          <img :src="'@/assets/logo.png' ? forums && forums.set_site && forums.set_site.site_xiaochengxu_logo : ''"/>
          <br>
          微信小程序
        </div>
        <div class="kuq-clear"></div>
      </div>
    </div>
  </div>
</template>
<script>

export default {
  name: 'Advertising',
  data() {
    return {
      siteName: ''
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
        if (val && val.set_site) {
          this.siteName = val.set_site.site_name
            ? val.set_site.site_name
            : 'Discuz! Q';
        }
      },
      deep: true
    }
  },
  mounted() {
    this.siteName
      = this.forums && this.forums.set_site && this.forums.set_site.site_name
        ? this.forums.set_site.site_name
        : 'Discuz! Q';

  },

  methods: {

  }
};
</script>
<style lang="scss" scoped>
  @import "@/assets/css/pay_scss/kuq.scss";
.adv-container{
  padding: 15px;
  /*IFTRUE_pay*/
  padding: 20px;
  /*FITRUE_pay*/
  /*display: flex;*/
  align-items: center;
  background: #fff;
  border-radius: 5px;
  box-shadow: 0 3px 3px rgba(0, 0, 0, 0.03);
  margin-bottom: 16px;
  /*IFTRUE_default*/
  @media screen and (max-width: 1005px) {
    font-size: 12px;
  }
  /*FITRUE_default*/
  .qrcode {
    width: 100%;
    /*height: 70px;*/
    flex: 0 0 70px;
    margin-right: 10px;
    display: flex;

  }
  .qrcode img {
    width: 100%;
  }
  .adv-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 6px;
    @media screen and (max-width: 1005px) {
      font-size: 16px;
      /*IFTRUE_pay*/
      font-size: 14px;
      /*FITRUE_pay*/
    }
  }
  .adv-logo2{
    float: left;
    width: 29px;
    height: 31px;
    margin-right: 6px;
    vertical-align: top;
    @media screen and ( max-width: 1005px ) {
      width: 25px;
      height: 26px;
    }
  }
  .adv-title2{
    font-size: 16px;
    color: #666;
    line-height: 32px;
    word-break: break-all;
    @media screen and ( max-width: 1005px ) {
      font-size:12px;
      line-height: 28px;
    }
  }
}
</style>
