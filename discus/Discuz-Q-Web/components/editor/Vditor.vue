<template>
  <div
    v-loading="onUploadImage"
    element-loading-background="hsla(0,0%,100%,0.6)"
    class="vditor-container"
  >
    <div class="sticky-box">
      <topic-list
        v-show="showTopic"
        class="action-vditor"
        @selectedTopic="selectActions"
      />
      <emoji-list
        v-show="showEmoji"
        class="action-vditor"
        @selectEmoji="selectActions"
      />
    </div>
    <div id="vditor" />
    <span v-if="textLimit" class="tip">{{
      textLimit >= textLength
        ? $t("post.note", { num: textLimit - textLength })
        : $t("post.exceed", { num: textLength - textLimit })
    }}</span>
    <caller
      v-if="showCaller"
      @close="$emit('close')"
      @selectedCaller="selectActions"
    />
    <Fire
      v-if="showFire"
      :on-fire.sync="showFire"
      @close="showFire=false"
      @inedit="inedit"
    />
    <div class="publish-container">
      <el-button
        class="btn"
        :loading="onPublish"
        type="primary"
        size="small"
        @click="publish"
      >{{ $t("post.post") }}</el-button>
      <el-button
        :disabled="isEdit"
        class="btn"
        type="primary"
        size="small"
        @click="savePublish"
      >保存至草稿箱</el-button>
    </div>
  </div>
</template>

<script>
const Vditor = process.client ? require('vditor') : '';
import { call, emoji, topic, picture, fire  } from '@/assets/svg-icons/vditor-icon';
import service from '@/api/request';
import handleError from '@/mixin/handleError';

