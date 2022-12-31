
<template>
  <div>
    /*IFTRUE_pay*/
    <div class="line-pay" />
    /*FITRUE_pay*/
    <comment-header
      v-if="postCount > 0"
      :comment-count="postCount"
      :is-positive-sort="isPositiveSort"
      @changeSort="changeSort"
    />
    <div v-else class="without-comment">{{ $t("topic.noComment") }}</div>
    <editor
      v-if="afterPushComment"
      editor-style="comment"
      class="comment-editor"
      selector="comment-editor"
      :type-information="commentType"
      :post.sync="comment"
      :on-publish="onCommentPublish"
      @publish="postCommentPublish"
    />
    <template v-if="postCount > 0">
      <div v-loading="commitLoading" style="min-height: 100px">
        <comment-list
          :types="types"
          :author="author"
          :current-user="currentUser"
          :reward-question="rewardQuestion"
          :thread-id="threadId"
          :comment-list="commentList"
          @replyPublish="updateSingleComment"
          @deleteComment="deleteComment"
          @deleteReply="deleteReply"
          @editComment="onEditComment"
          @like="onLike"
          @adoption="onAdoption"
          @reportShow="reportShow"
        />
      </div>
      <list-load-more
        :loading="scrollLoading"
        :length="commentList.length"
        :has-more="postCount > commentList.length"
        :page-num="pageNumber"
        @loadMore="loadMore"
        @scrollPost="scrollPost"
      />
    </template>
    <el-dialog
      :title="$t('topic.editComment')"
      :visible="Object.keys(editComment).length > 0"
      width="820px"
      destroy-on-close
      @close="closeEditPop"
    >
      <avatar-component :author="editComment.user || {}" :size="50">
        {{
          $t("topic.publishAt") +
            " " +
            timerDiff(editComment.updatedAt) +
            $t("topic.before")
        }}..
      </avatar-component>
      <editor
        style="margin-top: 20px;"
        editor-style="comment"
        class="edit-comment-editor"
        selector="edit-comment-editor"
        :edit-resource-show="editResourceShow"
        :on-publish="onEditCommentPublish"
        :type-information="commentType"
        :post.sync="editCommentPost"
        :is-edit="true"
        @publish="postEditCommentPublish"
      />
    </el-dialog>
  </div>
</template>

<script>
const postInclude = 'user,replyUser,images,thread,user.groups,thread.category'
  + ',lastThreeComments,lastThreeComments.user,lastThreeComments.replyUser,deletedUser'
  + ',lastDeletedLog,lastThreeComments.images,lastThreeComments.commentUser';
const commentInclude = 'user,likedUsers,commentPosts,commentPosts.user,commentPosts.user.groups'
  + ',commentPosts.replyUser,commentPosts.replyUser.groups,commentPosts.mentionUsers,commentPosts.images'
  + ',images,attachments';
import publishResource from '@/mixin/publishResource';
import postLegalityCheck from '@/mixin/postLegalityCheck';
import handleError from '@/mixin/handleError';
import timerDiff from '@/mixin/timerDiff';
import isLogin from '@/mixin/isLogin';
import newApi from '@/api/new-api';

