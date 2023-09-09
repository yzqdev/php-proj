<template>
  <div class="editor">
    <div v-if="userId && userId === 0" class="not-logged">
      <span>{{ $t("post.publishAfterLogin") }}</span>
    </div>
    <label v-if="typeInformation && typeInformation.showTitle">
      <input
        :placeholder="$t('post.pleaseInputPostTitle')"
        class="input-title"
        type="text"
        :value="post && post.title"
        @input="e => onPostContentChange('title', e.target.value)"
      >
    </label>

    <!-- 贴子支付 -->
    <editor-payment
      v-if="
        typeInformation
          && typeInformation.showPayment
          && canCreateThreadPaid
      "
      :payment="payment || {}"
      :type="typeInformation && typeInformation.type"
      :can-upload-attachments="canUploadAttachments"
      @paymentChange="e => onPaymentChange(e.key, e.value)"
    />
    <!-- 匿名 -->
    <div
      v-if="
        typeInformation
          && canPublishAnonymous
          && typeInformation.type !== 5
      "
      class="post-anonymous"
    >
      <div class="title">{{ $t("post.anonymous") }}：</div>
      <el-switch
        :value="post.isAnonymous"
        active-color="#1878F3"
        @input="value => onPostContentChange('isAnonymous', value)"
      />
    </div>
    <!-- 红包 -->
    <word-red-packet
      v-if="showRedPackage"
      :redpacket="redpacket"
      :is-edit="isEdit"
      @changeRedType="value => onRedPacketChange('is_red_packet', value)"
      @changeRedRule="value => onRedPacketChange('rule', value)"
      @changeRedMoney="value => onRedPacketChange('money', value)"
      @changeRedNumber="value => onRedPacketChange('number', value)"
      @changeRedCondition="value => onRedPacketChange('condition', value)"
      @changeRedlikenum="value => onRedPacketChange('likenum', value)"
    />
    <!--问答帖 -->
    <editor-qa
      v-if="typeInformation && typeInformation.type === 5"
      :post="post"
      :question="question"
      :can-publish-anonymous="canPublishAnonymous"
      @isAnonymousChange="value => onPostContentChange('isAnonymous', value)"
      @questionTypeChange="value => onQuestionChange('questionType', value)"
      @paymentTypeChange="value => onQuestionChange('paymentType', value)"
      @isOnlookerChange="value => onQuestionChange('isOnlooker', value)"
      @beUserChange="value => onQuestionChange('beUser', value)"
      @rewardPriceChange="value => onQuestionChange('price', value)"
      @rewardTimeChange="value => onQuestionChange('expired_at', value)"
    />
    <!--商品帖 -->
    <editor-product
      v-if="typeInformation && typeInformation.type === 6"
      :product="product"
      @productChange="onProductChange"
    />
    <attachment-upload
      v-if="isPost && canUploadAttachments"
      :file-list="post && post.attachedList"
      :on-upload.sync="onUploadAttached"
      action="/attachments"
      :accept="attachedTypeLimit"
      :limit="99999"
      :type="0"
      :size-limit="attachedSizeLimit"
      @success="files => onPostContentChange('attachedList', files)"
      @remove="files => onPostContentChange('attachedList', files)"
    />

    <!--海报图片 imageTypeLimit是图片格式。 onUploadImage是否开启，-->
    <!--limitImgNumber图片类型，imageTypeLimit允许上传图片的数量9-->
    <!--attachedSizeLimit大小限制-->
    <!--imageList数据-->
    <image-uploadhaibao
      v-if="type == 1"
      :type="5"
      :file-list="post && post.imageList"
      :on-upload.sync="onUploadImage"
      action="/attachments"
      :accept="imageTypeLimit"
      :limit="limitImgNumber"
      :size-limit="attachedSizeLimit"
      @success="imageList => onPostContentChange('imageList', imageList)"
      @remove="imageList => onPostContentChange('imageList', imageList)"
    />
    <Vditor
      v-if="isPost"
      :text-limit="typeInformation && typeInformation.textLimit"
      :text-length="textLength"
      :placeholder="typeInformation && typeInformation.placeholder"
      :image-accept="imageTypeLimit"
      :text="post && post.text"
      :show-caller="showCaller"
      :show-topic="showTopic"
      :show-emoji="showEmoji"
      :is-edit="isEdit"
      :is-draft="isDraft"
      :on-publish="onPublish"
      :attachment-size-limit="attachedSizeLimit"
      :on-upload-image.sync="onUploadImage"
      @textChange="value => onPostContentChange('text', value)"
      @close="showCaller = false"
      @hideActions="hideActions"
      @onActions="onActions"
      @publish="publish"
      @savePublish="savePublish"
    />
    <!--my editor 除了帖子以外，不使用 vditor-->
    <div v-if="!isPost" :class="['container-textarea', editorStyle]">
      <!--bar-->
      <editor-tool-bar
        v-if="editorStyle === 'post'"
        :text-limit="typeInformation && typeInformation.textLimit"
        :text-length="post && post.text && post.text.length"
        :actions="actions"
        :show-emoji="showEmoji"
        :show-topic="showTopic"
        :show-caller="showCaller"
        :resources="resources"
        :on-publish="onPublish"
        :editor-style="editorStyle"
        :is-edit="isEdit"
        :on-save="onSave"
        @publish="publish"
        @savePublish="savePublish"
        @selectActions="selectActions"
        @addResource="addResource"
        @hidePop="hideActions"
        @onActions="onActions"
      />
      <label>
        <textarea
          id="textarea"
          :value="post && post.text"
          :class="['input-text', editorStyle]"
          :placeholder="typeInformation ? typeInformation.placeholder : ''"
          :maxlength="post && post.textLimit"
          @input="e => onPostContentChange('text', e.target.value)"
        />
      </label>
      <div>
        <!--uploader type 15 是回答图片-->
        <div v-if="showUploadImg || showUploadVideo" class="resources-list">
          <image-upload
            v-if="showUploadImg"
            :type="attachmentsType"
            :file-list="post && post.imageList"
            :on-upload.sync="onUploadImage"
            action="/attachments"
            :accept="imageTypeLimit"
            :limit="limitImgNumber"
            :size-limit="attachedSizeLimit"
            @success="imageList => onPostContentChange('imageList', imageList)"
            @remove="imageList => onPostContentChange('imageList', imageList)"
          />
          <video-upload
            v-if="showUploadVideo"
            :on-upload-video.sync="onUploadVideo"
            :video-list="post && post.videoList"
            @videoChange="e => onPostContentChange(e.key, e.value)"
          />
        </div>
        <!--bar-->
        <editor-tool-bar
          v-if="editorStyle !== 'post'"
          :text-limit="(typeInformation && typeInformation.textLimit) || 450"
          :text-length="(post && post.text && post.text.length) || 0"
          :actions="actions"
          :show-emoji="showEmoji"
          :show-topic="showTopic"
          :show-caller="showCaller"
          :resources="resources"
          :on-publish="onPublish"
          :editor-style="editorStyle"
          :is-edit="isEdit"
          :on-save="onSave"
          @publish="publish"
          @savePublish="savePublish"
          @selectActions="selectActions"
          @addResource="addResource"
          @hidePop="hideActions"
          @onActions="onActions"
        />
      </div>
    </div>
    <caller
      v-if="showCaller && !isPost"
      @close="showCaller = false"
      @selectedCaller="selectActions"
    />
    <!-- 按钮 -->
    /*IFTRUE_pay*/
    <div v-if="editorStyle === 'post' && !isPost" class="publish-container">
      <span v-if="typeInformation && post" class="tip">
        {{
          typeInformation.textLimit >= post.text.length
            ? $t('post.note', { num: typeInformation.textLimit - post.text.length })
            : $t('post.exceed', { num: post.text.length - typeInformation.textLimit })
        }}
      </span>
      <el-button
        class="btn"
        :loading="onPublish"
        type="primary"
        size="small"
        @click="publish"
      >{{ $t('post.post') }}</el-button>
      <el-button
        :disabled="isEdit"
        class="btn"
        :loading="onSave"
        type="primary"
        size="small"
        @click="savePublish"
      >保存至草稿箱</el-button>
    </div>
    /*FITRUE_pay*/
  </div>