export default {
  name: 'Vditor',
  mixins: [handleError],
  props: {
    showCaller: {
      type: Boolean,
      default: false
    },
    showTopic: {
      type: Boolean,
      default: false
    },
    showEmoji: {
      type: Boolean,
      default: false
    },
    text: {
      type: String,
      default: ''
    },
    textLimit: {
      type: [Number, String],
      default: 49999
    },
    textLength: {
      type: [Number, String],
      default: 0
    },
    isEdit: {
      type: Boolean,
      default: false
    },
    isDraft: {
      type: Boolean,
      default: false
    },
    attachmentSizeLimit: {
      type: Number,
      default: 999999999999999999999
    },
    imageAccept: {
      type: String,
      default: ''
    },
    onUploadImage: {
      type: Boolean,
      default: false
    },
    placeholder: {
      type: String,
      default: ''
    },
    onPublish: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      vditor: {},
      input: {},
      range: [],
      showFire: false,
      afterVditorInit: false
    };
  },
  watch: {
    isDraft: {
      handler(isDraft) {
        if (isDraft && this.text) this.setDefaultValue();
      },
      immediate: true
    },
    isEdit: {
      handler(isEdit) {
        if (isEdit && this.text) this.setDefaultValue();
      },
      immediate: true
    }
  },
  mounted() {
    Vditor && this.initVditor();
  },
  methods: {
    initVditor() {
      this.vditor = new Vditor('vditor', {
        minHeight: 450,
        placeholder: this.placeholder,
        mode: 'wysiwyg',
        tab: '    ',
        input: value => {
          this.$emit('textChange', value);
        },
        toolbar: [
          {
            hotkey: '',
            name: '@',
            tipPosition: 'ne',
            tip: '@ 好友',
            className: 'right',
            icon: call,
            click: () => {
              this.range = getSelection().getRangeAt(0);
              this.$emit('onActions', 'showCaller');
            }
          },
          {
            hotkey: '',
            name: '#',
            tipPosition: 'ne',
            tip: '新增话题',
            className: 'right',
            icon: topic,
            click: () => {
              this.range = getSelection().getRangeAt(0);
              this.$emit('onActions', 'showTopic');
            }
          },
          {
            hotkey: '',
            name: 'my-emoji',
            tipPosition: 'ne',
            tip: '插入表情',
            className: 'right',
            icon: emoji,
            click: () => {
              this.range = getSelection().getRangeAt(0);
              this.$emit('onActions', 'showEmoji');
            }
          },
          {
            hotkey: '',
            name: 'fire',
            tipPosition: 'ne',
            tip: '小工具',
            className: 'right',
            icon: fire,
            click: () => {
              this.showFire = true;
            }
          },
          'headings',
          'bold',
          'italic',
          'strike',
          'link',
          'list',
          'ordered-list',
          'check',
          'outdent',
          'indent',
          'quote',
          {
            hotkey: '',
            name: 'picture',
            tipPosition: 'ne',
            tip: '插入图片',
            className: 'right',
            icon: picture,
            click: () => {
              this.uploader();
            }
          },
          'line',
          'code',
          'inline-code',
          'table',
          'both',
          'br',
          'undo',
          'redo'
        ],
        toolbarConfig: { pin: true },
        cache: { enable: false },
        after: () => {
          this.afterVditorInit = true;
        }
      });
    },
    inedit(v) {
      const key = v.key;
      let value = v.value;
      console.log(key);
      console.log(value);
      switch (key) {
        case 'pic':
          if (!/\.(gif|jpg|jpeg|png|GIF|JPG|PNG)$/.test(value)) {
            return this.$message.warning('请输入正确的图片地址');
          }
          value = `<img src="${value}">`;
          // eslint-disable-next-line no-case-declarations
          const markdown = this.vditor.html2md(value);
          this.vditor.insertValue(markdown.substr(0, markdown.length - 1));
          break;
        case 'audio':
          // if (!/tts/.test(value)) {
          //   if (!/\.(wav|mp3|m4a)$/.test(value)) {
          //     return this.$message.warning('请输入正确的音频地址');
          //   }
          // }

          value = `[audio url="${value}"/]`;
          this.vditor.insertValue(value);
          break;
        case 'video':
          // if (!/\.(mp4)$/.test(value)) {
          //   return this.$message.warning('请输入正确的视频地址');
          // }
          value = `[dplay url="${value}"/]`;
          this.vditor.insertValue(value);
          break;
        case 'recorder':
          // eslint-disable-next-line no-case-declarations
          const file = new window.File([value.file], `${value.filename}.mp3`, { type: 'audio/mp3' });
          // this.uploadFile(file);
          // eslint-disable-next-line no-case-declarations
          const formData = new FormData();
          formData.append('type', 0); // image 1
          formData.append('file', file);
          this.$emit('update:onUploadImage', true);
          service.post('/attachments', formData).then((res) => {
            console.log(res.data.data.attributes.url);
            const audio = `[audio url="${res.data.data.attributes.url}"/]`;
            this.vditor.insertValue(audio);
            this.$emit('update:onUploadImage', false);
          }, (e) => {
            console.log(e);
          });
          break;
      }
      this.showFire = false;
    },
    uploader() {
      if (this.onUploadImage) {
        return this.$message.warning('请等待上传中的图片完成上传');
      }
      this.input = document.createElement('input');
      this.input.type = 'file';
      this.input.multiple = true;
      this.input.accept = this.imageAccept;
      this.input.dispatchEvent(new MouseEvent('click'));
      this.input.oninput = e => {
        const files = e.target.files;
        const fileArray = [];
        // if (this.onUploadImage) return this.$message.warning('请等待上传中的图片完成上传')
        if (!this.checkSizeLimit(files)) return; // 文件大小检查
        for (let i = 0; i < files.length; i++) {
          fileArray.push(files[i]);
        }
        const promiseList = fileArray.reduce((result, file) => {
          result.push(this.uploadFile(file));
          return result;
        }, []);
        this.uploadFiles(promiseList);
      };
    },
    uploadFile(file) {
      const formData = new FormData();
      formData.append('type', 1); // image 1
      formData.append('file', file);
      return service.post('/attachments', formData);
    },
    uploadFiles(promiseList) {
      this.$emit('update:onUploadImage', true);
      Promise.all(promiseList)
        .then(
          resList => {
            const files = resList.map(item => item.data.data);
            files.forEach(item => {
              const name = encodeURIComponent(item.attributes.fileName);
              const html = `<img src="${item.attributes.url}" alt="${name}" title="${item.id}">`;
              const markdown = this.vditor.html2md(html);
              this.vditor.insertValue(markdown.substr(0, markdown.length - 1));
            });
            this.input.value = '';
          },
          e => {
            this.input.value = '';
            this.handleError(e).then(() => {});
          }
        )
        .finally(() => {
          this.$emit('update:onUploadImage', false);
        });
    },
    selectActions(code) {
      const selection = window.getSelection();
      selection.removeAllRanges();
      selection.addRange(this.range);
      this.vditor.insertValue(code);
      this.$emit('hideActions');
    },
    checkSizeLimit(files) {
      let pass = true;
      for (let i = 0; i < files.length; i++) {
        if (files[i].size > this.attachmentSizeLimit) pass = false;
      }
      if (!pass) {
        this.$message.error(
          `图片大小不可超过 ${this.attachmentSizeLimit / 1024 / 1024} MB`
        );
      }
      return pass;
    },
    publish() {
      const value = this.vditor.getValue();
      this.$emit('textChange', value);
      this.$emit('publish');
    },
    savePublish() {
      this.$emit('savePublish');
    },
    setDefaultValue() {
      if (this.afterVditorInit) {
        this.vditor.setValue(this.text, false);
      } else {
        setTimeout(this.setDefaultValue, 100);
      }
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/css/variable/color.scss';

.vditor-container {
  position: relative;
  margin-top: 20px;

  ::v-deep a {
    color: #1878f3;

    &:hover {
      border-bottom: 1px solid #1878f3;
    }
  }

  > .sticky-box {
    // 保证 topicList 和 emojiList 的定位正确
    position: sticky;
    background: rgba(0, 0, 0, 0);
    top: 65px;
    z-index: 1000;
    height: 1px;

    > .action-vditor {
      position: absolute;
      top: 38px;
      left: 2px;
    }
  }
  /*IFTRUE_default*/
  > .tip {
    position: absolute;
    color: #d0d4dc;
    top: 8px;
    z-index: 10;
    right: 10px;
  }
  > .publish-container {
    margin-top: 20px;
  }
  /*FITRUE_default*/
  /*IFTRUE_pay*/
  > .tip {
    position: absolute;
    color: #c5c6cb;
    bottom: 90px;
    right: 20px;
    z-index: 10;
  }
  > .button-publish {
    margin-top: 20px;
  }
  .publish-container{
    /*position: relative;*/
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 40px;
    padding-top: 20px;
    padding-bottom: 20px;
    font-size: 14px;
        position: sticky;
        bottom: 0;
        /*width: 100%;*/
        z-index: 12;
        background: #fff;
    .btn{
      padding: 12px 34px;
      margin: 0 12px;
      color: #333 !important;
      background: #eeeeee !important;
      border-radius: 20px !important;
      &:hover{
        color: #fff !important;
        background: #1878f3 !important;
      }
    }
  }
  /*FITRUE_pay*/
  ::v-deep.vditor-toolbar {
    /*background: #f5f6f7;
    top: 65px;
    padding-left: 10px !important;*/
    position: sticky;
    top: 100px;
    /*width: 100%;*/
    z-index: 12;
    padding-left: 10px !important;
    background: #f5f6f7;
  }

  ::v-deep.vditor-content pre {
    padding: 13px !important;
  }

}
</style>
