<template>
  <div class="mydrafts-container">
    <el-tabs v-model="activeName">
      <el-tab-pane :label="$t('profile.vip', {total})" name="all" />
    </el-tabs>
    <div class="post-list">
      <el-row :gutter="20">
        <el-col :span="6" v-for="(item, index) in groupsData"
           :key="index"
           @click="godetails(1, item._jv.id, index)"
        >
          <div class="kuq-beijing-hui kuq-juzhong kuq-yuanjiao-10 kuq-shang-10">
            <div class="kuq-font-16 kuq-henggao-40 kuq-beijing-lanse kuq-yuanjiao-shang-10 kuq-ziti-baise">
              <img :src="item.icon" alt="" class="kuq-img-15 kuq-shang-10">
              {{item.name}}
            </div>
            <div class="kuq-font-16 kuq-henggao-40">{{item.name}}</div>
            <div class="kuq-font-16 kuq-henggao-40 kuq-beijing-baihui">{{item.isPaid ? '付费':'免费'}}</div>
            <div class="kuq-font-16 kuq-henggao-40">有效 {{item.days}} 天</div>
            <div class="kuq-font-16 kuq-beijing-baihui1">
              <img :src="'https://api.pwmqr.com/qrcode/create/?url=https://'+url+'/pages/modify/rightdetails?sice=1'+'%26'+'groups='+item._jv.id+'%26'+'index='+index"
              alt=""
              class="kuq-img-100 kuq-shang-10 ">
              <div class="kuq-font-14">微信扫描</div>
            </div>
            <div class="kuq-font-16 kuq-henggao-40 kuq-beijing-baihui">简介</div>
            <div class="kuq-font-16 kuq-gao-90 kuq-zuoduiqi kuq-nei-10">{{item.content}}</div>
            <!--div class="kuq-font-16 kuq-henggao-40 kuq-beijing-baihui">查看</div-->

          </div>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script>
import head from '@/mixin/head';
import handleError from '@/mixin/handleError';
import apiRequest from '../../api/new-api';
const QRCode = process.client && require('qrcodejs2');
export default {
  name: 'Drafts',
  layout: 'center_layout',
  mixins: [head, handleError],
  data() {
    return {
      qrcode: null,
      title: this.$t('profile.vip'),
      activeName: 'all',
      groupsData: [], // 会员列表
      total: 0,
      pageNum: 1, // 请求页码
      pageSize: 10, // 每页条数
      loading: false,
      hasMore: false,

    };
  },
  computed: {
    userInfo() {
      return this.$store.state.user.info.attributes || {};
    },
    userId() {
      return this.$store.getters['session/get']('userId');
    },
    url() {
      return window.location.host;
    },
    imgurl(index, group, indexs) {
      return 'https://api.pwmqr.com/qrcode/create/?url=url/pages/modify/rightdetails?sice=${index}&groups=${group}&index=${indexs}'
    }
  },
  mounted() {
    this.getGroups();
  },
  destroyed() {
    this.qrcode = null;
  },
  methods: {
    // 置顶主题
    getGroups() {
      this.loading = true;
      // this.groupsData = [];
      const params = {
        'filter[isPaid]': 1,
      };
      this.$store.dispatch('jv/get', [`groups`, { params }]).then((res) => {
        this.groupsData = res;
      });
    },
    godetails(index, group, indexs) {
      uni.navigateTo({
        url: `/pages/modify/rightdetails?sice=${index}&groups=${group}&index=${indexs}`,
      });
    },
    imgtu(index, group, indexs) {
      return 'https://api.pwmqr.com/qrcode/create/?url=url/pages/modify/rightdetails?sice=${index}&groups=${group}&index=${indexs}'
    }

  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/css/variable/color.scss';
@import "@/assets/css/variable/color_pay.scss";
@import "@/assets/css/pay_scss/kuq.scss";
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
.qrcode {
  width: 70px;
  height: 70px;
  flex: 0 0 70px;
  margin-right: 10px;
}
</style>