export default {
  name: 'Comment',
  mixins: [handleError, publishResource, postLegalityCheck, isLogin, timerDiff],
  props: {
    threadId: {
      type: String,
      default: '0'
    },
    types: {
      type: [Number, String],
      default: 49999
    },
    author: {
      type: Object,
      default: () => {}
    },
    currentUser: {
      type: Object,
      default: () => {}
    },
    rewardQuestion: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      commentList: [],
      postCount: 0,
      pageLimit: 5,
      pageNumber: 1,
      isPositiveSort: true,
      onCommentPublish: false,
      onEditCommentPublish: false,
      comment: { text: '', imageList: [], attachedList: [] },
      editResourceShow: {
        showUploadImg: false,
        showUploadVideo: false,
        showUploadAttached: false
      },
      editCommentPost: { text: '', imageList: [], attachedList: [] },
      editComment: {},
      editCommentID: '',
      commentType: {
        type: 10,
        textLimit: 5000,
        showPayment: false,
        showTitle: false,
        showImage: true,
        showVideo: false,
        showAttached: false,
        showEmoji: true,
        showTopic: false,
        showCaller: true,
        placeholder: '写下我的评论 ...'
      },
      commitLoading: false,
      scrollLoading: false,
      afterPushComment: true, // 临时用了重置编辑器
      threadPostCount: 0
    };
  },
  created() {
    this.getComment('scroll');
    // 数据预加载相关，不参与界面渲染，无需放入data
    this.preThreadsPostsData = null;
    this.preThreadsPostsLoading = false;
  },
  methods: {
    preLoadThreads() {
      if (this.preThreadsPostsData || this.preThreadsPostsLoading) {
        return;
      }
      this.preThreadsPostsLoading = true;
      const params = {
        page: this.pageNumber,
        perPage: this.pageLimit,
        'filter[thread]': this.threadId,
        sort: this.isPositiveSort ? 'createdAt' : '-createdAt'
      };
      return newApi.readCommentList({ params }).then(result => {
        this.preThreadsPostsData = {
          [params.page]: result
        };
      })
        .catch(() => {
          this.preThreadsPostsData = null;
        })
        .finally(() => {
          this.preThreadsPostsLoading = false;
          this.commitLoading = false;
          this.scrollLoading = false;
        });
    },
    async scrollPost() {
      if (this.preThreadsPostsLoading) {
        return;
      }
      if (this.postCount > this.commentList.length) {
        await this.preLoadThreads();
        this.getComment('scroll');
      } else {
        this.scrollLoading = false;
        this.preThreadsPostsLoading = true;
      }
    },
    loadMore() {
      this.getComment('scroll');
    },
    async getComment(type) {
      if (this.commitLoading) return; // 事件防抖
      type === 'commit'
        ? (this.commitLoading = true)
        : (this.scrollLoading = true);
      const params = {
        page: this.pageNumber,
        perPage: this.pageLimit,
        'filter[thread]': this.threadId,
        sort: this.isPositiveSort ? 'createdAt' : '-createdAt'
      };
      let result;
      try {
        if (this.preThreadsPostsData && this.preThreadsPostsData[this.pageNumber]) {
          result = this.preThreadsPostsData[this.pageNumber];
          this.preThreadsPostsData = null;
        } else {
          // 这是清空，是处理快速滚动特殊情况下，
          // preThreadsPostsData 有数据，但是页数不匹配的情况
          this.preThreadsPostsData = null;
          result = await newApi.readCommentList({ params });
        }
        const data = result.Data.pageData;
        this.pageNumber += 1;
        // TODO postCount 不包括审核中的回复
        this.postCount = data.length > 0 ? result.Data.totalCount : 0;
        if (result.Data.currentPage <= result.Data.totalPage) {
          this.commentList.push(...data);
        }
        this.commentList.forEach(item => {
          item.isShowReport = false;
        });
        this.commitLoading = false;
        this.scrollLoading = false;
      } catch (e) {
        this.handleError(e);
      }
    },
    updateSingleComment(commentId) {
      if (this.commitLoading === true) return; // 事件防抖
      this.commitLoading = true;
      return this.$store
        .dispatch('jv/get', [
          `posts/${commentId}`,
          { params: { include: commentInclude }}
        ])
        .then(
          comment => {
            comment.lastThreeComments = comment.commentPosts
              .reverse()
              .filter(item => !item.isDeleted)
              .slice(0, 3); // 除去已删除，改为倒叙，截取前三
            this.$set(
              this.commentList,
              this.getCommentIndexFromId(commentId),
              comment
            );
            this.commitLoading = false;
            if (this.postCount > this.commentList.length) {
              this.scrollLoading = true;
            } else {
              this.scrollLoading = false;
            }
          },
          e => this.handleError(e)
        )
        .finally(() => {
          this.commitLoading = false;
        });
    },
    changeSort(value) {
      this.scrollLoading = true;
      this.pageNumber = 1;
      this.preThreadsPostsData = null;
      this.commentList = [];
      this.isPositiveSort = value;
      this.getComment('commit');
    },
    onLike(comment, index, type, comIndex) {
      const params = {
        _jv: { type: `posts`, id: comment.id },
        isLiked: !comment.isLiked,
        include: postInclude
      };
      return this.$store.dispatch('jv/patch', params).then(
        data => {
          if (type === 'comment') {
            if (data && this.commentList[index]) {
              const { likeCount, isLiked, likedAt } = data;
              this.commentList[index]['likeCount'] = likeCount;
              this.commentList[index]['isLiked'] = isLiked;
              this.commentList[index]['likedAt'] = likedAt;
            }
          } else {
            this.commentList[comIndex].lastThreeComments[index]['likeCount'] = data.likeCount;
            this.commentList[comIndex].lastThreeComments[index]['isLiked'] = data.isLiked;
          }
        },
        e => this.handleError(e)
      );
    },
    // 采纳
    onAdoption({ commentId, rewards }) {
      const params = {
        _jv: { type: `posts/reward` },
        thread_id: +this.threadId,
        rewards: +rewards,
        post_id: +commentId
      };
      return this.$store.dispatch('jv/post', params).then(data => {
        this.pageNumber = 1;
        this.preThreadsPostsData = null;
        this.commentList = [];
        this.postCount = 0;
        this.getComment();
      }, e => this.handleError(e));
    },
    deleteComment(id) {
      const params = { _jv: { type: `posts`, id }, isDeleted: true };
      this.$confirm(
        this.$t('topic.confirmDelete'),
        this.$t('discuzq.msgBox.title'),
        {
          confirmButtonText: this.$t('discuzq.msgBox.confirm'),
          cancelButtonText: this.$t('discuzq.msgBox.cancel'),
          type: 'warning'
        }
      ).then(
        () => {
          return this.$store.dispatch('jv/patch', params).then(
            () => {
              this.postCount -= 1;
              this.commentList.splice(this.getCommentIndexFromId(id), 1);
              this.$message.success(this.$t('topic.deleteSuccess'));
            },
            e => this.handleError(e)
          );
        },
        () => console.log('取消删除')
      );
    },
    deleteReply({ replyId, commentId }) {
      const params = {
        _jv: { type: `posts`, id: replyId },
        isDeleted: true
      };
      this.$confirm(
        this.$t('topic.confirmDelete'),
        this.$t('discuzq.msgBox.title'),
        {
          confirmButtonText: this.$t('discuzq.msgBox.confirm'),
          cancelButtonText: this.$t('discuzq.msgBox.cancel'),
          type: 'warning'
        }
      ).then(
        () => {
          return this.$store.dispatch('jv/patch', params).then(
            () => {
              this.updateSingleComment(commentId);
              this.$message.success(this.$t('topic.deleteSuccess'));
            },
            e => this.handleError(e)
          );
        },
        () => console.log('取消删除')
      );
    },
    onEditComment(comment, data) {
      if (data) {
        this.editCommentID = data;
      }
      this.editComment = comment;
      this.editCommentPost.text = comment.content;
      this.$nextTick(() => {
        const textarea = document.querySelector(
          `.edit-comment-editor #textarea`
        );
        textarea.style.height = `${textarea.scrollHeight}px`; // TODO 重置textarea 待优
      });
      if (comment.images.length > 0) {
        this.initThreadResource(this.editCommentPost.imageList, comment.images);
        this.editResourceShow.showUploadImg = true;
      }
    },
    postEditCommentPublish() {
      if (!this.isLogin()) return;
      if (this.onEditCommentPublish) return;
      if (!this.editCommentPost.text) {
        return this.$message.warning(this.$t('post.theContentCanNotBeBlank'));
      }
      if (this.editCommentPost.text.length > this.commentType.textLimit) {
        return this.$message.warning(this.$t('post.messageLengthCannotOver'));
      }
      this.onEditCommentPublish = true;
      let editCommentParams = {
        _jv: {
          type: `posts`,
          relationships: {}
        },
        content: this.editCommentPost.text
      };
      if (this.threadId) editCommentParams.id = this.threadId;
      editCommentParams = this.publishPostResource(
        editCommentParams,
        this.editCommentPost
      );
      this.deleteAttachmentsAfterEdit(
        this.editComment.images,
        this.editCommentPost.imageList
      );
      this.$store
        .dispatch('jv/patch', [
          editCommentParams,
          { url: `/posts/${this.editComment.id}` }
        ])
        .then(
          response => {
            if (this.editCommentID) {
              this.updateSingleComment(this.editCommentID);
            } else {
              this.updateSingleComment(this.editComment.id);
            }
            this.postLegalityCheck(
              response,
              this.$t('topic.commentPublishSuccess')
            );
            this.closeEditPop();
          },
          e => this.handleError(e)
        )
        .finally(() => {
          this.onEditCommentPublish = false;
        });
    },
    closeEditPop() {
      this.editComment = {};
      this.editCommentPost.text = '';
      this.editCommentPost.imageList = [];
      this.editResourceShow.showUploadImg = false;
    },
    initThreadResource(target, resource) {
      resource.forEach(item => {
        const attached = {
          name: item.fileName,
          url: item.thumbUrl,
          id: item.id,
          deleted: false // 用于图片 upload 的样式
        };
        target.push(attached);
      });
    },
    postCommentPublish() {
      if (!this.isLogin()) return;
      if (this.onCommentPublish) return;
      if (!this.comment.text) {
        return this.$message.warning(this.$t('post.theContentCanNotBeBlank'));
      }
      if (this.comment.text.length > this.commentType.textLimit) {
        return this.$message.warning(this.$t('post.messageLengthCannotOver'));
      }
      this.onCommentPublish = true;
      const commentParams = {
        _jv: {
          type: `posts`,
          relationships: {
            thread: { data: { type: 'threads', id: this.threadId, types:this.types }}
          }
        },
        content: this.comment.text
      };
      this.publishPostResource(commentParams, this.comment);
      return this.$store
        .dispatch('jv/post', commentParams)
        .then(
          response => {
            this.comment.text = '';
            this.comment.imageList = [];
            this.afterPushComment = false; // 重置输入框
            this.$nextTick(() => {
              this.afterPushComment = true;
            });
            this.postLegalityCheck(
              response,
              this.$t('topic.commentPublishSuccess')
            );
            // false 倒序 true 正序， 倒叙是从最新到最旧
            if (this.isPositiveSort) {
              this.commentList.length === this.postCount
                ? this.commentList.push(response)
                : '';
            } else {
              this.commentList.unshift(response);
            }
            this.postCount += 1;
          },
          e => this.handleError(e)
        )
        .finally(() => {
          this.onCommentPublish = false;
        });
    },
    getCommentIndexFromId(id) {
      const deleteComment = this.commentList.filter(
        item => item.id === id
      )[0];
      return this.commentList.indexOf(deleteComment);
    },
    reportShow(index, type) {
      if (type === 0) {
        this.commentList[index].isShowReport = true;
      } else {
        this.commentList[index].isShowReport = false;
      }
      this.commentList = Object.assign([], this.commentList);
    }
  }
};
</script>

