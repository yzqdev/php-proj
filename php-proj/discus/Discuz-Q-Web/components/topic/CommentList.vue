<template>
  <div>
    <div
      v-for="(comment, index) in commentList"
      :key="index"
      class="comment"
      @mouseenter="$emit('reportShow', index , 0)"
      @mouseleave="$emit('reportShow', index , 1)"
    >
      /*IFTRUE_default*/
      <div class="title">
        <div class="title-info">
          <avatar-component :author="comment.user" :size="30" />
          <!--div class="time">{{ formatDate(comment.updatedAt) }} <span>归属地 {{comment.user.province}}</span></div-->
        </div>
        <div class="management" @click.stop="() => {}">
          <div v-if="comment.isShowReport" class="report" @click="toShield(comment.id)">
            {{ $t('report.reportTitle') }}
          </div>
          <div v-if="comment.isApproved === 0" class="checking">
            {{ $t("topic.inReview") }}
          </div>
          <el-dropdown
            v-if="comment.canEdit || comment.canHide"
            size="medium"
            class="filter-dropdown"
            @command="commentManagement"
          >
            <span class="el-dropdown-link">
              {{ $t("topic.management") }}
            </span>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item
                v-if="comment.canEdit"
                :command="beforeHandleCommand('editComment', comment)"
              >
                {{ $t("topic.edit") }}
              </el-dropdown-item>
              <el-dropdown-item
                v-if="comment.canHide"
                :divided="true"
                :command="beforeHandleCommand('deleteComment', comment.id)"
              >
                {{ $t("topic.delete") }}
              </el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
          <div v-show="comment.isApproved === 1" class="actions">
            <span
              v-show="comment.canLike"
              class="like"
              @click="$emit('like', comment, index , 'comment')"
            >
              <svg-icon
                :type="comment.isLiked ? 'liked' : 'like'"
                style="font-size: 18px"
              />
              <span class="text">
                {{ $t("topic.like") }} {{ comment.likeCount > 0 ? comment.likeCount : "" }}</span>
            </span>
            <span
              v-show="rewardQuestion && rewardQuestion.type === 0 && canAdoption"
              class="adoption"
              @click="openAdoption(comment)"
            >
              <svg-icon type="pay_adoption" style="font-size: 18px" />
              <span class="text">采纳</span>
            </span>
            <span @click="toReply(comment.user, index)">
              <svg-icon type="comment" style="font-size: 18px" />
              <span class="text">{{ $t("topic.replyTa") }}</span>
            </span>
          </div>
        </div>
      </div>
      <div v-if="comment && comment.redPacketAmount" class="tip">
        <img class="tip-logo" src="../../assets/pay_imgs/logo2.png" alt="红包">
        获得<span class="tip-money">{{ comment.redPacketAmount }}元</span>红包
      </div>
      <div v-if="comment && comment.rewards > 0" class="tip">
        <img class="tip-logo" src="../../assets/pay_imgs/offer_reward.png" alt="悬赏">
        获得<span class="tip-money">{{ comment.rewards }}元</span>悬赏金
      </div>
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      <div class="title">
        <avatar-component :author="comment.user" :size="45" :round="true" />
        <!--span class="kuq-ziti-huise kuq-font-14">归属地： {{comment.user.province}}</span-->
        <!-- 红包获取情况 -->
        <div v-if="comment && comment.redPacketAmount" class="tip">
          <img class="tip-logo" src="../../assets/pay_imgs/logo2.png" alt="红包">
          获得<span class="tip-money">{{ comment.redPacketAmount }}元</span>红包
        </div>
        <div v-if="comment && comment.rewards > 0" class="tip">
          <img class="tip-logo" src="../../assets/pay_imgs/offer_reward.png" alt="悬赏">
          获得<span class="tip-money">{{ comment.rewards }}元</span>悬赏金
        </div>
        <div class="management">
          <div
            v-if="comment.canEdit"
            class="manage-button"
            @click="$emit('editComment', comment)"
          >{{ $t('topic.edit') }}</div>
          <div
            v-if="comment.canHide"
            class="manage-button"
            @click="$emit('deleteComment', comment.id)"
          >{{ $t('topic.delete') }}</div>
          <div v-if="comment.isApproved === 0" class="checking">
            {{ $t('topic.inReview') }}
          </div>
        </div>
      </div>
      /*FITRUE_pay*/

      <div class="container-detail">
        <div
          class="content-html"
          @click="showAll($event, comment)"
          v-html="$xss(formatSummary(comment))"
        />
        <div
          v-if="comment.images && comment.images.length > 0"
          v-viewer="{url: 'data-source'}"
          class="images"
        >
          <el-image
            v-for="(image, imageIndex) in comment.images"
            :key="imageIndex"
            style="width: 100px; height: 100px;border-radius: 5px; margin-right: 10px"
            :src="image.thumbUrl"
            :alt="image.filename"
            :data-source="image.url"
            fit="cover"
          />
        </div>
        /*IFTRUE_pay*/
        <div v-show="comment.isApproved === 1" class="actions">
          <div class="left">
            发布于 {{ formatDate(comment.updatedAt) }}
            <span>
              <span v-if="comment.replyCount > 0 " class="text">
                {{ $t('topic.replyAlready') + ' ' + comment.replyCount }}
              </span>
            </span>
          </div>
          <span
            v-show="comment.canLike"
            @click="$emit('like', comment, index , 'comment')"
          >
            <svg-icon
              :type="comment.isLiked ? 'liked' : 'like'"
              style="font-size: 18px"
            />
            <span class="text">
              {{ $t('topic.like') }}
              {{ comment.likeCount > 0 ? comment.likeCount : '' }}
            </span>
          </span>
          <span
            @click="
              twoShowReplyEditorForIndex = twoShowReplyEditorForIndex === index
                ? -1
                : index
            "
          >
            <svg-icon type="comment" style="font-size: 18px" />
            <span class="text">{{ $t('topic.replyTa') }}</span>
          </span>
          <span
            v-if="rewardQuestion && rewardQuestion.type === 0 && canAdoption"
            @click="openAdoption(comment)"
          >
            <svg-icon type="pay_adoption" style="font-size: 18px" />
            <span class="text">采纳</span>
          </span>
          <span @click="toShield(comment.id)">
            <svg-icon type="report" class="icon-setting" style="font-size: 16px" />
            <span class="text">{{ $t('report.reportTitle') }}</span>
          </span>
        </div>
        /*FITRUE_pay*/
      </div>
      <editor
        v-if="twoShowReplyEditorForIndex === index"
        style="margin-top: 0; margin-bottom: 20px"
        editor-style="reply"
        class="reply-editor"
        selector="reply-editor"
        :type-information="replyType"
        :post.sync="replyPost"
        :on-publish="onReplyPublish"
        @publish="replyPublish(comment.id)"
      />
      <div
        v-if="replyList[index] && replyList[index].length"
        class="reply-list"
        @click="
          $router.push(
            `/thread/comment?threadId=${threadId}&commentId=${comment.id}#reply`
          )
        "
      >
        <div
          v-for="(reply, replyIndex) in replyList[index]"
          :key="replyIndex"
          class="reply"
        >
          <div class="title">
            /*IFTRUE_default*/
            <Avatar :user="reply.user || {}" size="30" />
            /*FITRUE_default*/
            /*IFTRUE_pay*/
            <Avatar :user="reply.user || {}" size="30" :round="true" />
            /*FITRUE_pay*/
            <div class="title-info">
              <span class="author-name">{{ reply.user ? reply.user.username : '' }}</span>
              <span class="content-reply">
                <span
                  v-if="reply.commentPostId !== null && reply.commentUser"
                  class="toReply"
                >
                  {{ $t("topic.reply") }}
                </span>
                <span
                  v-if="reply.commentPostId !== null && reply.commentUser"
                  class="replyUser"
                >
                  {{ `${reply.commentUser.username}` }}
                </span>
              </span>
              <span class="timer">{{ formatDate(reply.updatedAt) }}</span>
              <span class="timer">归属地 {{ reply.user.province }}</span>
              <div class="management" @click.stop="() => {}">
                <div v-if="reply.isApproved === 0" class="checking">
                  {{ $t("topic.inReview") }}
                </div>
                /*IFTRUE_default*/
                <el-dropdown
                  v-if="reply.canEdit || reply.canHide"
                  size="medium"
                  class="filter-dropdown"
                  @command="replyManagement"
                >
                  <span class="el-dropdown-link">
                    {{ $t("topic.management") }}
                  </span>
                  <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item
                      v-if="reply.canEdit"
                      :command="beforeHandleReply('editComment', reply, comment.id)"
                    >
                      {{ $t("topic.edit") }}
                    </el-dropdown-item>
                    <el-dropdown-item
                      v-if="reply.canHide"
                      :divided="true"
                      :command="beforeHandleReply('deleteReply', reply.id, comment.id)"
                    >
                      {{ $t("topic.delete") }}
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </el-dropdown>
                <div v-show="reply.isApproved === 1" class="actions">
                  <span
                    v-show="reply.canLike"
                    class="like"
                    @click="$emit('like', reply, replyIndex, 'reply', index)"
                  >
                    <svg-icon
                      :type="reply.isLiked ? 'liked' : 'like'"
                      style="font-size: 18px"
                    />
                    <span class="text">
                      {{ $t("topic.like") }} {{ reply.likeCount > 0 ? reply.likeCount : "" }}</span>
                  </span>
                  <span @click="toReply(reply.user, replyIndex, reply)">
                    <svg-icon type="comment" style="font-size: 18px" />
                    <span class="text">{{ $t("topic.replyTa") }}</span>
                  </span>
                </div>
                /*FITRUE_default*/
                /*IFTRUE_pay*/
                <span
                  v-if="reply.canEdit"
                  class="manage-button"
                  @click="replyManagement(beforeHandleReply('editComment', reply, comment.id))"
                >
                  {{ $t("topic.edit") }}
                </span>
                <span
                  v-if="reply.canHide"
                  class="manage-button"
                  @click="replyManagement(beforeHandleReply('deleteReply', reply.id, comment.id))"
                >
                  {{ $t("topic.delete") }}
                </span>
                /*FITRUE_pay*/
              </div>
            </div>
          </div>
          <div
            class="content-html"
            @click="showAll($event, replyIndex, replyList)"
            v-html="$xss(formatReplySummary(reply.summary))"
          />
          <div
            v-if="reply.images && reply.images.length > 0"
            v-viewer="{ url: 'data-source' }"
            class="images"
            @click.stop="() => {}"
          >
            <el-image
              v-for="(image, imageIndex) in reply.images"
              :key="imageIndex"
              style="width: 100px; height: 100px;border-radius: 5px; margin-right: 10px; margin-bottom: 10px"
              :src="image.thumbUrl"
              :alt="image.filename"
              :data-source="image.url"
              fit="cover"
            />
          </div>
          /*IFTRUE_pay*/
          <div v-show="reply.isApproved === 1" class="actions" @click.stop="() => {}">
            <span
              v-show="reply.canLike"
              class="like"
              @click="$emit('like', reply, replyIndex, 'reply', index)"
            >
              <svg-icon
                :type="reply.isLiked ? 'liked' : 'like'"
                style="font-size: 18px"
              />
              <span class="text">
                {{ $t("topic.like") }} {{ reply.likeCount > 0 ? reply.likeCount : "" }}</span>
            </span>
            <span @click="toReply(reply.user, replyIndex, reply)">
              <svg-icon type="comment" style="font-size: 18px" />
              <span class="text">{{ $t("topic.replyTa") }}</span>
            </span>
          </div>
          /*FITRUE_pay*/
          <div @click.stop="() => {}">
            <editor
              v-if="
                threeShowReplyEditorForIndex === replyIndex
                  && commentPostId === reply.id
              "
              style="margin-top: 0; margin-bottom: 20px"
              editor-style="reply"
              class="reply-editor"
              selector="reply-editor"
              :type-information="replyType"
              :post.sync="replyPost"
              :on-publish="onReplyPublish"
              @publish="replyPublish(reply.id, comment.id)"
            />
          </div>
          <div class="line" />
        </div>
        <div v-if="comment.replyCount > 3">
          <div
            v-if="comment.replyCount !== (replyList[index] || []).length"
            class="show-all-reply"
          >
            {{ $t('topic.showOther') }} {{ comment.replyCount - 3 }}
            {{ $t('topic.item') + $t('topic.reply') }}
          </div>
        </div>
      </div>
    </div>
    <topic-report
      v-if="isShield"
      :thread-id="threadId"
      :type="2"
      :comment-id="commentId"
      @close="isShield = false"
    />
    <!-- 采纳弹框 -->
    <div v-show="showAdoptionBox" class="adoption-box">
      <div class="adoption-content">
        <div class="adoption-head">
          采纳回复悬赏
          <svg-icon type="close" @click="closeAdoption" />
        </div>
        <div class="percent">
          <div class="text">悬赏百分比：</div>
          <el-input
            v-model="percentVal"
            class="reward-percent"
            type="number"
          /> %
        </div>
        <div class="money">
          <div class="text">悬赏金额：</div>
          <div class="reward-money">{{ adoptionMoney }}</div>
        </div>
        <div class="btn">
          <el-button class="btn-confirm" @click="confirmAdoption">确定</el-button>
          <el-button class="btn-cancle" @click="cancel">取消</el-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import handleError from '@/mixin/handleError';