</template>

<script>
import handleError from '@/mixin/handleError';

export default {
  name: 'Editor',
  mixins: [handleError],
  props: {
    type: {
      type: String,
      default: ''
    },
    post: {
      type: Object,
      default: () => {}
    },
    redpacket: {
      type: Object,
      default: () => {}
    },
    onSave: {
      type: Boolean,
      default: false
    },
    payment: {
      type: Object,
      default: () => {}
    },
    question: {
      type: Object,
      default: () => {}
    },
    product: {
      type: Object,
      default: () => {}
    },
    typeInformation: {
      type: Object,
      default: () => {}
    },
    editResourceShow: {
      type: Object,
      default: () => {}
    },
    isEdit: {
      type: Boolean,
      default: false
    },
    isDraft: {
      type: Boolean,
      default: false
    },
    onPublish: {
      type: Boolean,
      default: false
    },
    editorStyle: {
      // post chat comment reply 代表不同的编辑器风格
      type: String,
      default: 'post'
    },
    selector: {
      // 当页面出现多个编辑器时，必须传入 selector (对应的 class name) 作为唯一的选择器，保证 textarea 元素选择正确
      type: String,
      default: 'editor'
    },
    limitImgNumber: {
      // 图片上传数量
      type: Number,
      default: 9
    }
  },
  data() {
    return {
      input: {},
      vditor: {},
      selectionStart: 0,
      selectionEnd: 0,
      // 是否显示弹窗
      showEmoji: false,
      showCaller: false,
      showTopic: false,
      showUploadImg: false,
      showUploadVideo: false,
      showUploadAttached: false,

      onUploadImage: false,
      onUploadVideo: false,
      onUploadAttached: false,

      listenerList: ['emoji-list', 'topic-list'],
      actions: [
        { icon: 'emoji', toggle: 'showEmoji', show: false },
        { icon: 'call', toggle: 'showCaller', show: false },
        { icon: 'topic', toggle: 'showTopic', show: false }
      ],
      resources: [
        { icon: 'picture', toggle: 'showUploadImg', show: false },
        { icon: 'video', toggle: 'showUploadVideo', show: false }
        // { icon: 'attached', toggle: 'showUploadAttached', show: false }
      ]
    };
  },
  computed: {
    userId() {
      return this.$store.state.session.userId;
    },
    url() {
      return '/api';
    },
    header() {
      if (process.client) {
        const token = window.localStorage.getItem('access_token');
        return { authorization: `Bearer ${token}` };
      }
      return {};
    },
    textarea() {
      return process.client
        ? document.querySelector(`.${this.selector} #textarea`)
        : '';
    },
    forums() {
      return this.$store.state.site.info.attributes || {};
    },
    canCreateThreadPaid() {
      return this.forums && this.forums.other
        ? this.forums.other.can_create_thread_paid
        : false;
    },
    canUploadAttachments() {
      return this.forums && this.forums.other
        ? this.forums.other.can_upload_attachments
        : false;
    },
    canCreateThreadRedPacket() {
      return this.forums && this.forums.other
        ? this.forums.other.can_create_thread_red_packet
        : false;
    },
    canCreateThreadLongRedPacket() {
      return this.forums && this.forums.other
        ? this.forums.other.can_create_thread_long_red_packet
        : false;
    },
    showRedPackage() {
      return ((this.type === '0' && this.canCreateThreadRedPacket)
        || (this.type === '1' && this.canCreateThreadLongRedPacket));
    },
    canPublishAnonymous() {
      let isAnonymous = false;
      if (this.forums && this.forums.other) {
        switch (this.type) {
          case '0':
            isAnonymous = this.forums.other.can_create_thread_anonymous;
            break;
          case '1':
            isAnonymous = this.forums.other.can_create_thread_long_anonymous;
            break;
          case '2':
            isAnonymous = this.forums.other.can_create_thread_video_anonymous;
            break;
          case '3':
            isAnonymous = this.forums.other.can_create_thread_image_anonymous;
            break;
          case '4':
            isAnonymous = this.forums.other.can_create_thread_audio_anonymous;
            break;
          case '5':
            isAnonymous = this.forums.other.can_create_thread_question_anonymous;
            break;
          case '6':
            isAnonymous = this.forums.other.can_create_thread_goods_anonymous;
            break;
        }
      }

      return isAnonymous;
    },
    attachedSizeLimit() {
      if (this.forums.set_attach) {
        return this.forums.set_attach.support_max_size * 1024 * 1024;
      }
      return 10485760; // 1024 * 1024 * 10 = 10M (保底值)
    },
    imageTypeLimit() {
      if (this.forums.set_attach) {
        const limitText = this.forums.set_attach.support_img_ext;
        return limitText
          .split(',')
          .map(item => `.${item}`)
          .join(',');
      }
      return '';
    },
    attachedTypeLimit() {
      if (this.forums.set_attach) {
        const limitText = this.forums.set_attach.support_file_ext;
        return limitText
          .split(',')
          .map(item => `.${item}`)
          .join(',');
      }
      return '';
    },
    isPost() {
      // 帖子类型 type 1
      return (this.typeInformation && this.typeInformation.type === 1) || false;
    },
    textLength() {
      if (this.post && this.post.text) {
        const mdText = this.post.text;
        const length = mdText.endsWith('\n') ? mdText.length - 1 : mdText.length;

        return length;
      }
      return 0;
    },
    attachmentsType() {
      if (this.editorStyle === 'chat') {
        return 4;
      }
      return this.typeInformation.type && this.typeInformation.type === 15 ? 5 : 1;
    }
  },
  watch: {
    editResourceShow: {
      handler(val) {
        if (!val) return;
        for (const key in val) this[key] = val[key];
      },
      deep: true,
      immediate: true
    },
    typeInformation: {
      handler(val) {
        if (!val) return;
        this.actions[0].show = val.showEmoji;
        this.actions[1].show = val.showCaller;
        this.actions[2].show = val.showTopic;
        this.resources[0].show = val.showImage;
        this.resources[1].show = val.showVideo;
        // this.resources[2].show = val.showAttached
      },
      deep: true,
      immediate: true
    },
    post: {
      handler(val) {
        if (
          this.editorStyle === 'chat'
            && !this.onUploadImage
            && val.imageList.length === 0
        ) {
          this.showUploadImg = false;
        }
      },
      deep: true,
      immediate: true
    }
  },
  mounted() {
    this.textarea && this.autoHeight();
    this.emojiListener();
  },
  methods: {
    // 红包贴选项，实时监听操作
    onRedPacketChange(key, value) {
      const _red = Object.assign({}, this.redpacket);
      _red[key] = +value;
      this.$emit(`update:redpacket`, _red);
    },
    onPostContentChange(key, value) {
      const _post = Object.assign({}, this.post);
      _post[key] = value;
      this.$emit(`update:post`, _post);
    },
    onPaymentChange(key, value) {
      const _payment = Object.assign({}, this.payment);
      _payment[key] = value;
      this.$emit(`update:payment`, _payment);
    },
    onQuestionChange(key, value) {
      const _question = Object.assign({}, this.question);
      _question[key] = value;
      this.$emit(`update:question`, _question);
    },
    onProductChange(product) {
      const _product = Object.assign({}, product);
      this.$emit(`update:product`, _product);
    },
    autoHeight() {
      if (this.editorStyle === 'chat') return; // 聊天框不需要自动高度
      // 编辑评论时需要改变内容才会触发自动高度，这里判断评论字数超过一定数量时给textarea一个初始高度以防高度不变导致内容无法展示
      if (this.post.text.length > 310) this.textarea.style.height = '180px';
      this.textarea.onkeyup = function() {
        // textarea 自动高度，通过 scrollHeight 赋值给 height
        // 当行数减少是 scrollHeight 只增不减，所以需要先把 height = auto
        // 但是会引起页面滚动条往上滚动，因为 textarea 高度瞬间减少了
        // 所以需要记录 oldScroll 重置滚动条
        const oldScroll = document.scrollingElement.scrollTop;
        this.style.height = 'auto';
        this.style.height = `${this.scrollHeight}px`;
        document.scrollingElement.scrollTop = oldScroll;
      };
    },
    getSelection() {
      this.selectionStart = this.textarea.selectionStart;
      this.selectionEnd = this.textarea.selectionEnd;
    },
    emojiListener() {
      document.addEventListener('click', e => {
        let pass = true;
        const path = e.path || (e.composedPath && e.composedPath());
        path.forEach(item => {
          if (item.classList) {
            if (
              item.classList.contains('emoji-list')
              || item.classList.contains('topic-list')
              || item.classList.contains('editor-bar')
            ) {
              pass = false;
            }
          }
        });
        if (pass) {
          this.showTopic = false;
          this.showEmoji = false;
        }
      });
    },
    onActions(toggle) {
      this.hideActions();
      setTimeout(() => {
        this[toggle] = !this[toggle];
      });
    },
    addResource(toggle) {
      if (this.editorStyle === 'chat') {
        if (this.onUploadImage || this.onUploadVideo) {
          return this.$message.warning('请等待资源上传');
        }
        this[toggle] = !this[toggle];
      } else {
        this[toggle] = true;
      }
    },
    selectActions(code) {
      this.getSelection();
      const _text
        = this.post.text.slice(0, this.selectionStart)
        + code
        + this.post.text.slice(this.selectionStart, this.post.text.length);
      this.onPostContentChange('text', _text);
      this.hideActions();
    },
    hideActions() {
      this.showEmoji = false;
      this.showTopic = false;
      this.showCaller = false;
    },
    savePublish() {
      if (this.onUploadAttached) {
        return this.$message.warning(
          this.$t('post.pleaseWaitForTheAttachedUploadToComplete')
        );
      }
      if (this.onUploadImage) {
        return this.$message.warning(
          this.$t('post.pleaseWaitForTheImageUploadToComplete')
        );
      }
      if (this.onUploadVideo) {
        return this.$message.warning(
          this.$t('post.pleaseWaitForTheVideoUploadToComplete')
        );
      }
      this.$emit('savePublish');
    },
    publish() {
      if (this.onUploadAttached) {
        return this.$message.warning(
          this.$t('post.pleaseWaitForTheAttachedUploadToComplete')
        );
      }
      if (this.onUploadImage) {
        return this.$message.warning(
          this.$t('post.pleaseWaitForTheImageUploadToComplete')
        );
      }
      if (this.onUploadVideo) {
        return this.$message.warning(
          this.$t('post.pleaseWaitForTheVideoUploadToComplete')
        );
      }
      this.$emit('update:onPublish', true);
      this.$emit('publish');
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
/*FITRUE_pay*/
/* editor placeholder */
::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.3);
  -webkit-border-radius: 100px;
}
::-webkit-scrollbar-thumb:active {
  font-family: inherit;
  background: rgba(0, 0, 0, 0.4);
  -webkit-border-radius: 100px;
}
::-webkit-input-placeholder {
  /* Chrome/Opera/Safari */
  font-family: inherit;
  font-size: 16px;
  color: #ddd;
}
::-moz-placeholder {
  /* Firefox 19+ */
  font-family: inherit;
  font-size: 16px;
  color: #ddd;
}
:-ms-input-placeholder {
  /* IE 10+ */
  font-family: inherit;
  font-size: 16px;
  color: #ddd;
}
:-moz-placeholder {
  /* Firefox 18- */
  font-family: inherit;
  font-size: 16px;
  color: #ddd;
}

.editor {
  width: 100%;
  margin-top: 20px;
  position: relative;

  > .not-logged {
    position: absolute;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background: rgba(255, 255, 255, 0.3);
    z-index: 1000000000;
    color: #8590a6;
    font-size: 20px;
    span {
      transform: translateY(-20px);
    }
  }
  /*IFTRUE_default*/
  label {
    width: 100%;

    > input {
      width: 100%;
      background: $background-color-editor;
    }

    > .input-title {
      height: 40px;
      padding: 0 15px;
      border-radius: 3px;
      border: 1px solid $border-color-base;
    }

    > .input-text {
      font-family: inherit;
      background: $background-color-editor;
      width: 100%;
      border: none;
      display: block;
      position: relative;
      resize: none;
      line-height: 16px;
      padding: 15px 15px 20px;
      overflow: hidden;
      overscroll-behavior: contain;
      transition: all 100ms linear;
      &.post {
        min-height: 200px;
      }
      &.comment {
        min-height: 115px;
      }
      &.reply {
        min-height: 80px;
      }
      &.chat {
        height: 120px;
        background: #fff;
        overflow: auto;
      }
    }
  }
  .post-anonymous{
    display: flex;
    align-items: center;
    padding: 20px 0;
    .title {
      font-size: 14px;
      font-weight: bold;
      color: #6d6d6d;
    }
  }
  /*FITRUE_default*/
  /*IFTRUE_pay*/
  label {
    width: 100%;

    > input {
      width: 100%;
      background: #fff;
    }

    > .input-title {
      height: 40px;
      padding: 0 15px;
      border-radius: 3px;
      border: 1px solid #e6e6e6;
    }

    > .input-text {
      font-family: inherit;
      background: $color-white;
      width: 100%;
      border: none;
      display: block;
      position: relative;
      resize: none;
      line-height: 24px;
      font-size: 16px;
      padding: 15px 15px 20px;
      overflow: hidden;
      overscroll-behavior: contain;
      transition: all 100ms linear;
      &.post {
        min-height: 200px;
      }
      &.comment {
        min-height: 120px;
      }
      &.reply {
        min-height: 80px;
      }
      &.chat {
        height: 120px;
        background: #fff;
        overflow: auto;
      }
    }
  }
  .post-anonymous{
    display: flex;
    align-items: center;
    padding: 20px 0;
    .title {
      min-width: 90px;
      font-size: 16px;
      color: #333;
    }
    ::v-deep.el-switch{
      .el-switch__core{
        width: 48px !important;
        height: 24px !important;
        border-radius: 12px !important;
        &:after{
          top: 3px;
          left:3px;
        }
      }
      &.is-checked{
        .el-switch__core{
          border-color: $color-red-pay !important;
          background-color: $color-red-pay !important;
          &:after{
            left: 100%;
            margin-left: -20px
          }
        }
      }
    }
  }
  /*FITRUE_pay*/
  > .container-textarea {
    border: 1px solid $border-color-base;
    border-radius: 3px;
    position: relative;
    margin-top: 30px;
    &.reply {
      margin-top: 0;
      margin-left: 60px;
    }
    &.chat {
      margin-top: 0;
    }
    &.comment {
      margin-top: 0;
    }
  }

  .resources-list {
    background: $background-color-grey;
    padding: 20px 20px 30px;
  }
  /*IFTRUE_pay*/
  .publish-container{
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 40px;
    font-size: 14px;
    .btn{
      padding: 12px 34px;
      margin: 0 12px;
      color: #333 !important;
      background: #eeeeee !important;
      border-radius: 20px !important;
      transition: all .3s;
      &:hover{
        color: white !important;
        background: #1878f3 !important;
      }
    }
    .tip{
      position: absolute;
      top: -80px;
      right: 20px;
      color: #c5c6cb;
    }
  }
  /*FITRUE_pay*/
}
</style>
