<template>
  <div class="index-top-conttainer">
    <div class="index-filter">
      <template v-for="(item, index) in filterQuery">
        <div
          v-if="item.isShow"
          :key="index"
          class="filter-btn"
          :class="{ 'active': query.filter === item.value }"
          @click="onClickFilter(item.value)"
        >
          {{ item.label }}
        </div>
      </template>

      <el-dropdown class="filter-dropdown" placement="bottom" @command="handleCommandSort">
        <span class="el-dropdown-link" :class="{'active': query.filterSort !== ''}">
          {{ $t('core.sort') }}<i class="el-icon-arrow-down el-icon--right" />
        </span>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item
            v-for="(item,index) in filterSort"
            :key="index"
            :command="item.value"
            :class="{'actiaskve': item.value === query.filterSort}"
          >
            {{ item.label }}
          </el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
    </div>
    <el-button type="danger"  round>
      <nuxt-link to="/thread/postquanpay?type=10">
        申请圈子 +
      </nuxt-link>
    </el-button>

  </div>
</template>
<script>
export default {
  name: 'IndexaskFilter',
  props: {
    showSort: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      // 筛选按钮
      filterQuery: [{
        label: '推荐', // 推荐智能排序
        value: 'isSort',
        isShow: false // 根据组件传值决定其显示性
      }, {
        label: this.$t('home.all'), // 所有
        value: '',
        isShow: true
      }, {
        label: this.$t('home.essence'), // 精华
        value: 'isEssence',
        isShow: true
      }, {
        label: this.$t('home.followed'), // 已关注
        value: 'followed',
        isShow: false
      }],
      // 类型
      filterType: [{
        label: this.$t('home.noLimit'), // 不限
        value: ''
      }, {
        label: this.$t('home.text'), // 文本
        value: 0
      }, {
        label: this.$t('home.invitation'), // 帖子
        value: 1
      }, {
        label: this.$t('home.video'), // 视频
        value: 2
      }, {
        label: this.$t('home.picture'), // 图片
        value: 3
      }, {
        label: this.$t('home.voice'), // 语音
        value: 4
      }, {
        label: this.$t('home.question'), // 问答
        value: 5
      }, {
        label: this.$t('home.product'), // 商品
        value: 6
      }],
      // 排序
      filterSort: [{
        label: this.$t('home.noLimit'), // 不限
        value: ''
      }, {
        label: this.$t('home.sortCreatedAt'), // 发布时间
        value: '-createdAt'
      }, {
        label: this.$t('home.sortUpdatedAt'), // 更新时间
        value: '-updatedAt'
      }],
      query: {
        filter: '',
        filterType: '',
        filterSort: ''
      }
    };
  },
  computed: {
    userId() {
      return this.$store.getters['session/get']('userId');
    }
  },
  watch: {
    userId(val) {
      // 登录才显示“已关注”
      if (val > 0) {
        this.filterQuery[3].isShow = true;
      } else {
        this.filterQuery[3].isShow = false;
      }
    },
    showSort(val) {
      if (val) {
        this.filterQuery[0].isShow = true;
        this.query.filter = 'isSort';
      } else {
        this.filterQuery[0].isShow = false;
        this.query.filter = '';
      }
    }
  },
  mounted() {
    if (this.userId > 0) {
      this.filterQuery[3].isShow = true;
    } else {
      this.filterQuery[3].isShow = false;
    }
    // 是否显示智能排序
    if (this.showSort) {
      this.filterQuery[0].isShow = true;
      this.query.filter = 'isSort';
    } else {
      this.filterQuery[0].isShow = false;
      this.query.filter = '';
    }
  },
  methods: {
    // 点击筛选，传值给父组件
    onClickFilter(val) {
      if (val === 'followed' && (!this.userId || +this.userId === 0)) {
        if (process.client) {
          this.$message.error(this.$t('core.not_authenticated'));
        }
        return;
      }
      this.query.filter = val;
      this.$emit('onChangeFilter', val);
    },
    // 点击类型筛选，传值给父组件
    handleCommandType(command) {
      this.query.filterType = command;
      this.$emit('onChangeType', command);
    },
    // 点击排序，传值给父组件
    handleCommandSort(command) {
      this.query.filterSort = command;
      this.$emit('onChangeSort', command);
    }
  }
};
</script>
<style lang="scss" scoped>
@import '@/assets/css/variable/color.scss';
/*IFTRUE_pay*/
@import '@/assets/css/variable/color_pay.scss';
/*FITRUE_pay*/
.index-top-conttainer{
  display: flex;
  justify-content: space-between;
  align-items: center;
  /*IFTRUE_default*/
  padding: 20px 20px 10px;
  /*FITRUE_default*/
  /*IFTRUE_pay*/
  padding: 20px 10px;
  border-bottom: 1px solid #f4f4f4;
  @media screen and ( max-width: 1005px ) {
    padding: 20px 0;
  }
  /*FITRUE_pay*/
}
/*IFTRUE_default*/
.index-filter{
  display: flex;
  align-items: center;
  line-height: 1;
  .filter-btn{
    color:#8590A6;
    padding:10.5px 16px;
    margin-right: 15px;
    line-height: 1;
    cursor: pointer;
    &.active{
      color: $color-blue-base;
      background: #E5F2FF;
      transition: all 0.2s ease-in-out;
      border-radius: 2px;
      font-weight: bold;
    }
    &:hover{
      color: $color-blue-base;
    }
    @media screen and ( max-width: 1005px ) {
      margin-right: 0;
    }
  }
  .filter-dropdown{
    margin: 0 0 0 10px;
    padding:8px 14px;
    color: #8590A6;
    cursor: default;
    @media screen and ( max-width: 1005px ) {
      margin: 0;
    }
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
.index-filter{
  display: flex;
  align-items: center;
  line-height: 1;
  .filter-btn{
    position: relative;
    padding:10px 16px;
    margin-right: 15px;
    font-size: 16px;
    color:$color-333;
    line-height: 1;
    cursor: pointer;
    &.active{
      color: $color-blue-base;
      transition: all 0.2s ease-in-out;
      font-weight: bold;
      &::after{
        content: '';
        position: absolute;
        left: 50%;
        bottom: -4px;
        margin-left: -16px;
        width: 32px;
        height: 4px;
        background-color: $color-blue-base;
        border-radius: 2px;
      }
    }
    &:hover{
      color: $color-blue-base;
      background: $color-background-hover;
    }
    @media screen and ( max-width: 1005px ) {
      padding: 8px 5px;
      margin-right: 0;
      font-size: 14px;
    }
  }
  .filter-dropdown{
    margin: 0 0 0 10px;
    padding:8px 14px;
    font-size: 16px;
    color: $color-333;
    cursor: default;
    @media screen and ( max-width: 1005px ) {
      margin: 0;
      padding:8px 5px;
      font-size: 14px;
    }
    &:hover{
      background: $color-background-hover;
    }
  }
}
/*FITRUE_pay*/
.el-dropdown-link{
  outline: none;
  &.active, &:hover{
    color: $color-blue-base;
  }
}

.el-dropdown-menu{
  padding: 5px 10px;
  .el-dropdown-menu__item{
    border-top: 1px solid rgba(237,237,237,0.6);
    min-width: 100px;
    padding: 0;
    color: #6D6D6D;
    text-align: center;
    &:first-child{
      border-top: 0;
    }
  }
  .el-dropdown-menu__item:not(.is-disabled):hover,.el-dropdown-menu__item:not(.is-disabled).active {
    background-color: transparent;
    color:  $color-blue-base;
  }
}
</style>
