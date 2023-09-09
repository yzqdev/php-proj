<template>
  <div class="global">
    <Avatar :user="author" :size="size" :round="round" />
    <div class="title-info">
      <div class="author-name">
        <a
          :href="isAnonymous ? 'javascript:void(0)' : '/user/' + author.id"
          class="username"
        >{{ author.username }}</a>
        <span
          v-if="author.groups && author.groups[0] && author.groups[0].isDisplay && !groupShow"
          class="group"
        >{{ author.groups[0].isDisplay ? `（${author.groups[0].name}）` : "" }}</span>
      </div>
      <div class="timer">
        <slot />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AvatarComponent',
  props: {
    author: {
      type: Object,
      default: () => {}
    },
    size: {
      type: Number,
      default: 50
    },
    round: {
      type: Boolean,
      default: false
    },
    groupShow: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    isAnonymous() {
      return !this.author.id || parseInt(this.author.id) < 0;
    }
  }
};
</script>

<style lang="scss" scoped>
/*IFTRUE_default*/
@import "@/assets/css/variable/color.scss";
.global {
  display: flex;
  > .title-info {
    margin-left: 15px;

    .author-name {
      font-size: 16px;
      > .username {
        font-weight: bold;
      }
      > .group {
        font-weight: normal;
        white-space: nowrap;
        font-size: 12px;
        line-height: 20px;
        color: #8590a6;
      }
    }

    .timer {
      margin-top: 5px;
      color: $font-color-grey;
      font-size: 12px;
    }
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
@import "@/assets/css/pay_scss/avatarComponent.scss";
/*FITRUE_pay*/
</style>
