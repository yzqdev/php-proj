<template>
  <div class="page-comment" @click="clearReply">
    <main v-loading="loading">
      <div class="container-comment">
        <div class="title">
          /*IFTRUE_default*/
          <avatar-component :author="comment.user || {}">
            {{ timerDiff(comment.updatedAt) + $t("topic.before") }}.. <span v-if="comment.user">归属地 {{ comment.user.province }}</span>
          </avatar-component>
          /*FITRUE_default*/
          /*IFTRUE_pay*/
          <avatar-component :author="comment.user || {}" :round="true">
            {{ $t("topic.publishAt") }}
            {{ timerDiff(comment.updatedAt) + $t("topic.before") }}..
          </avatar-component>
          /*FITRUE_pay*/
          <div v-show="comment.canHide" class="delete-comment">
            <div
              class="wrapper"
              @click="deleteComment({ id: comment._jv.id, type: 'comment' })"
            >
              <svg-icon type="trash" style="font-size: 16px" />
              <span>{{ $t("topic.delete") }}</span>
            </div>
          </div>
        </div>
        <topic-content
          :article="comment || {}"
          :paid-information="{ paid: true, price: 0 }"
          :thread-type="3"
          :category="{}"
          :video="{}"

        />
        <div class="thread-card">
          <avatar-component
            :size="40"
            round
            :author="(thread && thread.user) || {}"
          >
            {{ timerDiff(thread.createdAt) + $t("topic.before") }} .. <!--span v-if="thread.user">归属地 {{thread.user.province}}</span-->
          </avatar-component>
          <!-- <div
            v-show="thread && thread.firstPost"
            class="content-html"
            v-html="
              (thread && thread.firstPost && $xss(thread.firstPost.summary)) ||
                ''
            "
          /> -->
          <div
            v-show="thread && thread.firstPost"
            class="content-html"
            v-html="
              (thread && thread.firstPost && $xss(handleHTML(thread.firstPost.summary))) ||
                ''
            "
          />
          <nuxt-link
            :to="`/thread/${thread._jv ? thread._jv.id : ''}`"
            class="view-more"
          >{{ $t("topic.viewDetail") }}</nuxt-link>
          <svg-icon
            v-if="thread && thread.isEssence"
            style="font-size: 50px;"
            type="essence-comment"
            class="essence"
          />
        </div>
        <div id="reply" class="container-reply" @click.stop="">
          /*IFTRUE_pay*/
          <div class="line-pay" />
          /*FITRUE_pay*/
          <comment-header
            v-if="replyCount"
            id="replyCount"
            :comment-count="replyCount"
            :is-positive-sort="isPositiveSort"
            @changeSort="changeSort"
          />
          <div v-else class="without-comment">{{ $t("topic.noComment") }}</div>
          <editor
            v-if="afterPushReply"
            style="margin-top: 20px; margin-bottom: 20px"
            editor-style="comment"
            class="reply-editor"
            selector="reply-editor"
            :type-information="replyType"
            :post.sync="replyPost"
            :on-publish="onReplyPublish"
            @publish="replyPublish(comment._jv.id)"
          />
          <div v-loading="replyLoading">
            <reply-list
              :reply-list="replyList || []"
              @delete="deleteComment"
              @onLike="onLike"
              @reply="replyComment"
              @editComment="onEditComment"
            />
          </div>
          <list-load-more
            :loading="scrollLoading"
            :length="replyList.length"
            :has-more="replyCount > replyList.length"
            :page-num="pageNumber"
            @loadMore="getReplyList('scroll')"
          />
        </div>
      </div>
    </main>
    <topic-aside :author="(thread && thread.user) || {}" />
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
const threadInclude = 'posts.replyUser,user.groups,user,posts,posts.user,posts.likedUsers'
  + ',posts.images,firstPost,firstPost.likedUsers,firstPost.images,firstPost.attachments'
  + ',rewardedUsers,category,threadVideo,paidUsers';
const commentInclude = 'user,likedUsers,commentPosts,commentPosts.user,commentPosts.user.groups'
  + ',commentPosts.replyUser,commentPosts.replyUser.groups,commentPosts.mentionUsers'
  + ',commentPosts.images,images,attachments';
const replyInclude = 'replyUser,user.groups,user,images,commentUser';
import publishResource from '@/mixin/publishResource';
import postLegalityCheck from '@/mixin/postLegalityCheck';
import head from '@/mixin/head';
import handleError from '@/mixin/handleError';
import timerDiff from '@/mixin/timerDiff';
import isLogin from '@/mixin/isLogin';
import env from '@/utils/env';
import stringToLink from '@/utils/stringToLink';

