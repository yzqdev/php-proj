<template>
  <div class="content-item-container" @click="toContent">
    <div class="content-item-tips">
      <!-- 小图标 -->
      <img
        v-if="
          (item.type === 0 || item.type === 1)
            && item.isRedPacket
        "
        src="../assets/pay_imgs/post_red.png"
        class="item-img"
      >
      <img
        v-if="item.type === 5
          && item.questionTypeAndMoney
          && item.questionTypeAndMoney.type === 0"
        src="../assets/pay_imgs/post_reward.png"
        class="item-img"
      >
      <img
        v-else-if="item.type === 5"
        src="../assets/pay_imgs/post_question.png"
        class="item-img"
      >
      <span
        v-if="item.type === 5
          && item.questionTypeAndMoney
          && item.questionTypeAndMoney.type === 0"
        class="item-tip"
      >【￥{{ item.questionTypeAndMoney.money }}】</span>
    </div>
    <p v-if="item.type === 1" class="content">
      {{ handleContent(item.title) }}
    </p>
    <p v-else class="content">
      {{ handleContent(item.postContent) }}
    </p>
    <span class="read-count">
      {{ item.viewCount }}人已读
    </span>
  </div>
</template>

<script>
export default {
  name: 'ContentItem',
  props: {
    item: {
      type: Object,
      default: () => { }
    }
  },
  data() {
    return {
    };
  },
  methods: {
    // 跳转详情
    toContent() {
      this.$router.push(`/thread/${this.item._jv.id}`);
    },
    handleContent(content) {
      let con = content.replace(/:[a-z]+:/g, '').replace(/\s{3,}/g, ' ');

      if (this.item.type === 0) {
        con = con.slice(0, 18);
      } else if (this.item.type === 1) {
        con = con.slice(0, 20);
      } else if (this.item.type === 5) {
        con = con.slice(0, 10);
      }
      return con.length < 9 ? con : `${con}...`;
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color_pay.scss";
@import "@/assets/css/variable/mixin.scss";
.content-item-container{
  position: relative;
  margin-top: 10px;
  padding-bottom: 10px;
  font-size: 14px;
  border-bottom: 1px solid rgba(220,220,220,0.46);
  overflow: hidden;
  cursor: pointer;
    &:hover {
    background: $color-background-hover;
  }
  .content-item-tips{
    display: inline-flex;
    align-items: baseline;
    height: 40px;
    .item-img{
      align-self: flex-start;
      width: 55px;
      height:24px;
      margin-right: 8px;
      @media screen and (max-width: 1005px) {
        width: 50px;
        margin-right: 2px;
      }
    }
    .item-tip{
      font-size: 16px;
      font-weight: bold;
      color: #600111;
      margin-right: 3px;
      @media screen and (max-width: 1005px) {
        font-size: 14px;
      }
    }
  }
  .content{
    display: inline;
    line-height:40px;
    color: #000;
    word-break: break-all;
    word-wrap: break-word;
  }
  .read-count{
    position: absolute;
    right: 0;
    bottom: 3px;
    color: $font-color-grey;
    letter-spacing: 1px;
  }
}
</style>
