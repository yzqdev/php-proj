<template>
  <div
    v-loading.fullscreen="defaultLoading"
    element-loading-background="rgba(0, 0, 0, 0)"
    class="page-post"
  >
    <main v-loading="articleLoading">
      <div class="container-post">
        <div v-if="thread.thread && thread.thread.isApproved === 0" class="checking">
          {{
            thread.threadVideo && thread.threadVideo.status === 0
              ? $t("topic.VideoTranscoding")
              : $t("topic.examineTip")
          }}
        </div>

        <topic-header
          :author="thread.author || {}"
          :thread="thread"
          :management-list="managementList"
          :be-asked-user="question && question.beUser ? question.beUser : {}"
          @managementSelected="postCommand"
        />
        <div v-if="thread.thread && thread.thread.isOriginal == true">
          <el-alert
            :title="'本文内容为“'+thread.author.username+'”原创，未经过同意禁止转载。'"
            type="success"
            show-icon>
          </el-alert>

        </div>
        <topic-content
          :article="article"
          :title="thread.thread && thread.thread.title || ''"
          :video="thread.threadVideo || {}"
          :audio="thread.threadAudio || {}"
          :vurl="article.vurl || ''"
          :bvid="article.bvid || ''"
          :suburl="article.suburl || ''"
          :wavurl="article.wavurl || ''"
          :tid="threadId"
          :paid-information="paidInformation"
          :thread-type="thread.thread && thread.thread.type || 0"
          :category="thread.category || {}"
          :location="location"
          :thread="thread"
          topic-type="thread"
          @payForThread="showCheckoutCounter = true"
        />
        <!-- 红包、悬赏描述信息 -->
        <div v-if="redPacket && redPacket.isRedPacket === 1" class="desc-text">
          该主题包含红包，
          <span>{{
            redPacket.condition === 1 ? `点赞${redPacket.likenum}个` : "回复"
          }}</span>
          即可瓜分，共<span>{{ redPacket.number }}</span>个，已领取
          <span>{{ redPacket.number - redPacket.remain_number }}</span>个，还剩 <span>{{ redPacket.remain_number }}</span>个，
          <span>分完即止。</span>
          /*IFTRUE_pay*/
          <img
            src="../../assets/pay_imgs/redpacket.png"
            alt="领红包"
            class="desc-logo"
          >
          /*FITRUE_pay*/
        </div>
        <div
          v-if="rewardQuestion && rewardQuestion.type === 0"
          class="desc-text"
        >
          悬赏于<span>{{ rewardQuestion.showTime }}</span>日截止， 已分配<span>{{
            (rewardQuestion.money - rewardQuestion.remainMoney).toFixed(2)
          }}</span>元， 剩下<span>{{ rewardQuestion.remainMoney }}</span>元。
        </div>
        <qa-actions
          v-if="thread.thread && thread.thread.type === 5 && question.type !== 0"
          :question="question || {}"
          :current-user-id="currentUser && currentUser.id ? currentUser.id : ''"
          :question-user-id="
            thread.author && thread.author.id ? thread.author.id : ''
          "
          @answerPublished="getThread()"
          @payForAnswer="showCheckoutCounter = true"
        />
        <topic-reward-list
          :author="thread.author || {}"
          :paid-count="thread.thread && thread.thread.paidCount || 0"
          :rewarded-count="thread.thread && thread.thread.rewardedCount || 0"
          :paid-information="paidInformation"
          :can-paid="forums && forums.paycenter && forums.paycenter.wxpay_close"
          :can-reward="
            forums &&
              forums.paycenter &&
              forums.paycenter.wxpay_close &&
              thread.thread &&
              thread.thread.canBeReward
          "
          :thread-type="thread.thread && thread.thread.type || 0"
          :user-lists="[
            thread.paidUsers || [],
            thread.thread && thread.thread.canBeReward ? (thread.rewardedUsers || []) : [],
            thread.likedUsers || [],
            [],
          ]"
          @payOrReward="showCheckoutCounter = true"
        />
        <topic-actions
          :thread-id="threadId"
          :actions="actions || []"
          @clickAction="postCommand"
        />
        <topic-checkout-counter
          v-if="showCheckoutCounter"
          :thread-type="thread.thread && thread.thread.type || 0"
          :user="thread.author || {}"
          :amount="payOrRewardAmount"
          :content="article.summary || ''"
          :reward-or-pay="rewardOrPay"
          :be-asked-user="question.beUser || {}"
          ask-or-watch-answer="watchAnswer"
          @close="showCheckoutCounter = false"
          @paying="paying"
        />
        <topic-password
          v-if="showPasswordInput"
          :price="payOrRewardAmount"
          :password-error.sync="passwordError"
          :password-error-tip="passwordErrorTip"
          @close="showPasswordInput = passwordError = false"
          @password="onPasswordInputCompleted"
          @findPassword="onFindPassword"
        />
        <topic-wx-pay
          v-if="showWxPay"
          :qr-code="payment.wechat_qrcode"
          @close="showWxPay = false"
        />
        <find-paypwd
          v-if="findPassword && currentUser.originalMobile"
          :mobile="currentUser.mobile"
          :phonenum="currentUser.originalMobile"
          @close="findPassword = false"
        />
        <!-- 找回密码，没绑定手机 -->
        <without-phone
          v-if="findPassword && !currentUser.originalMobile"
          @close="findPassword = false"
        />
        <div v-if="forums && forums.set_site && forums.set_site.site_pinglun == 1">
          <comment
            v-if="thread.thread && thread.thread.isPinglun"
            :types="thread.thread && thread.thread.type"
            :thread-id="threadId"
            :author="thread.author || {}"
            :current-user="currentUser"
            :reward-question="rewardQuestion"
          />
        </div>
      </div>
    </main>
    <topic-aside :author="thread.author || {}" />
  </div>