<style>
.el-dialog {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  margin: 0 !important;
}
.el-dialog .el-dialog__body {
  padding: 20px;
}
.el-dialog .el-dialog__title {
  color: #6d6d6d;
  font-size: 16px;
  font-weight: 700;
}
.el-dialog .el-dialog__header {
  padding-bottom: 0;
}
.el-dialog .el-icon-close {
  font-size: 22px;
}
</style>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_pay*/
@import '@/assets/css/variable/color_pay.scss';
/*FITRUE_pay*/

.container-show-more {
  padding-top: 20px;
  border-top: 1px solid $border-color-base;
  .show-more {
    width: 100%;
    border: 1px solid $color-blue-base;
    border-radius: 3px;
    font-size: 16px;
    height: 45px;
    line-height: 45px;
    color: $color-blue-base;
  }
}
.without-comment {
  color: $font-color-grey;
  text-align: center;
}
.edit-box {
  padding: 20px;
}
/*IFTRUE_pay*/
.line-pay{
  position: relative;
  width: 100%;
  height: 10px;
  margin-bottom: 20px;
  background: rgb(244,245,246);
  &::before, &::after{
    content: '';
    position: absolute;
    top: 0;
    width: 76px;
    height: 10px;
    background: rgb(244,245,246);
  }
  &::before{
    left: -72px;
  }
  &::after{
    right: -72px;
  }
}
/*FITRUE_pay*/
</style>
