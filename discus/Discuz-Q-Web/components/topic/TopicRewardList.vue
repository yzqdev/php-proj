<template>
  <div class="global">
    <div
      v-for="(userList, usersIndex) in userLists"
      :key="usersIndex"
      class="group"
    >

      <div v-if="usersIndex === 0 && paidCount > 0" class="title">
        {{
          paidCount +
            $t("topic.personUnit") +
            $t(`topic.${command[usersIndex].title}`)
        }}
      </div>
      <div v-else-if="usersIndex === 1 && rewardedCount > 0 && canReward" class="title">
        {{
          rewardedCount +
            $t("topic.personUnit") +
            $t(`topic.${command[usersIndex].title}`)
        }}
      </div>
      <div v-else-if="usersIndex > 1 && userList.length > 0" class="title">
        {{
          userList.length +
            $t("topic.personUnit") +
            $t(`topic.${command[usersIndex].title}`)
        }}
      </div>
      <div v-if="userList.length > 0" class="person-list">
        <div class="first-show">
          <template
            v-for="(user, index) in userList.slice(
              0,
              command[usersIndex].value
            )"
          >
            /*IFTRUE_default*/
            <Avatar
              :key="index"
              class="container-avatar"
              size="30"
              round
              :user="user"
            />
            /*FITRUE_default*/
            /*IFTRUE_pay*/
            <Avatar
              :key="index"
              class="container-avatar"
              size="35"
              round
              :user="user"
            />
            /*FITRUE_pay*/
          </template>
        </div>
      </div>
      <div
        v-if="userList.length > 24"
        :class="{ rotate: command[usersIndex].value > userList.length }"
        @click="showMore(command[usersIndex], userList.length)"
      >
        <svg-icon
          style="font-size: 20px; margin-top: 10px; fill: #8590A6"
          type="drop-down"
        />
      </div>
      <!--商品帖、问答帖、红包帖 不支持打赏-->
      <button
        v-if="
          command[usersIndex].showButton &&
            command[usersIndex].buttonText &&
            threadType !== 5 &&
            threadType !== 6
        "
        @click="onClick(command, usersIndex)"
      >
        <svg-icon
          style="font-size: 16px; fill: white; margin-right: 10px"
          :type="command[usersIndex].icon"
        />
        <span>{{ command[usersIndex].buttonText }}</span>
      </button>
    </div>
  </div>
</template>

<script>
import isLogin from '@/mixin/isLogin';
export default {
  name: 'TopicRewardList',
  mixins: [isLogin],
  props: {
    paidCount: {
      type: Number,
      default: 0
    },
    rewardedCount: {
      type: Number,
      default: 0
    },
    author: {
      type: Object,
      default: () => {}
    },
    userLists: {
      type: Array,
      default: () => []
    },
    paidInformation: {
      type: Object,
      default: () => {}
    },
    threadType: {
      type: Number,
      default: 0
    },
    canReward: {
      type: Boolean,
      default: false
    },
    canPaid: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      payButtonText: [
        '',
        this.$t('topic.paymentViewRemainingContent'),
        this.$t('topic.paymentViewVideo'),
        this.$t('topic.paymentViewPicture'),
        this.$t('topic.paymentViewAudio')
      ],
      command: [
        {
          value: 24, // value 是每次加载的头像数量
          title: 'pay', // 支付文章
          buttonText: this.$t('topic.pay'),
          showButton: false,
          icon: 'pay'
        },
        {
          value: 24,
          title: 'reward',
          buttonText: this.$t('topic.reward'),
          showButton: false,
          icon: 'heart'
        },
        {
          value: 24,
          title: 'like'
        },
        {
          value: 24,
          title: 'pay', // 支付付费附件
          buttonText: this.$t('topic.pay'),
          showButton: false,
          icon: 'pay'
        }
      ]
    };
  },
  watch: {
    paidInformation: {
      handler(val) {
        this.command[0].showButton
          = parseFloat(val.price) > 0 && !val.paid && this.canPaid; // 支付
        this.command[3].showButton
          = parseFloat(val.attachmentPrice) > 0
          && !val.isPaidAttachment
          && this.canPaid; // 支付附件
        this.command[1].showButton
          = parseFloat(val.price) === 0
          && parseFloat(val.attachmentPrice) === 0
          && this.canReward; // 打赏

        this.command[3].buttonText
          = this.$t('topic.pay')
          + this.paidInformation.attachmentPrice
          + this.$t('topic.paymentViewAttachment');
      },
      deep: true
    },
    threadType(val) {
      this.command[0].buttonText
        = this.$t('topic.pay')
        + this.paidInformation.price
        + this.payButtonText[val];
    }
  },
  methods: {
    showMore(obj, length) {
      obj.value < length ? (obj.value += 24) : (obj.value = 24);
    },
    onClick(command, usersIndex) {
      if (!this.isLogin()) return;
      const userId = this.$store.state.user.info.id;
      if (parseInt(userId) === parseInt(this.author.id)) {
        return this.$message.error(this.$t('topic.iCantRewardMyself'));
      }
      return this.$emit('payOrReward', command[usersIndex].title);
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_default*/
.global {
  > .group {
    display: flex;
    flex-direction: column;
    align-items: center;

    > .title {
      margin-top: 30px;
      color: $font-color-grey;
      font-size: 14px;
    }

    > .person-list {
      margin: 10px auto 0;
      min-height: 33px;
      width: 400px;

      > .first-show {
        width: 100%;
        height: 100%;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;

        > .container-avatar {
          margin-right: 3px;
          margin-bottom: 5px;

          &:last-child {
            margin-right: 0;
          }
        }
      }
    }

    > .rotate {
      transform: rotate(180deg);
      transition: 0.5s;
    }

    > button {
      display: flex;
      align-items: center;
      margin-top: 30px;
      border-radius: 2px;
      height: 35px;
      padding: 0 35px;
      text-align: center;
      background: #fa5151;

      > span {
        color: #ffffff;
      }
    }
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
@import "@/assets/css/pay_scss/TopicRewardList.scss";
/*FITRUE_pay*/
</style>
