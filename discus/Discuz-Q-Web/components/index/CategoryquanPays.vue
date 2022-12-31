<template>
  <div v-if="categoryList && categoryList.length > 0" class="category-container">
    <div v-for="(item, index) in categoryList" :key="index">
      <!-- 分类父项 -->
      <!-- <div @click="onChange(item.id,item.id,item.search_ids)">{{item.id}}</div> -->
      <div 
        class="category-item"
        :class="{
          'active': selectId === item.id || selectId_parent === item.id,
          'loading': postLoading}
        "
        @click="onChange(item.id,item.id,item.search_ids)"
      >
        <i v-if="selectId_parent === item.id" class="el-icon-arrow-left arrow-icon" />
        <div class="title">{{ item.name }}</div>
        <div class="count">{{ item.thread_count }}</div>
      </div>
      <!-- 分类子项 -->
      <div v-if="item.hasChild && item.children.length > 0">
        <div 
          v-for="(c, indexs) in item.children"
          v-show="selectId_parent === item.id"
          :key="indexs"
          class="category-item category-item-sub"
          :class="{'active': selectId === c.id ,'loading': postLoading}"
          @click="onChange(c.id,item.id,c.search_ids)"
        >
          <div class="title">{{ c.name }}</div>
          <div class="count">{{ c.thread_count }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import handleError from '@/mixin/handleError';
import apiRequest from '../../api/new-api';
export default {
  name: 'Category',
  mixins: [handleError],
  props: {
    // 帖子加载中状态
    postLoading: {
      type: Boolean,
      default: false
    },
    // 分类列表
    list: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      categoryList: [], // 分类展示列表
      selectId: 0, // 当前选择项,包含父子项
      selectId_parent: 0 // 选中父类
    };
  },
  watch: {
    list: {
      handler(val) {
        if (this.categoryList.length === 0) {
          this.handleData(val);
        }
      },
      deep: true
    }
  },
  mounted() {
    // 判断父组件有没有传值过来，没有重新获取一遍
    if (this.categoryList.length === 0) {
      this.getCategoryList();
    }
    // 默认全部分类，否则当前ID为点击项的携带参数
    if (this.$route.params.id) {
      this.selectId = +this.$route.params.id;
    }
  },
  methods: {
    // 获取分类列表
    getCategoryList() {
      const type = {
        type: 5
      };
      apiRequest.readCategories({ params: type }).then((res) => {
        const resData = [...res.Data] || [];
        this.handleData(resData);
      }, (e) => {
        this.handleError(e);
      });
    },
    // 对数据做处理
    handleData(data) {
      // 计算全部帖子数
      let threadCount = 0;
      this.categoryList = [];
      data.forEach((item) => {
        let hasChild = false;
        const newChild = [];
        // 累加父类
        threadCount += item.threadCount;
        // 判断是否有子类
        if (item.children && item.children.length > 0) {
          hasChild = true;
          item.children.forEach(c => {
            // 处理子类
            newChild.push({
              id: parseInt(c.pid),
              search_ids: c.searchIds,
              name: c.name,
              thread_count: c.threadCount
            });
          });
        }
        // 处理父类
        this.categoryList.push({
          id: parseInt(item.pid),
          search_ids: item.searchIds,
          name: item.name,
          thread_count: item.threadCount,
          hasChild: hasChild,
          children: newChild
        });
      });
      // 为分类列表添加全部项
      this.categoryList.unshift({
        id: 0,
        search_ids: '0',
        name: this.$t('topic.whole'),
        thread_count: threadCount
      });
      // 确定当前激活的父子类
      this.categoryList.forEach(item => {
        if (item.id === this.selectId) {
          this.selectId_parent = item.id;
        } else {
          item.children && item.children.forEach(c => {
            if (c.id === this.selectId) {
              this.selectId_parent = item.id;
            }
          });
        }
      });
    },
    // 当前分类切换
    onChange(id, p_id, search_ids) {
      if (this.postLoading) return;
      this.selectId = id;
      this.selectId_parent = p_id;
      this.$emit('onChange', { id: id, search_ids: search_ids });
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/css/variable/color.scss';
/*IFTRUE_pay*/
@import '@/assets/css/variable/color_pay.scss';
/*FITRUE_pay*/
@import '@/assets/css/variable/mixin.scss';
.category-container{
  padding-left: 35px;
  padding-right: 20px;
  margin-bottom: 20px;
  @media screen and ( max-width: 1005px ) {
    padding-left: 20px;
    padding-right: 14px;
  }
  .category-item{
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 50px;
    font-family: 'MicrosoftYaHei';
    font-size: 16px;
    color: #333;
    border-bottom: 1px solid #eeeeee;
    cursor: pointer;
    &:hover{
      color: $color-blue-base;
      background: rgba(229, 242, 255, 0.3);
    }
    &.active{
      color: $color-blue-base;
      font-weight: bold;
    }
    &.loading{
      cursor: no-drop;
    }
    @media screen and ( max-width: 1005px ) {
      font-size: 14px;
    }
    .title{
      flex: 1;
      margin-right: 10px;
      @include text-hidden();
    }
    .count{
      color: #999999;
      font-size: 14px
    }
  }
  .category-item-sub{
    font-size: 14px;
    color: #666;
    border-bottom: none;
    padding-left: 20px;
    &:last-child{
      border-bottom: 1px solid #eeeeee;
    }
    &:hover,&.active{
      color: $color-blue-base;
    }
    .count{
      color: $font-color-grey;
    }
  }
  .arrow-icon{
    position: absolute;
    top: 50%;
    left: -20px;
    transform: translateY(-50%);
    width: 9px;
    height: 15px;
    line-height: 15px;
    color: $color-blue-base;
    font-size: 15px;
    font-weight: bold;
    @media screen and ( max-width: 1005px ) {
      left: -14px;
    }
  } 
}
</style>
