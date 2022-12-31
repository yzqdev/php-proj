<template>
  <div class="qa">
    <div v-if="showAnswer" class="block answer">
      <div class="header">
        <avatar-component :size="40" round :author="question.beUser || {}">
          {{ timerDiff(question.answeredAt) + $t("topic.before") }}.. 回答
        </avatar-component>
        <div v-show="question.onlookerNumber" class="counter">
          <span>{{ question.onlookerNumber + "人阅读" }}</span>
          <span
            v-show="question.onlookerNumber > 0"
          >, {{ question.onlookerNumber + "人已支付" }}</span>
        </div>
      </div>
      <div
        class="answer-text content-html"
        v-html="$xss(question.contentHtml)"
      />
      <div
        v-if="question.images && question.images.length > 0"
        v-viewer="{ url: 'data-source' }"
        class="images"
      >
        <el-image
          v-for="(image, index) in question.images"
          :key="index"
          class="image"
          :data-source="image.url"
          :src="image.thumbUrl"
          :alt="image.filename"
          fit="cover"
        >
          <div slot="placeholder" class="image-slot">
            <i class="el-icon-loading" />
          </div>
        </el-image>
      </div>
    </div>
    <div v-if="showAnswerButton" class="block answer-btn">
      <button @click="showAnswerEditor = true">
        <svg-icon type="question" style="font-size: 15px" />
        <span>{{ $t("topic.answerQuestion") }}</span>
      </button>
      <!-- <div v-show="parseFloat(question.price) > 0" class="tip"> -->
      <div class="tip">
        <span v-show="parseFloat(question.price) > 0">
          <span>{{ $t("topic.answerCanGet") }}</span>
          <span class="amount">
            ￥{{ question.price }}
          </span>
          <span>{{ $t("post.yuan") }}。</span>
        </span>
        <span v-show="question.isOnlooker && siteUserScale > 0">
          <span>{{ $t("topic.answerTip") }}</span>
          <span
            class="amount"
          >￥
            {{
              (
                (parseFloat(question.onlookerUnitPrice) * siteUserScale) /
                2
              ).toFixed(2)
            }}
          </span>
          <span>{{ $t("post.yuan") }}。</span>
        </span>
      </div>
    </div>
    <div v-if="showOnLookerButton" class="block looker-btn">
      <button @click="$emit('payForAnswer')">
        <svg-icon type="yuan" style="font-size: 15px" />
        <span>{{
          $t("topic.payAmountCanWatch", {
            amount: question.onlookerUnitPrice
          })
        }}</span>
      </button>
    </div>
    <el-dialog
      :title="$t('topic.answerQuestion')"
      :visible.sync="showAnswerEditor"
      destroy-on-close
      width="820px"
    >
      <editor
        editor-style="comment"
        class="edit-answer-editor"
        selector="edit-answer-editor"
        :edit-resource-show="editResourceShow"
        :on-publish="onAnswerPublish"
        :type-information="answerType"
        :post.sync="answer"
        @publish="answerPublish"
      />
    </el-dialog>
  </div>
</template>

<script>
import timerDiff from '@/mixin/timerDiff';
import publishResource from '@/mixin/publishResource';
import postLegalityCheck from '@/mixin/postLegalityCheck';
import handleError from '@/mixin/handleError';
import isLogin from '@/mixin/isLogin';

