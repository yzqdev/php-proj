<template>
  <div class="qa">
    <!-- 问答类型 -->
    <div class="block">
      <div class="title">问答类型:</div>
      <el-radio
        :value="question.questionType"
        label="offerReward"
        @input="value => $emit('questionTypeChange', value)"
      >悬赏提问</el-radio>
      <el-radio
        :value="question.questionType"
        label="assigner"
        @input="value => $emit('questionTypeChange', value)"
      >指定人问答</el-radio>
    </div>
    <!-- 悬赏信息输入 -->
    <div v-show="question.questionType === 'offerReward'" class="reward-box block">
      <div class="title">悬赏金额：</div>
      <el-input
        v-model="question.price"
        type="number"
        placeholder="请输入悬赏金额"
        @change="value => $emit('rewardPriceChange', handleRewardPrice(value))"
      />
      <div class="title">悬赏结束时间：</div>
      <el-date-picker
        v-model="question.expired_at"
        type="datetime"
        :clearable="false"
        :picker-options="pickerOptionsBind"
        placeholder="选择日期时间"
        format="yyyy-MM-dd HH:mm:ss"
        @change="value => $emit('rewardTimeChange', checkTime(value))"
      />
    </div>
    <!-- 指定问答信息 -->
    <div v-show="question.questionType === 'assigner'" class="qa-payment block">
      <div class="title">{{ $t("post.questionMode") }}:</div>
      <el-radio
        :value="question.paymentType"
        label="free"
        @input="value => $emit('paymentTypeChange', value)"
      >{{ $t("post.freeWatch") }}</el-radio>
      <el-radio
        :value="question.paymentType"
        label="paid"
        @input="value => $emit('paymentTypeChange', value)"
      >{{ $t("post.paidWatch") }}</el-radio>
    </div>
    <div v-if="canPublishAnonymous" class="qa-anonymous block">
      <div class="title">{{ $t("post.anonymous") }}：</div>
      <el-switch
        :value="post.isAnonymous"
        active-color="#1878F3"
        @input="value => $emit('isAnonymousChange', value)"
      />
    </div>
    <div
      v-show="question.questionType === 'assigner' && canSetOnlooker"
      class="qa-onlooker block"
    >
      <div class="title">{{ $t("post.onLooker") }}:</div>
      <el-switch
        :value="question.isOnlooker"
        active-color="#1878F3"
        @input="value => $emit('isOnlookerChange', value)"
      />
      <span class="tip">
        <span>{{ `他人围观需付费 ${siteOnlookerPrice} 元。` }}</span>(
        <span v-show="siteUserScale" class="tip-con">
          {{ `你得 ${((siteOnlookerPrice * siteUserScale) / 2).toFixed(2)} 元
          ，回答者得 ${((siteOnlookerPrice * siteUserScale) / 2).toFixed(2)} 元。` }}
        </span>
        <span v-show="siteMasterScale" class="tip-con">
          {{ `平台得 ${(siteOnlookerPrice * siteMasterScale).toFixed(2)} 元` }}
        </span>)
      </span>
    </div>
    <div v-show="question.questionType === 'assigner'" class="qa-answerer block">
      <div class="title">
        <span>{{ $t("post.questionHim") }}:</span>
        <span class="select-answerer" @click="showQACaller = true">
          {{
            Object.keys(beAskedUser).length > 0
              ? $t("post.changeBeAskedUser")
              : $t("post.addBeAskedUser")
          }}
        </span>
      </div>
      <div v-show="Object.keys(beAskedUser).length > 0" class="be-asked-user">
        <avatar-component :author="beAskedUser" :size="50" round>
          {{ $t("topic.activeAt") }} {{ timerDiff(beAskedUser.updatedAt) }}{{ $t("topic.before") }}
        </avatar-component>
      </div>
    </div>
    <qa-caller
      v-if="showQACaller"
      @close="showQACaller = false"
      @selectedQaCaller="selectedQaCaller"
    />
  </div>
</template>

<script>
import timerDiff from '@/mixin/timerDiff';
import dayjs from 'dayjs';

export default {
  name: 'EditorQa',
  mixins: [timerDiff],
  props: {
    question: {
      type: Object,
      default: () => {}
    },
    post: {
      type: Object,
      default: () => {}
    },
    canPublishAnonymous: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      beAskedUser: {},
      showQACaller: false,
      pickerOptionsBind: {
        disabledDate(time) {
          return time.getTime() < Date.now();
        }
      }
    };
  },
  computed: {
    forums() {
      return this.$store.state.site.info.attributes || {};
    },
    canSetOnlooker() {
      return this.forums
        && this.forums.other
        && this.forums.other.can_be_onlooker
        ? this.forums.other.can_be_onlooker
        : false;
    },
    siteOnlookerPrice() {
      return this.forums && this.forums.set_site
        ? this.forums.set_site.site_onlooker_price
        : '';
    },
    siteMasterScale() {
      return this.forums && this.forums.set_site
        ? this.forums.set_site.site_master_scale / 10
        : '';
    },
    siteUserScale() {
      return this.siteMasterScale === '' ? 0 : 1 - this.siteMasterScale;
    }
  },
  watch: {
    question: {
      handler(val) {
        if (val.beUser) {
          this.beAskedUser = val.beUser;
        }
      },
      deep: true,
      immediate: true
    }
  },
  // TODO beAskedUser 回显
  mounted() {
    console.log(this.forums, 'forums');
    console.log('被提问信息', this.question);
    if (this.question.beUser) {
      this.beAskedUser = this.question.beUser;
    }
    // console.log(this.beAskedUser);
  },
  methods: {
    checkTime(value) {
      if (dayjs(value).isAfter(dayjs().add(1, 'day'))) {
        return value;
      } else {
        this.$message.warning('悬赏结束时间不能少于一天！');
        return '';
      }
    },
    handleRewardPrice(val) {
      if (/^(([1-9][0-9]*)|(([0]\.\d{0,2}|[1-9][0-9]*\.\d{0,2})))$/.test(val)) {
        return val;
      }
      if (val.length) {
        this.$message.warning('悬赏金额格式错误');
      }
      return '';
    },
    selectedQaCaller(user) {
      this.beAskedUser = user;
      this.showQACaller = false;
      this.$emit('beUserChange', user);
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
@import '@/assets/css/variable/mixin.scss';
@include custom-el-input-style();
/*IFTRUE_default*/
::v-deep .el-input .el-input__inner:focus {
  border-color: #409EFF;
}
.qa {
  > .block {
    margin-top: 20px;
    > .title {
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 10px;
      color: #6d6d6d;
      > .select-answerer {
        font-weight: normal;
        color: $color-blue-base;
        margin-left: 10px;
        cursor: pointer;
      }
    }
    > .be-asked-user {
      min-width: 230px;
      height: 70px;
      padding: 0 11px;
      display: inline-flex;
      align-items: center;
      background: #f4f5f6;
      border: 1px solid #ededed;
      border-radius: 2px;
    }
    > .tip {
      font-size: 14px;
      margin-left: 15px;
      color: #6d6d6d;
      .tip-con {
        color: #cccccc;
      }
    }
  }
  > .block.reward-box {
    display: flex;
    align-items: center;
    padding: 20px 0;
    > .title {
      margin-bottom: 0;
      font-size: 14px;
      color: #6d6d6d;
    }
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
@include custom-el-radio-style();
@import "@/assets/css/pay_scss/EditorQa.scss";
/*FITRUE_pay*/
</style>