export default {
  name: 'Comment',
  layout: 'custom_layout',
  mixins: [
    head,
    timerDiff,
    handleError,
    isLogin,
    publishResource,
    postLegalityCheck
  ],
  async asyncData({ store, query }) {
    if (!env.isSpider) {
      return {};
    }
    const threadId = query.threadId;
    const commentId = query.commentId;
    try {
      const thread = await store.dispatch('jv/get', [
        `threads/${threadId}`,
        { params: { include: threadInclude }}
      ]);
      const comment = await store.dispatch('jv/get', [
        `posts/${commentId}`,
        { params: { include: commentInclude }}
      ]);
      // TODO 暂时不用 SSR 避免出问题
      // const replyList = await store.dispatch('jv/get', [`posts`, {
      //   params: {
      //     'filter[thread]': threadId,
      //     'filter[reply]': commentId,
      //     'filter[isDeleted]': 'no',
      //     sort: '-createdAt',
      //     'filter[isComment]': 'yes',
      //     include: replyInclude
      //   }
      // }])
      // const { data: { included: commentIncluded }}
      // = await service.get(`posts/${commentId}`, { include: commentInclude })
      // if (comment && commentIncluded) comment.user
      // = commentIncluded.filter(item => item.type === 'users')[0].attributes
      return { thread, comment };
    } catch (e) {
      return { loading: true };
    }
  },
  data() {
    return {
      thread: {},
      comment: {},
      afterPushReply: true,
      pageLimit: 5,
      pageNumber: 1,
      replyCount: 0,
      isPositiveSort: true,
      replyList: [],
      replyLoading: false,
      scrollLoading: false,
      loading: false,
      onReplyPublish: false,
      replyPost: { text: '', imageList: [], attachedList: [] },
      replyType: {
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
        placeholder: '写下我的回复 ...'
      },
      commentPostId: '', // 当前回复的评论的id(楼中楼)
      commentUserId: '', // 当前回复的评论的用户id
      editResourceShow: {
        showUploadImg: false,
        showUploadVideo: false,
        showUploadAttached: false
      },
      onEditCommentPublish: false,
      editCommentPost: { text: '', imageList: [], attachedList: [] },
      editComment: {},
      editCommentID: '',
      editCommentIndex: -1,
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
        placeholder: '写下我的回复 ...'
      }
    };
  },
  computed: {
    threadId() {
      return this.$route.query.threadId;
    },
    commentId() {
      return this.$route.query.commentId;
    }
  },
  mounted() {
    if (Object.keys(this.comment).length === 0) {
      this.getComment().then(() => {
        this.loading = false;
      });
    }
    if (Object.keys(this.thread).length === 0) this.getThread();
    this.getReplyList('scroll');
  },
  methods: {
    getThread() {
      return this.$store
        .dispatch('jv/get', [
          `threads/${this.threadId}`,
          { params: { include: threadInclude }}
        ])
        .then(
          response => {
            if (response.isDeleted) return this.$router.push('/404');
            this.thread = response;
          },
          e => this.handleError(e)
        );
    },
    getComment() {
      return this.$store
        .dispatch('jv/get', [
          `posts/${this.commentId}`,
          { params: { include: commentInclude }}
        ])
        .then(
          response => {
            if (response.isDeleted) return this.$router.push('/404');
            // TODO replyCount 不包括审核中的评论
            this.replyCount = response.replyCount;
            this.comment = response;
          },
          e => this.handleError(e)
        );
    },
    getReplyList(type) {
      if (this.replyLoading) return;
      type === 'commit'
        ? (this.replyLoading = true)
        : (this.scrollLoading = true);
      return this.$store
        .dispatch('jv/get', [
          `posts`,
          {
            params: {
              'filter[thread]': this.threadId,
              'filter[reply]': this.commentId,
              'page[number]': this.pageNumber,
              'page[limit]': this.pageLimit,
              'filter[isDeleted]': 'no',
              'filter[isComment]': 'yes',
              sort: this.isPositiveSort ? 'createdAt' : '-createdAt',
              include: replyInclude
            }
          }
        ])
        .then(
          data => {
            this.replyList.push(...data);
            this.pageNumber += 1;
          },
          e => this.handleError(e)
        )
        .finally(() => {
          // eslint-disable-next-line no-multi-assign
          this.replyLoading = this.scrollLoading = false;
        });
    },
    changeSort(value) {
      this.scrollLoading = true;
      this.pageNumber = 1;
      this.replyList = [];
      this.isPositiveSort = value;
      this.getReplyList('commit');
    },
    deleteComment({ id, type }) {
      const params = {
        _jv: { type: `posts`, id },
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
              if (type === 'comment') {
                this.$router.push(`/thread/${this.thread._jv.id}`);
              } else if (type === 'reply') {
                const deleteReply = this.replyList.filter(
                  item => item._jv.id === id
                )[0];
                this.replyList.splice(this.replyList.indexOf(deleteReply), 1);
                this.replyCount -= 1;
              }
              this.$message.success(this.$t('topic.deleteSuccess'));
            },
            e => this.handleError(e)
          );
        },
        () => console.log('取消删除')
      );
    },
    onLike({ reply, index }) {
      const params = {
        _jv: { type: `posts`, id: reply._jv.id },
        isLiked: !reply.isLiked,
        include: replyInclude
      };
      return this.$store.dispatch('jv/patch', params).then(
        data => {
          this.replyList[index].isLiked = data.isLiked;
          this.replyList[index].likeCount = data.likeCount;
        },
        e => this.handleError(e)
      );
    },
    replyPublish(id) {
      if (!this.isLogin()) return;
      if (!this.replyPost.text) {
        return this.$message.warning(this.$t('post.theContentCanNotBeBlank'));
      }
      if (this.replyPost.text.length > this.replyType.textLimit) {
        return this.$message.warning(this.$t('post.messageLengthCannotOver'));
      }
      this.onReplyPublish = true;
      const replyParams = {
        _jv: {
          type: `posts`,
          relationships: {
            thread: { data: { type: 'threads', id: this.thread?._jv?.id }}
          }
        },
        isComment: true,
        replyId: id,
        content: this.replyPost.text
      };
      if (this.commentPostId) {
        replyParams.commentPostId = this.commentPostId;
        replyParams.commentUserId = this.commentUserId;
      }
      this.publishPostResource(replyParams, this.replyPost);
      return this.$store
        .dispatch('jv/post', replyParams)
        .then(
          response => {
            this.afterPushReply = false; // 重置编辑器
            this.$nextTick(() => {
              this.afterPushReply = true;
            });
            this.replyPost.text = '';
            this.replyPost.imageList = [];
            // false 倒序 true 正序， 倒叙是从最新到最旧
            if (this.isPositiveSort) {
              this.replyList.length === this.replyCount
                ? this.replyList.push(response)
                : '';
            } else {
              this.replyList.unshift(response);
            }
            this.replyCount += 1;
            this.postLegalityCheck(
              response,
              this.$t('topic.replyPublishSuccess')
            );
          },
          e => this.handleError(e)
        )
        .finally(() => {
          this.onReplyPublish = false;
        });
    },
    handleHTML(html) {
      return stringToLink(html);
    },
    replyComment(reply) {
      document.querySelector('#replyCount').scrollIntoView(true);
      this.commentPostId = reply._jv.id;
      this.commentUserId = reply.user._jv.id;
      this.replyType.placeholder = `回复${reply.user.username}`;
    },
    onEditComment(reply, index) {
      this.editCommentID = this.commentId;
      this.editComment = reply;
      this.editCommentPost.text = reply.content;
      this.editCommentIndex = index;
      this.$nextTick(() => {
        const textarea = document.querySelector(
          `.edit-comment-editor #textarea`
        );
        textarea.style.height = `${textarea.scrollHeight}px`; // TODO 重置textarea 待优
      });
      if (reply.images.length > 0) {
        this.initThreadResource(this.editCommentPost.imageList, reply.images);
        this.editResourceShow.showUploadImg = true;
      }
    },
    initThreadResource(target, resource) {
      resource.forEach(item => {
        const attached = {
          name: item.fileName,
          url: item.thumbUrl,
          id: item._jv.id,
          deleted: false // 用于图片 upload 的样式
        };
        target.push(attached);
      });
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
      if (this.threadId) editCommentParams._jv.id = this.threadId;
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
          { url: `/posts/${this.editComment._jv.id}` }
        ])
        .then(
          response => {
            this.scrollLoading = true;
            this.pageNumber = 1;
            this.replyList = [];
            this.getReplyList('commit');
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
    clearReply() {
      if (this.commentPostId) {
        this.commentPostId = '';
        this.commentUserId = '';
        this.replyType.placeholder = '写下我的回复 ...';
      }
    }
  },
  head() {
    return {
      title: this.comment && this.comment.content
        ? this.comment.content.slice(0, 15)
        : '评论详情页'
    };
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
/*FITRUE_pay*/

.page-comment {
  background: #f4f5f6;
  width: 100%;
  display: flex;
  justify-content: space-between;

  main {
    background: transparent;
    flex: 1;

    > .container-comment {
      border-radius: 3px;
      /*IFTRUE_default*/
      padding: 20px;
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      padding: 35px 70px;
      /*FITRUE_pay*/
      width: 100%;
      background: #ffffff;

      > .title {
        height: 50px;
        display: flex;
        justify-content: space-between;

        > .delete-comment {
          > .wrapper {
            cursor: pointer;

            span {
              color: $font-color-grey;
            }
          }
        }
      }

      > .thread-card {
        position: relative;
        margin-top: 30px;
        width: 100%;
        padding: 15px;
        background: $background-color-grey;
        > .content-html {
          margin-top: 10px;
          margin-bottom: 20px;
          word-break: break-all;
          /*IFTRUE_pay*/
          max-width: 100% !important;
          ::v-deep pre{
            max-width: 100% !important;
            code {
              max-width: 100% !important;
            }
          }
          /*FITRUE_pay*/
        }
        > .view-more {
          position: absolute;
          right: 15px;
          bottom: 15px;
          color: $color-blue-base;
        }
        > .essence {
          position: absolute;
          top: -12px;
          right: 15px;
        }
      }

      > .container-reply {
        margin-top: 40px;
        > .without-comment {
          color: $font-color-grey;
          text-align: center;
        }
      }
    }
  }
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
    width: 72px;
    height: 10px;
    background: rgb(244,245,246);
  }
  &::before{
    left: -70px;
  }
  &::after{
    right: -70px;
  }
}
/*FITRUE_pay*/
</style>
