<template>
  <div class="mydrafts-container">
    <el-tabs v-model="activeName">
      <el-tab-pane :label="$t('topic.allDrafts', {total})" name="all" />
    </el-tabs>
    <div class="post-list">
      <template v-for="(item, index) in draftsList">
        /*IFTRUE_default*/
        <post-item
          :key="index"
          :item="item"
          :show-like="false"
          :show-comment="false"
          :show-share="false"
        >
          <template slot="btn-edit">
            <div class="btn edit" @click="handleEditDrafts(item,index)">
              <svg-icon type="pay_edit" class="icon" />
              {{ $t('topic.edit') }}
            </div>
          </template>
          <template slot="btn-delete">
            <div class="btn delete" @click="handleDeleteDrafts(item,index)">
              <svg-icon type="delete" class="icon" />
              {{ $t('topic.delete') }}
            </div>
          </template>
        </post-item>
        /*FITRUE_default*/
        /*IFTRUE_pay*/
        <post-item-pay
          :key="index"
          :item="item"
          :show-like="false"
          :show-comment="false"
          :show-share="false"
        >
          <template slot="btn-edit">
            <div class="btn edit" @click="handleEditDrafts(item,index)">
              <svg-icon type="pay_edit" class="icon" />
              {{ $t('topic.edit') }}
            </div>
          </template>
          <template slot="btn-delete">
            <div class="btn delete" @click="handleDeleteDrafts(item,index)">
              <svg-icon type="delete" class="icon" />
              {{ $t('topic.delete') }}
            </div>
          </template>
        </post-item-pay>
        /*FITRUE_pay*/
      </template>
      <list-load-more
        :loading="loading"
        :has-more="hasMore"
        :page-num="pageNum"
        :length="draftsList.length"
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
      title: this.$t('profile.mydrafts'),
      activeName: 'all',
      draftsList: [],
      total: 0,
      pageNum: 1,
      pageSize: 10,
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
    this.getDraftsList();
  },
  methods: {
    getDraftsList() {
      this.loading = true;
      const params = {
        include: 'user,user.groups,firstPost,firstPost.images,firstPost.attachments,firstPost.postGoods'
        + ',category,threadVideo,question,question.beUser,question.beUser.groups',
        'page[number]': this.pageNum,
        'page[limit]': this.pageSize
      };
      this.$store.dispatch('jv/get', ['thread/draft', { params }])
        .then((data) => {
          this.total = data._jv.json.meta.threadCount;
          this.hasMore = data.length === this.pageSize;
          if (this.pageNum === 1) {
            this.draftsList = data;
          } else {
            this.draftsList = [...this.draftsList, ...data];
          }
          console.log(this.draftsList);
          this.pageNum += 1;
          if (data._jv) {
            this.hasMore = this.draftsList.length < data._jv.json.meta.threadCount;
          }
        }, (e) => {
          this.handleError(e);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    loadMore() {
      this.getDraftsList();
    },
    // 处理编辑
    handleEditDrafts(item, index) {
      const params = {
        threadId: item._jv.id,
        operating: 'draft'
      };
      if (item.category) {
        params.categoryId = item.category._jv.id;
      }
      if (item.question && item.question.be_user_id) {
        params.beaskId = item.question.be_user_id;
      }
      this.$router.push({
        path: `/thread/postpay?type=${item.type}`,
        query: params
      });
    },
    // 处理删除
    handleDeleteDrafts(item, index) {
      this.$confirm('是否确认删除此草稿?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.$store.dispatch('jv/delete', [`threads/${item._jv.id}`, {}])
          .then(() => {
            this.$message.success('删除成功!');
            this.pageNum = 1;
            this.draftsList = [];
            this.getDraftsList();
          })
          .catch((e) => {
            this.handleError(e);
          });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        });
      });
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
  .svg-icon-pay_edit{
    font-size: 22px !important;
  }
  .svg-icon-pay_publish{
    font-size: 21px !important;
  }
  .svg-icon-delete{
    font-size: 15px !important;
  }
}
</style>
