<template>
  <div
    class="user-item-container"
    :class="{'simple': show === 'simple'}"
  >
    <div
      class="flex user-item"
      @click.stop="toUser"
    >
      /*IFTRUE_default*/
      <avatar
        :user="item"
        :size="show === 'simple' ? 43 : 45"
      />
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      <avatar
        :user="item"
        :size="show === 'simple' ? 40 : 45"
        :round="true"
      />
      /*FITRUE_pay*/
      <div class="info">
        <div class="flex">
          <span class="name text-hidden">{{ item.username }}</span>
          <span
            v-if="show === 'all' && item.groupName"
            class="role"
          >{{ item.groupName }}</span>
        </div>
        <div class="flex count">
          <div
            v-if="show === 'all'"
            class="count-item"
          >{{ $t('profile.topic') }} {{ item.threadCount || 0 }}</div>
          <div
            v-if="show === 'all'"
            class="count-item"
          >{{ $t('profile.following') }} {{ item.followCount || 0 }}</div>
          <div class="count-item">{{ $t('profile.followers') }} {{ item.fansCount || 0 }}</div>
        </div>
      </div>
    </div>
    <el-button
      v-if="isFollow"
      type="text"
      class="follow"
    >
      <!-- <svg-icon type="follow-heart" class="follow-icon" /> -->
      {{ $t('profile.following') }}
    </el-button>
  </div>
</template>
<script>
export default {
  name: 'UserItem',
  props: {
    item: {
      type: Object,
      default: () => { }
    },
    show: {
      type: String,
      default: 'all' // simple 显示简单版 all显示所有内容
    },
    isFollow: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {};
  },
  methods: {
    // 跳转详情
    toUser() {
      this.$router.push(`/user/${this.item.id}`);
    }
  }
};
</script>
<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
/*FITRUE_pay*/
.user-item-container {
  padding: 20px;
  border-bottom: 1px solid $line-color-base;
  display: flex;
  .flex {
    display: flex;
    align-items: center;
  }
  .user-item {
    flex: 1;
    cursor: pointer;
    margin-right: 10px;
  }
  .info {
    margin-left: 10px;
    .count {
      margin-top: 5px;
    }
    .name {
      max-width: 150px;
      display: inline-block;
      @media screen and (max-width: 1005px) {
        max-width: 80px;
      }
      &:hover{
        color: $color-blue-base;
      }
    }
    .role {
      color: #8590a6;
      margin-left: 10px;
    }
    .count-item {
      color: #8590a6;
      margin-right: 20px;
      padding-right: 20px;
      border-right: 1px solid #efefef;
      &:last-child {
        margin-right: 0;
        padding-right: 0;
        border-right: none;
      }
    }
  }
  &.simple {
    padding: 10px 0;
    padding-bottom: 12px;
    .name {
      max-width: 120px;
      font-size: 16px;
    }
    .info .count {
      margin-top: 0;
    }
  }
  .follow {
    border: 1px solid #d0d4dc;
    padding: 0 14px;
    font-size: 12px;
    height: 25px;
    line-height: 25px;
    border-radius: 18px;
    cursor: pointer;
    color: #8590a6;
    .follow-icon {
      color: #ff8888;
      font-size: 13px;
    }
  }
}
</style>
