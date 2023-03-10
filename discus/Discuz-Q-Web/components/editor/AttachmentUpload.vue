<template>
  <div v-viewer class="container-upload">
    <div class="upload">
      <span class="attachment-list">{{ $t("post.attachmentList") }}</span>
      <span class="add-attachment" @click="onClick">{{
        addTips || $t("post.addAttachment")
      }}</span>
      <input
        id="upload"
        ref="uploadFile"
        :accept="accept"
        type="file"
        multiple
        @input="onInput"
      >
    </div>
    <div
      v-for="(file, index) in previewFiles"
      :key="index"
      :class="{ 'preview-item': true, deleted: file.deleted }"
    >
      <div class="container-item">
        <div class="info">
          <svg-icon
            :type="extensionValidate(file.name)"
            style="font-size: 18px; vertical-align: middle;"
          />
          <span
            :class="{ 'file-name': true, uploading: file.progress < 100 }"
          >{{ file.name }}</span>
        </div>
        <span
          class="size"
        >{{
          parseInt((file.size / 1024).toString()).toLocaleString()
        }}
          KB</span>
        <div
          v-show="file.progress < 100"
          class="progress"
          :style="{ width: file.progress + '%' }"
        />
      </div>
      <div class="remove">
        <svg-icon
          v-show="file.progress && file.progress === 100"
          type="delete"
          class="remove-icon"
          @click="removeItem(index)"
        />
      </div>
    </div>
  </div>
</template>

<script>
import handleError from '@/mixin/handleError';
import handleAttachmentError from '@/mixin/handleAttachmentError';
import { extensionList } from '@/constant/extensionList';
import uploader from '@/mixin/uploader';
import service from '@/api/request';

export default {
  name: 'AttachmentUpload',
  mixins: [handleError, handleAttachmentError, uploader],
  props: {
    action: {
      type: String,
      default: '',
      required: true
    },
    fileList: {
      type: Array,
      default: () => [],
      required: true
    },
    accept: {
      type: String,
      default: ''
    },
    limit: {
      type: Number,
      default: 9999
    },
    sizeLimit: {
      type: Number,
      default: 99999999999999999
    },
    onUpload: {
      type: Boolean,
      default: false
    },
    addTips: {
      type: String,
      default: ''
    },
    type: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      previewFiles: [],
      currentInput: ''
    };
  },
  computed: {
    input() {
      return process.client ? document.getElementById('upload') : '';
    },
    service() {
      return service;
    }
  },
  methods: {
    onClick() {
      const currentObj = this.$refs.uploadFile;
      this.currentInput = currentObj;
      this.uploaderFile(currentObj);
    },
    extensionValidate(fileName) {
      const extension = fileName.split('.')[fileName.split('.').length - 1];
      return extensionList.indexOf(extension.toUpperCase()) > 0
        ? extension.toUpperCase()
        : 'UNKNOWN';
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";

.container-upload {
  margin-top: 20px;

  > .preview-item {
    position: relative;
    margin-top: 5px;
    min-height: 35px;
    display: flex;
    &.deleted {
      transition: 1s all;
      transform: translateY(-100%);
      opacity: 0;
    }
    > .remove {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 16px;
      margin-left: 16px;
      > .remove-icon {
        cursor: pointer;
        font-size: 16px;
        fill: #6d6d6d;
        &:hover {
          fill: #1878f3;
        }
      }
    }
    > .container-item {
      flex: 1;
      padding: 5px 10px;
      border-radius: 3px;
      border: 1px solid #ededed;
      display: flex;
      justify-content: space-between;
      align-items: center;
      height: 100%;
      background: #f4f5f6;
      .info {
        display: flex;
        align-items: center;
        z-index: 10;
        > .file-name {
          width: 580px;
          z-index: 10;
          margin-left: 8px;
          font-size: 16px;
          color: #333;
          white-space: nowrap;
          text-overflow: ellipsis;
          overflow: hidden;
          &.uploading {
            color: #8590a6;
          }
        }
      }
      > .size {
        font-size: 12px;
        color: #b7b7b7;
        z-index: 10;
      }
      > .progress {
        position: absolute;
        top: 0;
        left: 0;
        background: rgba(221, 232, 246);
        height: 100%;
      }
    }
  }
  /*IFTRUE_default*/
  > .upload {
    margin-bottom: 10px;
    span {
      font-size: 14px;
    }
    .attachment-list {
      color: #6d6d6d;
      font-weight: bold;
    }
    .add-attachment {
      cursor: pointer;
      margin-left: 10px;
      color: $color-blue-base;
    }
    > #upload {
      display: none;
    }
  }
  /*FITRUE_default*/
  /*IFTRUE_pay*/
  > .upload {
    font-size: 0;
    font-weight: normal;
    margin-top: 16px;
    margin-bottom: 30px;
    color: #333;
    > .attachment-list{
      display: inline-block;
      width: 90px;
      font-size: 16px;
    }
    > .add-attachment {
      font-size: 14px;
      color: #1878f3;
      padding: 12px 20px;
      border: 1px solid rgba(61, 93, 234,.2);
      border-radius: 4px;
      cursor: pointer;
    }
    > #upload {
      display: none;
    }
  }
  /*FITRUE_pay*/
}
</style>
