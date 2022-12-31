<template>
  <div class="mydrafts-container">
    <el-tabs v-model="activeName">
      <el-tab-pane :label="$t('topic.allGoods', {total})" name="all" />
    </el-tabs>
    <div class="post-list">
      <template v-for="(item, index) in goodsList">
        /*IFTRUE_default*/
        <post-item :key="index" :item="item" />
        /*FITRUE_default*/
        /*IFTRUE_pay*/
        <post-item-pay :key="index" :item="item" />
        /*FITRUE_pay*/
      </template>
      <list-load-more
        :loading="loading"
        :has-more="hasMore"
        :page-num="pageNum"
        :length="goodsList.length"
        @loadMore="loadMore"
      />
    </div>
  </div>
</template>

<script>
import head from '@/mixin/head';
import handleError from '@/mixin/handleError';
export default {
  name: 'Drafts',
  layout: 'center_layout',
  mixins: [head, handleError],
  data() {
    return {
      title: this.$t('profile.mygoods'),
      activeName: 'all',
      goodsList: [],
      total: 0,
      pageNum: 1, // 请求页码
      pageSize: 10, // 每页条数
      loading: false,
      hasMore: false
    };
  },
  computed: {
    userInfo() {
      return this.$store.state.user.info.attributes || {};
    },
    userId() {
      return this.$store.getters['session/get']('userId');
    }
  },
  mounted() {
    this.getgoodsList();
  },
  methods: {
    getgoodsList() {
      this.loading = true;
      const params = {
        'page[number]': this.pageNum,
        'page[size]': this.pageSize
      };
      this.$store.dispatch('jv/get', ['threads/paid', { params }])
        .then((data) => {
          this.total = data._jv.json.meta.threadCount;
          this.hasMore = data.length === this.pageSize;
          if (this.pageNum === 1) {
            this.goodsList = data;
          } else {
            this.goodsList = [...this.goodsList, ...data];
          }
          this.pageNum += 1;
          if (data._jv) {
            this.hasMore = this.goodsList.length < data._jv.json.meta.threadCount;
          }
        }, (e) => {
          this.handleError(e);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    loadMore() {
      this.getgoodsList();
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/css/variable/color.scss';
@import "@/assets/css/variable/color_pay.scss";
.mydrafts-container{
  margin: 0 30px;
  .el-tabs ::v-deep{
    .el-tabs__header{
      margin: 0;
    }
    .el-tabs__active-bar, .el-tabs__nav-wrap::after {
      height: 0;
    }
    .el-tabs__nav-wrap{
      border-bottom: 1px solid $border-color-base;
      padding-bottom:5px;
      margin: 0 30px 2px;
    }
    .el-tabs__item{
      font-size: 16px;
      color: #B5B5B5;
      &.is-active{
        color: $color-black;
        font-size: 18px;
        font-weight:bold;
      }
    }
  }
  .post-list{
    .delete-container{
      color: $font-color-grey;
      width: 100px;
      text-align: right;
      .delete{
        cursor: pointer;
        .svg-icon-delete{
          margin-right: 6px;
          font-size: 14px;
        }
        &:hover{
          color: $color-blue-deep;
        }
      }
    }
  }
}
</style>
