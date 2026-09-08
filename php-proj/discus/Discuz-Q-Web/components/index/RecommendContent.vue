<template>
  <div
    v-loading="loading"
    class="recommend-content-container"
  >
    <div class="recommend-content-title">{{ $t('home.recommentContent') }}</div>
    <div v-show="!loading" class="recommend-content-list">
      <content-item v-for="(item, index) in contentList" :key="index" :item="item" />
      <div v-if="contentList.length === 0" class="no-more">{{ $t('discuzq.list.noData') }}</div>
    </div>
    <div class="refresh" @click="refresh">
      <svg-icon type="refresh" class="icon" />
      {{ $t('home.refresh') }}
    </div>
  </div>
</template>

<script>
import handleError from '@/mixin/handleError';
export default {
  name: 'RecommendContent',
  mixins: [handleError],
  props: {
    // 用户列表
    list: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      contentList: [],
      loading: false
    };
  },
  watch: {
    list: {
      handler(val) {
        this.contentList = val;
        // console.log(this.contentList);
      },
      deep: true
    }
  },
  mounted() {
    // 父组件传值是否成功，未成功则组件内重新请求数据
    if (this.contentList.length === 0) {
      this.getList();
    }
  },
  methods: {
    // 获取推荐内容列表
    getList() {
      this.contentList = [];
      this.loading = true;
      this.$store.dispatch('jv/get', ['threads/recommend', {}]).then((res) => {
        const data = res.slice(0, 5);
        this.contentList = data;
      }, (e) => {
        this.handleError(e);
      })
        .finally(() => {
          this.loading = false;
        });
    },
    // 刷新
    refresh() {
      this.getList();
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/css/variable/color.scss';
/*IFTRUE_pay*/
@import '@/assets/css/variable/color_pay.scss';
/*FITRUE_pay*/
.recommend-content-container{
  padding: 0 20px;
  @media screen and ( max-width: 1005px ) {
    padding: 14px;
  }
  .recommend-content-title{
    padding: 24px 0;
    font-size: 20px;
    color: #333;
    border-bottom: 1px solid rgba(220,220,220,0.46);
  }
  .refresh{
    padding: 27px 0;
    font-size: 16px;
    color: #666666;
    text-align: center;
    line-height: 16px;
    cursor: pointer;
    &:hover{
      color:$color-blue-base;
    }
    .icon{
      margin-right: 5px;
      font-size: 20px;
    }
  }
}
</style>