import isLogin from '@/mixin/isLogin';
import publishResource from '@/mixin/publishResource';
import postLegalityCheck from '@/mixin/postLegalityCheck';
import timerDiff from '@/mixin/timerDiff';
import s9e from '@/utils/s9e';
import dayjs from 'dayjs';
import stringToLink from '@/utils/stringToLink';

export default {
  name: 'CommentList',
  mixins: [handleError, timerDiff, publishResource, postLegalityCheck, isLogin],
  props: {
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
    },
    commentList: {
      type: Array,
      default: () => []
    },
    threadId: {
      type: String,
      default: ''
    },
    types: {
      type: [Number, String],
      default: '0'
    }
  },
  data() {
    return {
      replyList: [],
      onReplyPublish: false,
      twoShowReplyEditorForIndex: -1,
      threeShowReplyEditorForIndex: -1,
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
        placeholder: '写下我的评论 ...'
      },
      showAllReply: false,
      replyLoading: false,
      isShield: false,
      commentId: '',
      showAdoptionBox: false, // 显示采纳弹框
      percentVal: 0, // 悬赏百分比
      currentCommentId: 0, // 当前采纳评论ID
      commentPostId: '', // 当前回复的评论的id(楼中楼)
      commentUserId: '' // 当前回复的评论的用户id
    };
  },
  computed: {
    canAdoption() {
      return this.author.id === this.currentUser.id;
    },
    adoptionMoney() {
      return Number((this.percentVal * +this.rewardQuestion.money / 100).toFixed(2));
    }
  },
  watch: {
    commentList: {
      handler(val) {
        if (!this.showAllReply) {
          this.replyList = val.map(item => item.lastThreeComments);
        }
      },
      deep: true,
      immediate: true
    }
  },
  methods: {
    openAdoption(comment) {
      if (comment.user.id === this.author.id) {
        return this.$message.warning('自己不能采纳自己');
      }
      this.currentCommentId = comment.id;
      this.showAdoptionBox = !this.showAdoptionBox;
    },
    closeAdoption() {
      this.showAdoptionBox = false;
    },
    confirmAdoption(commentId) {
      if (!this.percentVal) {
        return this.$message.warning('请输入采纳百分比！');
      }
      if (this.percentVal <= 0 || this.percentVal > 100) {
        return this.$message.warning('采纳百分比取值为0 ~ 100 (不包含0)');
      }
      if (Number(this.rewardQuestion.remain_money) < this.adoptionMoney) {
        return this.$message.warning('悬赏金额不足！');
      }
      this.$emit('adoption', {
        commentId: this.currentCommentId,
        rewards: this.adoptionMoney
      });
      this.showAdoptionBox = false;
      this.percentVal = 0;
    },
    cancel() {
      this.showAdoptionBox = false;
      this.percentVal = 0;
    },
    formatDate(date) {
      return dayjs(date).format('YYYY-MM-DD HH:mm');
    },
    formatSummary(comment) {
      let html;
      if (comment.content !== comment.summaryText) {
        html = `${stringToLink(comment.summary)}<button class="showAllComment">${this.$t(
          'topic.all'
        )}</button>`;
      } else {
        html = stringToLink(comment.contentHtml);
      }
      return s9e.parse(html);
    },
    formatReplySummary(html) {
      const _html = stringToLink(html);
      return s9e.parse(_html);
    },
    showAll(e, comment) {
      if (e.target.matches('.showAllComment')) {
        this.$router.push(
          `/thread/comment?threadId=${this.threadId}&commentId=${comment.id}`
        );
      }
    },
    replyPublish(id, commentId) {
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
            thread: { data: { type: 'threads', id: this.threadId, types:this.types }}
          }
        },
        isComment: true,
        replyId: id,
        content: this.replyPost.text
      };
      if (commentId) {
        replyParams.commentPostId = this.commentPostId;
        replyParams.commentUserId = this.commentUserId;
      }
      this.publishPostResource(replyParams, this.replyPost);
      return this.$store
        .dispatch('jv/post', replyParams)
        .then(
          response => {
            this.replyPost.text = '';
            this.replyPost.imageList = [];
            this.twoShowReplyEditorForIndex = -1;
            this.threeShowReplyEditorForIndex = -1;
            this.postLegalityCheck(
              response,
              this.$t('topic.replyPublishSuccess')
            );
            if (commentId) {
              this.$emit('replyPublish', commentId);
            } else {
              this.$emit('replyPublish', id);
            }
          },
          e => this.handleError(e)
        )
        .finally(() => {
          this.onReplyPublish = false;
        });
    },
    toShield(commentId) {
      this.isShield = true;
      this.commentId = commentId;
    },
    toReply(user, index, reply) {
      this.replyType.placeholder = `回复 ${user.username}`;
      if (reply) {
        if (this.commentPostId !== reply.id) {
          this.threeShowReplyEditorForIndex = -1;
        }
        this.threeShowReplyEditorForIndex = this.threeShowReplyEditorForIndex === index ? -1 : index;
        this.twoShowReplyEditorForIndex = -1;
        this.commentPostId = reply.id;
        this.commentUserId = user.id;
      } else {
        this.twoShowReplyEditorForIndex = this.twoShowReplyEditorForIndex === index ? -1 : index;
        this.threeShowReplyEditorForIndex = -1;
        this.commentPostId = '';
        this.commentUserId = '';
      }
    },
    // 获取下拉菜单点击选项的值
    beforeHandleCommand(command, data) {
      return {
        command, data
      };
    },
    // 评论管理
    commentManagement(command) {
      if (command.command === 'editComment') {
        this.$emit('editComment', command.data);
      } else {
        this.$emit('deleteComment', command.data);
      }
    },
    // 获取下拉菜单点击选项的值
    beforeHandleReply(sign, reply, commentId) {
      return {
        sign, reply, commentId
      };
    },
    replyManagement(command) {
      if (command.sign === 'deleteReply') {
        this.$emit('deleteReply', { replyId: command.reply, commentId: command.commentId });
      } else {
        this.$emit('editComment', command.reply, command.commentId);
      }
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
@import '@/assets/css/variable/mixin.scss';
@import '@/assets/css/pay_scss/kuq.scss';
@include custom-el-input-style();
/*IFTRUE_default*/
::v-deep .el-input .el-input__inner:focus {
  border-color: #409EFF;
}
.comment {
  padding-top: 20px;
  border-top: 1px solid $line-color-base;
  display: flex;
  flex-direction: column;
  width: 100%;

  &:first-child {
    border-top: none;
  }

  > .title {
    height: 36px;
    display: flex;
    justify-content: space-between;

    > .title-info {
      display: flex;
      ::v-deep .global .title-info {
        margin-left: 10px;
      }
      ::v-deep .global .title-info .author-name {
        font-size: 14px !important;
      }
      > .time {
        font-size: 12px;
        color: #8590A6;
        margin-left: 10px;
        line-height: 25px;
      }
    }

    > .management {
      margin-left: auto;
      font-size: 12px;
      display: flex;
      color: #8590A6;
      line-height: 25px;
      cursor: pointer;

      > .report {
        &:hover {
          color: #1878F3;
        }
      }

      > .filter-dropdown {
        margin-left: 20px;
        font-size: 12px;
        color: #8590A6;
        &:hover {
          color: #1878F3;
        }
      }


      > .checking {
        margin-left: 27px;
        color: #fa5151;
      }

      > .actions {
        margin-left: 27px;
        ::v-deep .text:hover {
          color: #1878F3;
        }
        > .like,.adoption {
          margin-right: 18px;
        }
        .adoption .circle{
          stroke: red;
        }
      }
    }
  }

  > .tip{
    display: flex;
    align-items: center;
    height: 30px;
    margin-left: 40px;
    font-size: 14px;
    color: #333;
    font-weight: bold;
    background: #fefbf5;
    > .tip-logo{
      width: 20px;
      height: 20px;
      margin-right: 5px;
    }
    > .tip-money{
      color: #d23a2a;
      padding: 0 2px;
    }
  }

  > .container-detail {
    margin-left: 40px;

    > .content-html {
      word-break: break-all;
      margin-bottom: 20px;
      font-size: 16px !important;

      ::v-deep p {
        font-size: 16px !important;
      }

      ::v-deep.showAllComment {
        font-size: 16px;
        color: #00479b;
        cursor: pointer;
      }
    }

    > .images {
      margin-top: 20px;
      width: 330px;
    }

  }

  > .reply-list {
    cursor: pointer;
    margin-left: 40px;


    > .reply {
      padding-top: 10px;

      &:first-child {
        border-top: 1px solid $border-color-base;
      }

      > .title {
        height: 36px;
        display: flex;

        > .title-info {
          margin-left: 10px;
          font-size: 14px;
          flex: 1;

          > .content-reply {
            font-size: 14px;
            line-height: 24px;
            white-space: nowrap;

            > .toReply {
              color: #8590A6;
            }

            > .replyUser {
              font-weight: bold;
            }
          }

          > .author-name {
            font-weight: bold;
          }

           > .management {
            float: right;
            font-size: 12px;
            display: flex;
            color: #8590A6;
            line-height: 25px;
            cursor: pointer;

            > .report {
              &:hover {
                color: #1878F3;
              }
            }

            > .filter-dropdown {
              margin-left: 20px;
              font-size: 12px;
              color: #8590A6;
              &:hover {
                color: #1878F3;
              }
            }

            > .checking {
              color: #fa5151;
            }

            > .actions {
              margin-left: 27px;
              ::v-deep .text:hover {
                color: #1878F3;
              }
              > .like {
                margin-right: 18px;
              }
            }
          }

          > .timer {
            margin: 5px 0px 0px 10px;
            color: $font-color-grey;
            font-size: 12px;
          }
        }
      }

      &:last-child {
        > .content {
          border-bottom: none;
        }
      }

      > .content-html {
        font-size: 16px;
        margin: 0px 0px 20px 40px;
        word-break: break-all;
      }

      > .images {
        max-width: 330px;
        margin-left: 40px;
        margin-bottom: 20px;
      }

      > .line {
        margin-left: 40px;
        background: $line-color-base;
        height: 1px;
      }
    }

    .show-all-reply {
      cursor: pointer;
      color: $font-color-grey;
      margin: 20px 0 20px 40px;
    }
  }
}
.adoption-box{
  position: fixed;
  left: 0;
  top: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0,0,0,.2);
  z-index: 99;
  .adoption-content{
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    margin: auto;
    width: 620px;
    height: 280px;
    font-size: 16px;
    color: #333;
    border-radius: 10px;
    background: #fff;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    .adoption-head{
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      line-height: 50px;
      .svg-icon-close {
        font-size: 14px;
        fill: #6d6d6d;
        cursor: pointer;
      }
    }
    .percent,.money{
      display: flex;
      align-items: center;
      padding: 0 20px;
    }
    .text{
      flex: 0 0 120px;
    }
    .reward-percent{
      height: 40px;
      width: 400px;
      margin-right: 20px;
    }
    .reward-money{
      height: 40px;
      width: 400px;
      line-height: 40px;
      padding: 0 15px;
      background: #f8f8f8;
      border: 1px solid  #dec0e6 !important;
      border-radius: 4px;
    }
    .btn{
      display: flex;
      justify-content: flex-end;
      align-items: center;
      height: 50px;
      padding: 0 20px;
      background: #f4f7f7;
      .el-button{
        padding: 10px 20px;
        margin-right: 20px;
        border-radius: 0 !important;
      }
      .btn-confirm{
        color: #fff ;
        background: #1878f3;
        border-color: transparent !important;
      }
      .btn-cancle{
        color: #333 ;
        background: #fff;
        border-color: #dec0e6 !important;
        &:hover{
          border: 1px solid #d4e6fc;
          background: #e5f2ff !important
        }
      }
    }
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
@import '@/assets/css/variable/color_pay.scss';
@import "@/assets/css/pay_scss/CommentList.scss";
/*FITRUE_pay*/
</style>