</template>

<script>
import handleError from '@/mixin/handleError';
import isLogin from '@/mixin/isLogin';
import payment from '@/mixin/payment';
import env from '@/utils/env';
import newApi from '@/api/new-api';

export default {
  name: 'Topic',
  layout: 'custom_layout',
  mixins: [handleError, isLogin, payment],
  async asyncData({ params, store }) {
    if (!env.isSpider) {
      return {};
    }
    try {
      const data = {
        pid: Number(params.id)
      };
      const threadData = await newApi.readThreadDetail({ params: data });
      return {
        thread: threadData,
        article: threadData.firstPost,
        postId: threadData.firstPost.id
      };
    } catch (error) {
      return { articleLoading: true };
    }
  },
  data() {
    return {
      thread: {},
      article: {},
      question: {},
      redPacket: {}, // 红包
      rewardQuestion: {}, // 悬赏问答
      timer: null, // 轮询定时器
      onUpdateRed: false, // 正请求红包
      onUpdateReward: false, // 正请求悬赏
      postId: 0,
      actions: [
        {
          text: this.$t('topic.read'),
          count: 0,
          command: '',
          canOpera: false,
          icon: 'book'
        },
        {
          text: this.$t('topic.getLike'),
          count: 0,
          command: 'isLiked',
          canOpera: false,
          isStatus: false,
          icon: 'like'
        },
        {
          text: this.$t('topic.collection'),
          command: 'isFavorite',
          canOpera: false,
          isStatus: false,
          icon: 'favor'
        },
        {
          text: this.$t('topic.share'),
          command: 'showLink',
          canOpera: true,
          icon: 'link'
        }
      ],
      paidInformation: {
        price: '0',
        freeWords: 0,
        paid: false,
        paidUsers: [],
        paidCount: 0,
        isPaidAttachment: false,
        attachmentPrice: '0'
      },
      payment: { wechat_qrcode: '', rewardAmount: '' },
      location: { location: '', latitude: '', longitude: '' },
      managementList: [
        {
          name: 'canEdit',
          command: 'toEdit',
          isStatus: false,
          canOpera: false,
          text: this.$t('topic.edit'),
          type: '0'
        },
        {
          name: 'canEssence',
          command: 'isEssence',
          isStatus: false,
          canOpera: false,
          text: this.$t('topic.essence'),
          type: '1'
        },
        {
          name: 'canSticky',
          command: 'isSticky',
          isStatus: false,
          canOpera: false,
          text: this.$t('topic.sticky'),
          type: '2'
        },
        {
          name: 'canPoster',
          command: 'isPoster',
          isStatus: false,
          canOpera: false,
          text: this.$t('topic.poster'),
          type: '4'
        },
        {
          name: 'canPinglun',
          command: 'isPinglun',
          isStatus: false,
          canOpera: false,
          text: this.$t('topic.cancelpinglun'),
          type: '5'
        },
        {
          name: 'canZhiding',
          command: 'isZhiding',
          isStatus: false,
          canOpera: false,
          text: this.$t('topic.shuaxinsticky'),
          type: '6'
        },
        /*{
          name: 'canOriginal',
          command: 'isOriginal',
          isStatus: false,
          canOpera: false,
          text: this.$t('topic.originalsticky'),
          type: '7'
        },*/
        {
          name: 'canEdit',
          names: 'canOriginal',
          command: 'isOriginal',
          isStatus: false,
          canOpera: false,
          text: this.$t('topic.original'),
          type: '8'
        },
        {
          name: 'canHide',
          command: 'isDeleted',
          isStatus: false,
          canOpera: false,
          text: this.$t('topic.delete'),
          type: '3'
        }
      ],
      showCheckoutCounter: false,
      showPasswordInput: false,
      showWxPay: false,
      defaultLoading: false,
      articleLoading: false,
      passwordError: false,
      passwordErrorTip: '',
      findPassword: false
    };
  },
  computed: {
    threadId() {
      return this.$route.params.id;
    },
    currentUser() {
      return this.$store.state.user.info.attributes || {};
    },
    forums() {
      return this.$store.state.site.info.attributes || {};
    },
    //+积分
    integral() {
      return this.forums && this.forums.set_site  && this.forums.set_site.site_integral && this.forums.set_site.site_integral[3].ivalue;
    },
    unintegral() {
      return this.forums && this.forums.set_site  && this.forums.set_site.site_integral && this.forums.set_site.site_integral[3].unvalue;
    },
    rewardOrPay() {
      const price = parseFloat(this.paidInformation.price);
      const attachmentPrice = parseFloat(this.paidInformation.attachmentPrice);
      const questionPrice
        = Object.keys(this.question).length > 0
          ? parseFloat(this.question.onlookerUnitPrice)
          : 0;
      return price > 0 || attachmentPrice > 0 || questionPrice > 0
        ? 'pay'
        : 'reward';
    },
    payOrRewardAmount() {
      const price = parseFloat(this.paidInformation.price || '0');
      const attachmentPrice = parseFloat(
        this.paidInformation.attachmentPrice || '0'
      );
      const questionPrice
        = Object.keys(this.question).length > 0
          ? parseFloat(this.question.onlookerUnitPrice)
          : 0;
      return (
        price || attachmentPrice || questionPrice || this.payment.rewardAmount
      );
    },
    payOrderType() {
      const price = parseFloat(this.paidInformation.price || '0');
      const attachmentPrice = parseFloat(
        this.paidInformation.attachmentPrice || '0'
      );
      const questionPrice
        = Object.keys(this.question).length > 0
          ? parseFloat(this.question.onlookerUnitPrice)
          : 0;
      // 1：注册，2：打赏，3：付费主题，4：付费用户组，5：问答提问支付，6：问答围观付费, 7: 付费附件
      if (price > 0) {
        return 3;
      } else if (attachmentPrice > 0) {
        return 7;
      } else if (questionPrice > 0) {
        return 6;
      } else {
        return 2;
      }
    }
  },
  mounted() {
    if (Object.keys(this.thread).length === 0) this.getThread();
    else this.initData();
  },
  beforeDestroy() {
    clearInterval(this.timer);
    this.timer = null;
  },
  methods: {
    async getThread() {
      const params = {
        pid: Number(this.threadId)
      };
      return await newApi.readThreadDetail({ params }).then(result => {
        const { Data: data } = result;
        // const data = result.Data;
        if (data && data.thread && data.thread.isDeleted) return this.$router.replace('/error');
        this.articleLoading = false;
        this.thread = data;
        this.article = data.firstPost;
        this.postId = this.article.id;
        this.initData();
      },
      (e) => this.handleError(e, 'thread'));
    },
    initData() {
      this.initManagementList(this.thread);
      this.initPaidInformation(this.thread);
      this.initActions(this.thread, this.article);
      this.initLocation(this.thread);
      this.initQuestion(this.thread);
      this.filterImages();
      this.initRedPacket(this.thread);
      this.initReward(this.thread);
    },
    initRedPacket(data) {
      this.redPacket.isRedPacket = data.thread.isRedPacket;
      if (this.redPacket.isRedPacket === 0) return;
      // 是红包贴，调用一次轮询函数初始化红包信息
      this.autoUpdateRedpacketStatus();
      // 开启轮询
      this.timer = setInterval(() => {
        this.autoUpdateRedpacketStatus();
      }, 5000);
    },
    // 轮询红包状态
    autoUpdateRedpacketStatus() {
      if (this.onUpdateRed) return;
      if (this.redPacket.remain_number === 0) return clearInterval(this.timer);

      this.onUpdateRed = true;
      this.$store.dispatch('jv/get', [`redpacket/${this.threadId}`, {}]).then(
        (data) => {
          this.redPacket = Object.assign({}, this.redPacket, data);
          this.onUpdateRed = false;
        },
        (e) => this.handleError(e, 'redpacket')
      );
    },
    initReward(data) {
      if (
        data.thread.type === 5
        && data.thread.questionTypeAndMoney
        && data.thread.questionTypeAndMoney.type === 0
      ) {
        this.rewardQuestion = data.thread.questionTypeAndMoney;
        this.rewardQuestion.showTime = data.thread.questionTypeAndMoney.expiredAt.substr(
          0,
          10
        );

        this.timer = setInterval(() => {
          this.autoUpdateRewardStatus();
        }, 5000);
      }
    },
    // 轮询悬赏
    autoUpdateRewardStatus() {
      if (this.rewardQuestion.remainMoney === 0) {
        return clearInterval(this.timer);
      }
      if (this.onUpdateReward) return;
      this.onUpdateReward = true;
      // json-api 会带来缓存问题，故此构造普通请求
      // FIX: 修复了因为腾讯云COS存储带签名导致的重复赋值问题
      const params = {
        pid: Number(this.threadId)
      };
      return newApi.readThreadDetail({ params }).then(result => {
        const data = result.Data;
        this.rewardQuestion = data.thread.questionTypeAndMoney;
        this.rewardQuestion.showTime = data.thread.questionTypeAndMoney.expiredAt.substr(0, 10);
        this.onUpdateReward = false;
      },
      (e) => this.handleError(e, 'reward'));
    },
    filterImages() {
      this.article.imageSource = this.thread.images; // 把原图路径记为 imageSource
      if (
        this.article.contentAttachIds
        && this.article.contentAttachIds.length > 0
        && this.thread.images
        && this.thread.images.length > 0
      ) {
        this.thread.images = this.thread.images.filter(
          (image) => this.article.contentAttachIds.indexOf(String(image.id)) < 0
        );
      }
    },
    initQuestion(data) {
      if (!data.question) return;
      this.question = data.question;
      this.question.onlookerState = data.thread.onlookerState;
    },
    initLocation(data) {
      for (const key in this.location) this.location[key] = data.thread[key];
    },
    initPaidInformation(data) {
      for (const key in this.paidInformation) {
        if (key === 'paidUsers') {
          this.paidInformation[key] = data[key];
        } else {
          this.paidInformation[key] = data.thread[key];
        }
      }
    },
    initManagementList(data, type) {
      if (type === 'patch') {
        this.managementList.forEach((item) => {
          item.canOpera = data[item.name];
          if (item.name === 'canEssence') {
            item.isStatus = data.isEssence;
            item.text = item.isStatus
              ? this.$t('topic.cancelEssence')
              : this.$t('topic.essence');
          } else if (item.name === 'canSticky') {
            item.isStatus = data.isSticky;
            item.text = item.isStatus
              ? this.$t('topic.cancelSticky')
              : this.$t('topic.sticky');
          } else if (item.name === 'canPoster') {
            item.isStatus = data.isPoster;
            item.text = item.isStatus
              ? this.$t('topic.cancelPoster')
              : this.$t('topic.poster');
          } else if (item.name === 'canPinglun') {
            item.isStatus = data.isPinglun;
            item.text = item.isStatus
              ? this.$t('topic.cancelPinglun')
              : this.$t('topic.pinglun');
          } else if (item.name === 'canZhiding') {
            item.isStatus = data.isZhiding;
            item.text = item.isStatus
            item.text = this.$t('topic.shuaxinsticky');
          } else if (item.names === 'canOriginal') {
            item.isStatus = data.isOriginal;
            item.text = item.isStatus
            ? this.$t('topic.cancelOriginal')
            : this.$t('topic.originalsticky');
          }
        });
        if (
          (this.thread.thread.type === 4 || this.thread.thread.type === 5)
          && this.managementList.filter((item) => item.name === 'canEdit').length > 0
        ) {
          // 语音贴和问答帖，不支持编辑
          this.managementList.filter(
            (item) => item.name === 'canEdit'
          )[0].canOpera = false;
        }

        this.thread.isEssence = data.isEssence;
        this.managementList = this.managementList.filter((item) => item.canOpera);
      } else {
        this.managementList.forEach((item) => {
          item.canOpera = data.thread[item.name];
          if (item.name === 'canEssence') {
            item.isStatus = data.thread.isEssence;
            item.text = item.isStatus
              ? this.$t('topic.cancelEssence')
              : this.$t('topic.essence');
          } else if (item.name === 'canSticky') {
            item.isStatus = data.thread.isSticky;
            item.text = item.isStatus
              ? this.$t('topic.cancelSticky')
              : this.$t('topic.sticky');
          } else if (item.name === 'canPoster') {
            item.isStatus = data.thread.isPoster;
            item.text = item.isStatus
              ? this.$t('topic.cancelPoster')
              : this.$t('topic.poster');
          } else if (item.name === 'canPinglun') {
            item.isStatus = data.thread.isPinglun;
            item.text = item.isStatus
              ? this.$t('topic.cancelPinglun')
              : this.$t('topic.pinglun');
          } else if (item.name === 'canZhiding') {
            item.isStatus = data.thread.isZhiding;
            item.text = item.isStatus
            item.text = this.$t('topic.shuaxinsticky');
          } else if (item.names === 'canOriginal') {
            item.isStatus = data.thread.isOriginal;
            item.text = item.isStatus
            ? this.$t('topic.cancelOriginal')
            : this.$t('topic.originalsticky');
          }
        });
        if (
          (this.thread.thread.type === 4 || this.thread.thread.type === 5)
          && this.managementList.filter((item) => item.name === 'canEdit').length > 0
        ) {
          // 语音贴和问答帖，不支持编辑
          this.managementList.filter(
            (item) => item.name === 'canEdit'
          )[0].canOpera = false;
        }
        this.thread.thread.isEssence = data.thread.isEssence;
        this.managementList = this.managementList.filter((item) => item.canOpera);
      }
    },
    initActions(data, firstPost, type) {
      const viewInfo = { ...this.actions[0] };
      const favorInfo = { ...this.actions[2] };
      const likeInfo = { ...this.actions[1] };
      if (type === 'patch' && data) {
        if (data.viewCount !== undefined) {
          viewInfo.count = data.viewCount;
        }
        if (data.isFavorite !== undefined) {
          favorInfo.canOpera = data.canFavorite;
          favorInfo.isStatus = data.isFavorite;
          favorInfo.text = favorInfo.isStatus
            ? this.$t('topic.collectionAlready')
            : this.$t('topic.collection');
          favorInfo.icon = favorInfo.isStatus ? 'favored' : 'favor';
        }
      } else {
        if (data) {
          viewInfo.count = data.thread.viewCount;
          favorInfo.isStatus = data.isFavorite;
          favorInfo.text = favorInfo.isStatus
            ? this.$t('topic.collectionAlready')
            : this.$t('topic.collection');
          favorInfo.icon = favorInfo.isStatus ? 'favored' : 'favor';
          favorInfo.canOpera = data.canFavorite;
        }
      }
      if (firstPost) {
        likeInfo.count = firstPost.likeCount;
        likeInfo.isStatus = firstPost.isLiked;
        likeInfo.canOpera = firstPost.canLike;
        likeInfo.text = likeInfo.isStatus
          ? this.$t('topic.liked')
          : this.$t('topic.getLike');
        likeInfo.icon = likeInfo.isStatus ? 'liked' : 'like';
      }

      this.$set(this.actions, 0, viewInfo);
      this.$set(this.actions, 1, likeInfo);
      this.$set(this.actions, 2, favorInfo);
    },
    paying({ payWay, hideAvatar, rewardAmount }) {
      if (
        this.rewardOrPay === 'reward'
        && (!rewardAmount || parseFloat(rewardAmount) === 0)
      ) {
        return this.$message.error(this.$t('pay.AmountCannotBeLessThan0'));
      }
      this.payment.rewardAmount = rewardAmount;
      this.showCheckoutCounter = false;
      if (payWay === 'walletPay') {
        this.showPasswordInput = true;
        this.createOrder(
          hideAvatar,
          this.payOrRewardAmount,
          this.payOrderType,
          20
        ).finally(() => {
          this.defaultLoading = false;
        });
      } else if (payWay === 'wxPay') {
        if (!this.forums.paycenter.wxpay_close) {
          return this.$message.warning(this.$t('pay.wxPayClose'));
        }
        this.createOrder(
          hideAvatar,
          this.payOrRewardAmount,
          this.payOrderType,
          10
        )
          .then(() => {
            this.payOrder().then(
              (wechatQrcode) => {
                this.payment.wechat_qrcode = wechatQrcode;
                this.wxPayActive().then(
                  () => {
                    this.getThread();
                  },
                  () => console.log('支付失败')
                );
              },
              () => console.log('支付失败')
            );
          })
          .finally(() => {
            this.defaultLoading = false;
          });
      }
    },
    onPasswordInputCompleted(password) {
      this.payOrder(password).then(
        () => {
          this.getThread();
        },
        () => console.log('支付失败')
      );
    },
    postCommand(item) {
      if (this.defaultLoading) return;
      if (!this.isLogin()) return;
      this.defaultLoading = true;
      const params
        = item.command === 'isLiked'
          ? { _jv: { type: `posts`, id: this.postId }}
          : { _jv: { type: `threads`, id: this.threadId }};
      params[item.command] = !item.isStatus;
      return this.$store
        .dispatch('jv/patch', params)
        .then(
          (data) => {
            this.initManagementList(data, 'patch');
            this.initActions(data, '', 'patch');
            /*if (item.command === 'isLiked') {
              if (this.integral == 0) {
                return item.isStatus
                  ? this.$message.success(this.$t('discuzq.msgBox.cancelLikeSuccess'))
                  : this.$message.success(this.$t('discuzq.msgBox.likeSuccess'));
              } else {
                return item.isStatus
                  ? this.$message.success(this.$t('discuzq.msgBox.cancelLikeSuccess')+' 减掉积分：-'+this.unintegral)
                  : this.$message.success(this.$t('discuzq.msgBox.likeSuccess')+' 奖励积分：+'+this.integral);
              }

            }*/
            if (item.command === 'isSticky') {
              return item.isStatus
                ? this.$message.success(this.$t('topic.stickySuccess'))
                : this.$message.success(this.$t('topic.cancelStickySuccess'));
            }
            if (item.command === 'isPoster') {
              return item.isStatus
                ? this.$message.success(this.$t('topic.successPoster'))
                : this.$message.success(this.$t('topic.cancelSuccessPoster'));
            }
            if (item.command === 'isPinglun') {
              return item.isStatus
                ? this.$message.success(this.$t('topic.successPinglun'))
                : this.$message.success(this.$t('topic.cancelSuccessPinglun'));
            }
            if (item.command === 'isZhiding') {
              return item.isStatus
                ? this.$message.success(this.$t('topic.stickyzhidingSuccess'))
                : this.$message.success(this.$t('topic.stickyzhidingSuccess'));
            }
            if (item.command === 'isOriginal') {
              return item.isStatus
                ? this.$message.success(this.$t('topic.stickyoriginalSuccess'))
                : this.$message.success(this.$t('topic.cancelSuccessOriginal'));
            }
            if (item.command === 'isLiked') {

              if (this.integral == 0) {
                this.setLikeUser(!item.isStatus, data);
                item.isStatus
                  ? this.$message.success(this.$t('discuzq.msgBox.cancelLikeSuccess'))
                  : this.$message.success(this.$t('discuzq.msgBox.likeSuccess'));
              } else {
                this.setLikeUser(!item.isStatus, data);
                item.isStatus
                  ? this.$message.success(this.$t('discuzq.msgBox.cancelLikeSuccess')+' 减掉积分：-'+this.unintegral)
                  : this.$message.success(this.$t('discuzq.msgBox.likeSuccess')+' 奖励积分：+'+this.integral);

              }
            }
            if (item.command === 'isDeleted') return this.afterDeleted();
          },
          (e) => this.handleError(e)
        )
        .finally(() => {
          this.defaultLoading = false;
        });
    },
    setLikeUser(status, data) {
      this.initActions(null, data);
      status
        ? this.thread.likedUsers.unshift(this.currentUser)
        : this.thread.likedUsers.forEach((item, index, array) => {
          item.id === this.currentUser.id && array.splice(index, 1);
        });
    },
    afterDeleted() {
      this.$message({
        typeInformation: 'success',
        message: this.$t('topic.deleteSuccessAndJumpToBack')
      });
      setTimeout(() => {
        this.$router.push('/');
      }, 1000);
    }
  },
  head() {
    return {
      title:
        ((this.thread && this.thread.thread && this.thread.thread.title)
          || (this.thread
            && this.thread.firstPost
            && this.thread.firstPost.content.slice(0, 15))
          || '主题详情页')
        + (this.forums && this.forums.set_site && this.forums.set_site.site_name
          ? ` - ${this.forums.set_site.site_name}`
          : '\u200E'),
      meta: [
        {
          hid: 'keywords',
          name: 'keywords',
          content: (this.thread.category && this.thread.category.name) || ''
        },
        {
          hid: 'description',
          name: 'description',
          content:
            (this.thread.firstPost
              && this.thread.firstPost.summaryText.slice(0, 80))
            || ''
        },
        {
          name: 'og:video',
          content:
            (this.thread.threadVideo && this.thread.threadVideo.media_url)
            || ''
        }
      ]
    };
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
@import "@/assets/css/variable/color_pay.scss";

.page-post {
  background: #f4f5f6;
  width: 100%;
  display: flex;
  justify-content: space-between;

  main {
    background: transparent;
    flex: 1;
    /*IFTRUE_default*/
    width: 690px;
    /*FITRUE_default*/
    > .container-post {
      /*IFTRUE_default*/
      border-radius: 3px;
      padding: 20px;
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      border-radius: 3px;
      padding: 35px 35px;
      /*FITRUE_pay*/
      width: 100%;
      background: #ffffff;
      > .checking {
        margin-bottom: 20px;
        border-radius: 5px;
        text-align: center;
        background: #ededed;
        height: 35px;
        line-height: 35px;
        color: $font-color-grey;
      }
    }
  }
}
.desc-text {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 54px;
  margin-top: 30px;
  padding: 0 100px;
  font-size: 16px;
  color: #333;
  font-weight: bold;
  background: #fefbf5;
  white-space: nowrap;
  .desc-text-icon {
    font-size: 24px;
    color: $color-red-pay;
    &.offer-reward-icon {
      color: rgb(204, 163, 83);
    }
  }
  span {
    color: $color-red-pay;
  }
  .desc-logo {
    position: absolute;
    bottom: 10px;
    right: 25px;
    width: 65px;
    z-index: 10;
  }
}
</style>
