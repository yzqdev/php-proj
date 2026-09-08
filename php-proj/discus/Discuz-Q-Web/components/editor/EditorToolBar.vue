<template>
  <div :class="['editor-bar', editorStyle]">
    <div class="block">
      <template v-for="(action, index) in actions">
        <popover
          v-if="action.show && action.icon === 'emoji'"
          :key="index"
          :visible="showEmoji"
          class="svg"
          @hidePop="e => $emit('hidePop', e)"
        >
          <template v-slot:pop>
            <emoji-list @selectEmoji="e => $emit('selectActions', e)" />
          </template>
          <template
            v-slot:activeNode
          ><svg-icon
            :type="action.icon"
            style="font-size: 20px"
            @click="$emit('onActions', action.toggle)"
          /></template>
        </popover>
        <popover
          v-if="action.show && action.icon === 'topic'"
          :key="index"
          :visible="showTopic"
          class="svg"
          @hidePop="e => $emit('hidePop', e)"
        >
          <template v-slot:pop>
            <topic-list @selectedTopic="e => $emit('selectActions', e)" />
          </template>
          <template v-slot:activeNode>
            <svg-icon
              :type="action.icon"
              style="font-size: 20px"
              @click="$emit('onActions', action.toggle)"
            />
          </template>
        </popover>
        <svg-icon
          v-else-if="action.show && action.icon === 'call'"
          :key="index"
          :type="action.icon"
          class="svg"
          style="font-size: 20px"
          @click="$emit('onActions', action.toggle)"
        />
      </template>
    </div>
    <div class="block">
      <template v-for="(resource, index) in resources">
        <svg-icon
          v-if="resource.show"
          :key="index"
          :type="resource.icon"
          class="svg"
          style="font-size: 20px"
          @click="$emit('addResource', resource.toggle)"
        />
      </template>
    </div>
    /*IFTRUE_default*/
    <div class="publish-container">
      <span v-if="textLimit" class="tip">{{
        textLimit >= textLength
          ? $t("post.note", { num: textLimit - textLength })
          : $t("post.exceed", { num: textLength - textLimit })
      }}</span>
      <el-button
        class="button-publish"
        :loading="onPublish"
        type="primary"
        size="small"
        @click="$emit('publish')"
      >{{ $t("post.post") }}</el-button>
      <el-button
        v-if="editorStyle === 'post'"
        :disabled="isEdit"
        class="button-publish"
        :loading="onSave"
        type="primary"
        size="small"
        @click="$emit('savePublish')"
      >保存至草稿箱</el-button>
    </div>
    /*FITRUE_default*/
    /*IFTRUE_pay*/
    <div v-if="editorStyle !== 'post'" class="publish-container">
      <span v-if="textLimit" class="tip">{{
        textLimit >= textLength
          ? $t('post.note', { num: textLimit - textLength })
          : $t('post.exceed', { num: textLength - textLimit })
      }}</span>
      <el-button
        class="button-publish"
        :loading="onPublish"
        type="primary"
        size="small"
        @click="$emit('publish')"
      >{{ $t('post.post') }}</el-button>
    </div>
    /*FITRUE_pay*/
  </div>
</template>

<script>
export default {
  name: 'EditorToolBar',
  props: {
    actions: {
      type: Array,
      default: () => []
    },
    showEmoji: {
      type: Boolean,
      default: false
    },
    showTopic: {
      type: Boolean,
      default: false
    },
    resources: {
      type: Array,
      default: () => []
    },
    textLimit: {
      type: [Number, String],
      default: 450
    },
    textLength: {
      type: [Number, String],
      default: 0
    },
    onPublish: {
      type: Boolean,
      default: false
    },
    editorStyle: {
      type: String,
      default: 'post'
    },
    isEdit: {
      type: Boolean,
      default: false
    },
    onSave: {
      type: Boolean,
      default: false
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";

.editor-bar {
  width: 100%;
  height: 45px;
  display: flex;
  padding: 0 10px;
  align-items: center;
  background: #ffffff;
  /*IFTRUE_pay*/
  height: 60px;
  background: #fafafb;
  /*FITRUE_pay*/
  position: sticky;
  top: 65px;
  z-index: 6;
  border-bottom: 1px solid $border-color-base;

  &.chat {
    background: $background-color-grey;
  }

  .block {
    display: flex;
    padding: 0 10px;

    &:first-child {
      border: 0;
    }

    .svg {
      cursor: pointer;
      margin-left: 20px;

      &:first-child {
        margin-left: 0;
      }
    }
  }
  /*IFTRUE_default*/
  .publish-container {
    margin-left: auto;
    > .button-publish {
      padding: 10px 15px;
      margin-left: auto;

      ::v-deep span {
        font-size: 14px;
      }
    }

    > .tip {
      margin-right: 10px;
      color: #d0d4dc;
    }
  }
  /*FITRUE_default*/
  /*IFTRUE_pay*/
  .publish-container {
    position: relative;
    margin-left: auto;
    > .button-publish {
      margin: 10px 26px;
      padding: 12px 28px;
      color: #fff !important;
      background:  #1878f3 !important;
      border-radius: 20px !important;
      ::v-deep span {
        font-size: 14px;
      }
    }

    > .tip {
      position: absolute;
      top: -40px;
      right: 20px;
      width: 200px;
      line-height: 14px;
      text-align: right;
      display: inline-block;
      color: #c5c6cb;
    }
  }
  /*FITRUE_pay*/
}
</style>
