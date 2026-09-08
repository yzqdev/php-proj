<template>
  <div>
    <div v-for="(reply, index) in replyList" :key="index" class="reply-list">
      <div class="title">
        /*IFTRUE_default*/
        <div class="title-info">
          <avatar-component
            :author="reply.user"
            :size="30"
            :group-show="reply.commentPostId !== null && reply.commentUser ? true : false"
          />
          <div class="content-reply">
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
          </div>
          <div class="time">{{ formatDate(reply.updatedAt) }} <span v-if="reply.user">归属地 {{ reply.user.province }}</span></div>
        </div>
        <div class="management">
          <div v-if="reply.isApproved === 0" class="checking">
            {{ $t("topic.inReview") }}
          </div>
          <el-dropdown
            v-if="reply.canEdit || reply.canHide"
            size="medium"
            class="filter-dropdown"
            @command="commentManagement"
          >
            <span class="el-dropdown-link">
              {{ $t("topic.management") }}
            </span>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item
                v-if="reply.canEdit"
                :command="beforeHandleCommand('editComment', reply, index)"
              >
                {{ $t("topic.edit") }}
              </el-dropdown-item>
              <el-dropdown-item
                v-if="reply.canHide"
                :divided="true"
                :command="beforeHandleCommand('delete', reply._jv.id ,'reply')"
              >
                {{ $t("topic.delete") }}
              </el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
          <div v-show="reply.isApproved === 1" class="actions">
            <span
              v-if="reply.canLike"
              class="like"
              @click="$emit('onLike', { reply, index })"
            >
              <svg-icon
                :type="reply.isLiked ? 'liked' : 'like'"
                style="font-size: 18px"
              />
              <span class="text">
                {{ $t("topic.like") }}
                {{ reply.likeCount > 0 ? reply.likeCount : "" }}</span>
            </span>
            <span @click="$emit('reply', reply)">
              <span class="text">{{ $t("topic.replyTa") }}</span>
            </span>
          </div>
        </div>
        /*FITRUE_default*/
        /*IFTRUE_pay*/
        <div class="title-info">
          <avatar-component :author="reply.user" :size="45" :round="true" />
          <div class="content-reply">
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
          </div>
          <div class="time">
            {{ $t("topic.publishAt") }}
            {{ timerDiff(reply.updatedAt) + $t("topic.before") }}..
          </div>
        </div>
        <div class="management">
          <div v-if="reply.isApproved === 0" class="checking">
            {{ $t("topic.inReview") }}
          </div>
          <span
            v-if="reply.canEdit"
            class="manage-button"
            @click="commentManagement(beforeHandleCommand('editComment', reply, index))"
          >
            {{ $t("topic.edit") }}
          </span>
          <span
            v-if="reply.canHide"
            class="manage-button"
            @click="commentManagement(beforeHandleCommand('delete', reply._jv.id ,'reply'))"
          >
            {{ $t("topic.delete") }}
          </span>
        </div>
        /*FITRUE_pay*/
      </div>
      <div class="container-detail">
        <div
          class="content-html"
          v-html="$xss(formatHtml(reply.contentHtml))"
        />
        <div
          v-if="reply.images && reply.images.length > 0"
          v-viewer="{ url: 'data-source' }"
          class="images"
        >
          <el-image
            v-for="(image, imageIndex) in reply.images"
            :key="imageIndex"
            style="width: 100px; height: 100px;border-radius: 5px; margin-right: 10px"
            :src="image.thumbUrl"
            :alt="image.filename"
            :data-source="image.url"
            fit="cover"
          />
        </div>
        /*IFTRUE_pay*/
        <div v-show="reply.isApproved === 1" class="actions">
          <span
            v-if="reply.canLike"
            class="like"
            @click="$emit('onLike', { reply, index })"
          >
            <svg-icon
              :type="reply.isLiked ? 'liked' : 'like'"
              style="font-size: 18px"
            />
            <span class="text">
              {{ $t("topic.like") }}
              {{ reply.likeCount > 0 ? reply.likeCount : "" }}</span>
          </span>
          <span @click="$emit('reply', reply)">
            <svg-icon type="comment" style="font-size: 18px" />
            <span class="text">{{ $t("topic.replyTa") }}</span>
          </span>
        </div>
        /*FITRUE_pay*/
      </div>
    </div>
  </div>
</template>

<script>
import s9e from '@/utils/s9e';
import timerDiff from '@/mixin/timerDiff';
import dayjs from 'dayjs';
import stringToLink from '@/utils/stringToLink';

export default {
  name: 'ReplyList',
  mixins: [timerDiff],
  props: {
    replyList: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    formatHtml(html) {
      return html ? s9e.parse(stringToLink(html)) : '';
    },
    // 时间转换
    formatDate(date) {
      return dayjs(date).format('YYYY-MM-DD HH:mm');
    },
    toShield(commentId) {
      this.isShield = true;
      this.commentId = commentId;
    },
    beforeHandleCommand(sign, data, key) {
      return {
        sign, data, key
      };
    },
    // 评论管理
    commentManagement(command) {
      if (command.sign === 'delete') {
        this.$emit('delete', { id: command.data, type: command.key });
      } else {
        this.$emit('editComment', command.data, command.key);
      }
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
/*FITRUE_pay*/
.reply-list {
  padding-top: 20px;
  padding-bottom: 15px;
  border-top: 1px solid $border-color-base;
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
      /*IFTRUE_pay*/
      align-items: center;
      /*FITRUE_pay*/
      ::v-deep .global .title-info {
        margin-left: 10px;
      }
      ::v-deep .global .title-info .author-name {
        font-size: 14px !important;
        line-height: 24px;
      }
      > .time {
        font-size: 12px;
        color: #8590A6;
        margin-left: 10px;
        line-height: 25px;
      }

      > .content-reply {
        margin-left: 5px;
        display: flex;
        line-height: 24px;
        white-space: nowrap;
        font-size: 14px;

        > .toReply {
          color: #8590A6;
        }

        > .replyUser {
          margin-left: 5px;
          font-weight: bold;
        }
      }
    }

    > .management {
      font-size: 12px;
      display: flex;
      color: #8590A6;
      line-height: 25px;
      cursor: pointer;

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

      > .manage-button {
        margin-left: 10px;
        cursor: pointer;
        &:hover {
          color: #1878f3;
        }
      }

      > .actions {
        margin-left: 27px;
        > span {
          margin-left: 18px;
        }
        .text:hover {
          color: #1878F3;
        }
      }
    }
  }

  > .container-detail {
    /*IFTRUE_pay*/
    margin-left: 60px;
    /*FITRUE_pay*/
    /*IFTRUE_default*/
    margin-left: 40px;
    /*FITRUE_default*/
    > .content-html {
      line-height: 24px;
      word-break: break-all;
      font-size: 16px;
      ::v-deep p {
        font-size: 16px;
      }
    }

    > .images {
      margin-top: 20px;
      width: 330px;
    }

    > .actions {
      margin: 10px 0;
      width: 100%;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      color: $font-color-grey;
      font-size: 12px;

      > span {
        margin-left: 60px;
        cursor: pointer;
        &:hover {
          color: #1878f3;
        }

        > .text {
          margin-left: 5px;
        }
      }
    }
  }
}
</style>