export default {
  name: 'QaActions',
  mixins: [timerDiff, publishResource, postLegalityCheck, handleError, isLogin],
  props: {
    question: {
      type: Object,
      default: () => {}
    },
    questionUserId: {
      type: [String, Number],
      default: ''
    },
    currentUserId: {
      type: [String, Number],
      default: ''
    }
  },
  data() {
    return {
      answerType: {
        type: 15,
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
      editResourceShow: {
        showUploadImg: false,
        showUploadVideo: false,
        showUploadAttached: false
      },
      answer: { text: '', imageList: [], attachedList: [] },
      onAnswerPublish: false,
      showAnswerEditor: false
    };
  },
  computed: {
    isQuestionUser() {
      return parseInt(this.questionUserId) === parseInt(this.currentUserId);
    },
    isBeAskedUser() {
      return (
        parseInt(this.question.beUserId) === parseInt(this.currentUserId)
      );
    },
    showAnswer() {
      if (this.question.isAnswer === 0) return false;
      else {
        return !(
          !this.isBeAskedUser
          && !this.isQuestionUser
          && this.question.onlookerState === '0'
          && this.question.onlookerPrice > 0
        );
      }
    },
    showAnswerButton() {
      return this.isBeAskedUser && this.question.isAnswer === 0;
    },
    showOnLookerButton() {
      return (
        !this.isBeAskedUser
        && !this.isQuestionUser
        && this.question.onlookerUnitPrice > 0
        && !this.question.onlookerState
        && this.question.isAnswer === 1
      );
    },
    forums() {
      return this.$store.state.site.info.attributes || {};
    },
    siteMasterScale() {
      return this.forums && this.forums.set_site
        ? this.forums.set_site.site_master_scale / 10
        : '';
    },
    siteUserScale() {
      return 1 - this.siteMasterScale;
    }
  },
  methods: {
    closeEditor() {
      this.answer.text = '';
      this.answer.imageList = [];
      this.showAnswerEditor = false;
    },
    answerPublish() {
      if (!this.isLogin()) return;
      if (this.onAnswerPublish) return;
      if (!this.answer.text) {
        return this.$message.warning(this.$t('post.theContentCanNotBeBlank'));
      }
      if (this.answer.text.length > this.answerType.textLimit) {
        return this.$message.warning(this.$t('post.messageLengthCannotOver'));
      }
      this.onAnswerPublish = true;
      const id = this.question.id;
      const answerParams = {
        _jv: {
          type: `answer`,
          links: { self: `questions/${id}/answer` },
          relationships: {
            attachments: {
              data: []
            }
          }
        },
        content: this.answer.text,
        type: 5
      };
      this.publishPostResource(answerParams, this.answer);
      return this.$store
        .dispatch(`jv/post`, [answerParams, { url: `/questions/${id}/answer` }])
        .then(
          response => {
            this.postLegalityCheck(
              response,
              this.$t('topic.answerPublishSuccess')
            );
            this.$emit('answerPublished');
            this.closeEditor();
          },
          e => this.handleError(e)
        )
        .finally(() => {
          this.onAnswerPublish = false;
        });
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
.qa {
  .editor-box {
    padding: 20px;
  }
  > .block {
    margin-top: 30px;
    display: block;
    text-align: center;
    &.answer {
      border-radius: 5px;
      padding: 15px 15px 30px;
      background: #f7f7f7;
      text-align: left;
      > .header {
        display: flex;
        justify-content: space-between;
        > .counter {
          color: #8590a6;
        }
      }
      > .answer-text {
        text-align: left;
        margin-top: 15px;
        font-size: 16px;
      }
      > .images {
        margin-top: 15px;
        text-align: left;
        width: 330px;
        > .image {
          cursor: pointer;
          width: 100px;
          height: 100px;
          margin-right: 10px;
          margin-bottom: 10px;
        }
      }
    }
    > .tip {
      margin-top: 10px;
      color: #8590a6;
      > .amount {
        color: black;
      }
    }
    > button {
      display: flex;
      align-items: center;
      margin: 0 auto;
      height: 30px;
      padding: 0 20px;
      background: #fa5151;
      color: white;
      line-height: 35px;
      border: none;
      border-radius: 2px;
      > span {
        margin-left: 8px;
      }
    }
  }
  /*IFTRUE_pay*/
  .block.answer-btn,
  .block.looker-btn{
    padding: 40px 0;
    background: #fefbf5;
     > button {
      height: 54px;
      line-height: 26px;
      padding: 14px 58px;
      font-size: 18px;
      color: white;
      background: #1878f3;
      border: none;
      border-radius: 27px;
    }
  }
  /*FITRUE_pay*/
}
</style>
