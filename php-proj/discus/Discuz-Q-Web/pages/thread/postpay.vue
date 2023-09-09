<template>
  <div v-loading="defaultLoading" class="page-post">
    <!-- 标题 -->
    <div v-if="type" class="title">
      {{ $t(`post.${typeInformation[type].headerText}`) }}
    </div>
    <!-- 分类列表 -->
    <div v-loading="categoryList.length === 0" class="category-list">
      <div class="category-title">内容分类：</div>
      <!-- 一级 -->
      <div class="category-one">
        <template v-for="(item, index) in categoryList">
          <span
            v-if="item.canCreateThread"
            :key="item._jv.id"
            :class="{ category: true, selected: selectedId === +item._jv.id}"
            @click="handleClickOne(+item._jv.id, index)"
          >{{ item.name }}</span>
        </template>
      </div>
      <!-- 二级-->
      <div v-show="categorySubList.length !== 0" class="category-two">
        <template v-for="subItem in categorySubList">
          <span
            v-if="subItem.canCreateThread"
            :key="subItem.id"
            :class="{
              category: true,
              'sub-category': true,
              selected: subSelectedId === subItem.id
            }"
            @click="handleClickTwo(subItem.id)"
          >{{ subItem.name }}</span>
        </template>
      </div>
    </div>

    <!-- 编辑区 -->
    <editor
      :type="type"
      :type-information="typeInformation[type]"
      :edit-resource-show="editResourceShow"
      :redpacket.sync="redpacket"
      :question.sync="question"
      :product.sync="product"
      :payment.sync="payment"
      :on-publish="onPublish"
      :on-save="onSave"
      :is-edit="isEditor"
      :is-draft="isDraft"
      :post.sync="post"
      @publish="publish"
      @savePublish="savePublish"
    />

    <!-- 收银台 -->
    <topic-checkout-counter
      v-if="showCheckoutCounter"
      :thread-type="threadType"
      :user="currentUser || {}"
      :amount="0"
      :be-asked-user="question.beUser || {}"
      :show-anonymous="false"
      :show-wechat-pay="canWechatPay"
      :reward-or-pay="rewardOrRed"
      :redpacket="redpacket"
      :offer-money="+question.price"
      @close="showCheckoutCounter = false"
      @paying="paying"
    />

    <!-- 密码 -->
    <topic-password
      v-if="showPasswordInput"
      :price="payAmount"
      :password-error.sync="passwordError"
      :password-error-tip="passwordErrorTip"
      @close="showPasswordInput = passwordError = false"
      @password="onPasswordInputCompleted"
      @findPassword="onFindPassword"
    />

    <!-- 微信支付 -->
    <topic-wx-pay
      v-if="showWxPay"
      :qr-code="wechatQrCode"
      @close="showWxPay = false"
    />

    <!-- 找回密码 -->
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
  </div>
</template>

<script>
const threadInclude = 'firstPost,firstPost.images,firstPost.attachments,rewardedUsers,'
  + 'category,threadVideo,question,question.beUser,question.images,onlookers';
import head from '@/mixin/head';
import publishResource from '@/mixin/publishResource';
import handleError from '@/mixin/handleError';
import isLogin from '@/mixin/isLogin';
import tencentCaptcha from '@/mixin/tencentCaptcha';
import payment from '@/mixin/payment';
import dayjs from 'dayjs';

export default {
  layout: 'post_layout',
  name: 'PostPay',
  mixins: [head, tencentCaptcha, handleError, publishResource, isLogin, payment],
  data() {
    return {
      captchaInfo: {}, // 用来保存支付时的验证信息
      title: this.$t('topic.publish'),
      editThread: {}, // 被编辑的主题
      categoryList: [], // 一级菜单
      categorySubList: [], // 二级菜单
      selectedId: '', // 选择的一级分类id
      subSelectedId: '', // 选择的二级分类id
      post: {
        id: '',
        title: '',
        text: '',
        imageList: [],
        videoList: [],
        attachedList: [],
        isAnonymous: false
      }, // 问答帖的匿名单独读取设置
      redpacket: {
        is_red_packet: 0,
        rule: 1,
        money: '',
        number: '',
        condition: 0,
        likenum: 0,
        isPaid: false
      }, // 红包默认选项
      payment: {
        paidType: 'free',
        price: 0,
        freeWords: 0,
        attachmentPrice: 0
      }, // free 免费， paid 全部付费，attachmentPaid 文章免费，附件付费
      location: { latitude: '', location: '', longitude: '' },
      product: {},
      question: {
        questionType: 'assigner',
        beUser: '',
        orderId: '',
        price: '',
        isOnlooker: false,
        paymentType: 'free',
        isAnonymous: false,
        isPaid: false,
        expired_at: ''
      }, // 问答类型 offerReward 悬赏， assigner 指定人问答
      editResourceShow: {
        showUploadImg: false,
        showUploadVideo: false,
        showUploadAttached: false
      },
      typeInformation: {
        // 0 文字帖 1 帖子 2 视频 3 图片 4 语音 5 问答 6 商品
        0: {
          type: 0,
          headerText: 'postText',
          textLimit: 5000,
          showPayment: false,
          showTitle: false,
          showImage: true,
          showVideo: false,
          showAttached: false,
          showEmoji: true,
          showTopic: true,
          showCaller: true,
          placeholder: '请输入您要发表的内容 ...'
        },

        1: {
          type: 1,
          headerText: 'postPost',
          textLimit: 49999,
          showPayment: true,
          showTitle: true,
          showImage: true,
          showVideo: false,
          showAttached: true,
          showEmoji: true,
          showTopic: true,
          showCaller: true,
          placeholder: '请输入您要发表的内容 ...'
        },

        2: {
          type: 2,
          headerText: 'postVideo',
          textLimit: 5000,
          showPayment: true,
          showTitle: false,
          showImage: false,
          showVideo: true,
          showAttached: false,
          showEmoji: true,
          showTopic: true,
          showCaller: true,
          placeholder: '请输入您要发表的内容 ...'
        },

        3: {
          type: 3,
          headerText: 'postImage',
          textLimit: 5000,
          showPayment: true,
          showTitle: false,
          showImage: true,
          showVideo: false,
          showAttached: false,
          showEmoji: true,
          showTopic: true,
          showCaller: true,
          placeholder: '请输入您要发表的内容 ...'
        },

        // 4 语音帖不需要发帖
        5: {
          type: 5,
          headerText: 'postQA',
          textLimit: 5000,
          showPayment: false,
          showTitle: false,
          showImage: true,
          showVideo: false,
          showAttached: false,
          showEmoji: true,
          showTopic: true,
          showCaller: true,
          placeholder: '请输入您要发表的内容 ...'
        },

        6: {
          type: 6,
          headerText: 'postProduct',
          textLimit: 5000,
          showPayment: false,
          showTitle: false,
          showImage: true,
          showVideo: false,
          showAttached: false,
          showEmoji: true,
          showTopic: true,
          showCaller: true,
          placeholder: '请输入您要发表的内容 ...'
        },
        14: {
          type: 14,
          headerText: 'postAsk',
          textLimit: 5000,
          showPayment: false,
          showTitle: false,
          showImage: true,
          showVideo: false,
          showAttached: false,
          showEmoji: true,
          showTopic: true,
          showCaller: true,
          placeholder: '请输入您要发表的内容 ...'
        }
      },
      isDraft: false, // 帖子草稿状态默认值
      isEditor: false, // 帖子编辑的默认值
      showCheckoutCounter: false,
      showPasswordInput: false,
      showWxPay: false,
      wechatQrCode: '',
      defaultLoading: false,
      passwordError: false,
      findPassword: false,
      passwordErrorTip: '',
      payAmount: 0, // 红包或悬赏的支付额
      threadType: 0, // 发布校验时需要调用收银台的主题类型： 0-文本红包 1-帖子红包 5-问答指定回答人
      rewardOrRed: '', // redpacket 红包, offerReward 悬赏, reward 指定人问答付费, renewal 站点续费
      timer1: null, // 准备启动自动保存
      timer2: null, // 自动保存
      currentId: '', // 当前发布帖子的ID
      onPublish: false, // 发布操作
      onSave: false, // 手动保存草稿操作
      onSaving: false // 自动保存草稿操作
    };
  },
  computed: {
    type() {
      return this.$route.query.type;
    },
    threadId() {
      return this.$route.query.threadId;
    },
    categoryId() {
      return +this.$route.query.categoryId;
    },
    operating() {
      return this.$route.query.operating; // 操作行为 edite-管理员编辑 draft-草稿
    },
    forums() {
      return this.$store.state.site.info.attributes || {};
    },
    canWechatPay() {
      if (this.forums && this.forums.paycenter) {
        return this.forums.paycenter.wxpay_close;
      } else {
        return false;
      }
    },
    textarea() {
      return process.client ? document.querySelector(`#textarea`) : '';
    },
    currentUser() {
      return this.$store.state.user.info.attributes || {};
    },
    beaskId() {
      return this.$route.query.beaskId;
    },
    qcloudCaptcha() {
      return this.forums && this.forums.qcloud && this.forums.qcloud.qcloud_captcha;
    },
    createThreadWithCaptcha() {
      return this.forums && this.forums.other && this.forums.other.create_thread_with_captcha;
    },
    openCaptcha() {
      return this.qcloudCaptcha && this.createThreadWithCaptcha;
    }
  },
  watch: {
    threadId(val) {
      this.currentId = val;
    },
    categoryId(val) {
      this.initCategoryId(val);
    }
  },
  mounted() {
    if (['0', '1', '2', '3', '5', '6','14'].indexOf(this.type) < 0) return this.$router.replace('/error');
    if (this.threadId) {
      this.currentId = this.threadId; // 当前发帖帖子id是否存在。新帖-id默认空;草稿-通过路由参数传递过来
    }
    this.getCategoryList();
    this.getThread();
    if (this.beaskId && this.beaskId !== 0) {
      this.getBeAskUserInfo();
    }
    // 监听自动保存
    if (this.operating !== 'edit') {
      this.onStartSave();
    }
  },
  beforeDestroy() {
    clearInterval(this.timer1);
    clearInterval(this.timer2);
    this.timer1 = null;
    this.timer2 = null;
  },
  methods: {
    getBeAskUserInfo() {
      const params = {
        include: 'groups'
      };
      this.$store
        .dispatch('jv/get', [`users/${this.beaskId}`, { params }])
        .then((res) => {
          if (res) {
            this.question.beUser = res;
          }
        })
        .catch((err) => {
          this.handleError(err);
        });
    },
    getCategoryList() {
      let types = this.type;
      if (types == 14) {
        const params = {type: 7};
        this.$store.dispatch('jv/get', ['categories', { params }]).then(
          res => {
            this.categoryList = res;
            this.categoryId && this.initCategoryId(this.categoryId);
          },
          e => this.handleError(e)
        );
      } else if (types == 12) {
        const params = {type: 6};
        this.$store.dispatch('jv/get', ['categories', { type: 6 }]).then(
          res => {
            this.categoryList = res;
            this.categoryId && this.initCategoryId(this.categoryId);
          },
          e => this.handleError(e)
        );
      } else {
        this.$store.dispatch('jv/get', ['categories', {}]).then(
          res => {
            this.categoryList = res;
            this.categoryId && this.initCategoryId(this.categoryId);
          },
          e => this.handleError(e)
        );
      }

    },
    initCategoryId(categoryId) {
      this.categoryList.forEach(item => {
        if (+item._jv.id === categoryId) {
          this.selectedId = categoryId;
          this.subSelectedId = '';
        } else {
          item.children && item.children.forEach(c => {
            if (c.id === categoryId) {
              this.selectedId = +item._jv.id;
              this.subSelectedId = categoryId;
              this.categorySubList = item.children || [];
            }
          });
        }
      });
    },
    // 处理一级分类点击
    handleClickOne(id, index) {
      if (this.selectedId === id) {
        this.selectedId = '';
        this.subSelectedId = '';
        this.categorySubList = [];
        return;
      }
      this.selectedId = id;
      this.subSelectedId = '';
      this.categorySubList = this.categoryList[index].children || [];
    },
    // 处理二级分类点击
    handleClickTwo(id) {
      if (this.subSelectedId === id) {
        this.subSelectedId = '';
        return;
      }
      this.subSelectedId = id;
    },
    // 非第一次发帖时，请求回显具体主题数据
    getThread() {
      if (!this.threadId) return;
      return this.$store
        .dispatch('jv/get', [
          `threads/${this.threadId}`,
          { params: { include: threadInclude }}
        ]).then(
          data => {
            if (data.isDeleted) return this.$router.push('/');
            this.initData(data);
            if (this.operating === 'draft') {
              this.isDraft = true; // 草稿状态
            } else if (this.operating === 'edit') {
              this.isEditor = true; // 发布后再编辑状态
            }
            if (this.textarea) {
              this.$nextTick(() => {
                this.textarea.style.height = `${this.textarea.scrollHeight}px`;
              }); // TODO 重置textarea 待优
            }
            if (data.firstPost.images.length > 0) {
              const contentAttachIds = data.firstPost.contentAttachIds;
              let imageList = data.firstPost.images;
              if (contentAttachIds.length > 0 && this.type === '1') {
                imageList = imageList.filter(
                  image => contentAttachIds.indexOf(image._jv.id) < 0
                );
              }
              this.editResourceShow.showUploadImg = true;
              this.initThreadResource(this.post.imageList, imageList);
            }
            if (data.firstPost.attachments.length > 0) {
              this.editResourceShow.showUploadAttached = true;
              this.initThreadResource(
                this.post.attachedList,
                data.firstPost.attachments
              );
            }
            if (data.threadVideo && Object.keys(data.threadVideo).length > 0) {
              this.editResourceShow.showUploadVideo = true;
              this.initThreadResource(
                this.post.videoList,
                [data.threadVideo],
                'videoList'
              );
              this.post.videoList[0].videoPercent = 1;
            }
          }, e => this.handleError(e)
        );
    },
    initData(data) {
      this.editThread = data; // 主题数据
      this.post.title = data.title;
      this.post.text = data.firstPost.parseContentHtml;
      this.post.id = data.firstPost._jv.id;
      this.redpacket.is_red_packet = data.isRedPacket;
      this.post.isAnonymous = data.isAnonymous;
      this.initRedPacket(); // 初始化红包
      if (data.questionTypeAndMoney && data.questionTypeAndMoney.type === 0) {
        this.initReward(data.questionTypeAndMoney); // 初始化悬赏
      }
      if (data.question) {
        this.question.isOnlooker = data.question.is_onlooker; // 初始指定人问答
      }
      this.payment.price = parseFloat(data.price);
      this.payment.attachmentPrice = parseFloat(data.attachmentPrice);
      this.payment.freeWords = data.freeWords;
      if (
        parseFloat(data.price) === 0
        && parseFloat(data.attachmentPrice) === 0
      ) {
        this.payment.paidType = 'free';
      } else if (
        parseFloat(data.price) > 0
        && parseFloat(data.attachmentPrice) === 0
      ) {
        this.payment.paidType = 'paid';
      } else if (
        parseFloat(data.price) === 0
        && parseFloat(data.attachmentPrice) > 0
      ) {
        this.payment.paidType = 'attachmentPaid';
      }
      this.location.location = data.location;
      this.location.latitude = data.latitude;
      this.location.longitude = data.longitude;
      this.product = { ...data.firstPost.postGoods };
    },
    // 初始化红包数据
    initRedPacket() {
      if (this.redpacket.is_red_packet === 0) return;
      this.$store
        .dispatch('jv/get', [`redpacket/${this.threadId}`, {}])
        .then(
          data => {
            this.redpacket.rule = data.rule;
            this.redpacket.number = data.number;
            this.redpacket.condition = data.condition;
            this.redpacket.likenum = data.likenum;
            this.redpacket.money = data.rule === 0
              ? (data.money / data.number).toFixed(2)
              : data.money;
          },
          e => this.handleError(e, 'redpacket')
        );
    },
    // 初始化悬赏数据
    initReward(data) {
      this.question.questionType = 'offerReward';
      this.question.price = data.money;
      this.question.expired_at = data.expired_at;
    },
    // 初始化主题资源
    initThreadResource(target, resource, key = '') {
      resource.forEach(item => {
        const attached = {
          name: key === 'videoList' ? item.file_name : item.fileName,
          url: key === 'videoList' ? item.media_url : item.thumbUrl,
          id: key === 'videoList' ? item.file_id : item._jv.id,
          size: key === 'videoList' ? '' : item.fileSize,
          type: key === 'videoList' ? '' : item.type, // 图片类型
          deleted: false // 用于图片 upload 组件的样式
        };
        target.push(attached);
      });
    },
    // 进入支付
    paying({ payWay, hideAvatar, rewardAmount }) {
      // if (Number(rewardAmount) < Number(this.forums.other.can_be_asked_money)) {
      //   this.$message.warning(`打赏金额不能低于${this.forums.other.can_be_asked_money}元`);
      //   return;
      // }
      if (!rewardAmount || parseFloat(rewardAmount) === 0) {
        return this.$message.error(this.$t('pay.AmountCannotBeLessThan0'));
      }
      this.payAmount = rewardAmount;
      this.showCheckoutCounter = false;
      if (this.threadType === 0 || this.threadType === 1) {
        this.payingRedpacket(payWay, hideAvatar, rewardAmount);
      }
      if (this.threadType === 5) {
        this.payingQuestion(payWay, hideAvatar, rewardAmount);
      }
    },
    // 支付红包
    payingRedpacket(payWay, hideAvatar, rewardAmount) {
      // 两种红包的不同订单类型
      const orderType = this.type === '0' ? 20 : 21;
      if (payWay === 'walletPay') {
        this.createOrder(hideAvatar, rewardAmount, orderType, 20)
          .then(() => {
            this.showPasswordInput = true;
          })
          .finally(() => {
            this.defaultLoading = false;
          });
      } else if (payWay === 'wxPay') {
        if (!this.forums.paycenter.wxpay_close) {
          return this.$message.warning(this.$t('pay.wxPayClose'));
        }
        this.createOrder(hideAvatar, rewardAmount, orderType, 10)
          .then(() => {
            this.payOrder().then(
              wechatQrcode => {
                this.wechatQrCode = wechatQrcode;
                this.wxPayActive().then(
                  orderNo => {
                    this.redpacket.orderId = orderNo;
                    this.redpacket.isPaid = true;
                    this.publish();
                    console.log('支付成功');
                  },
                  () => console.log('支付失败')
                );
              },
              () => console.log('支付失败')
            );
          }).finally(() => {
            this.defaultLoading = false;
          });
      }
    },
    // 支付问答
    payingQuestion(payWay, hideAvatar, rewardAmount) {
      this.question.price = rewardAmount;
      const payeeId = this.question.questionType === 'assigner'
        ? this.question.beUser._jv.id
        : 0;

      if (payWay === 'walletPay') {
        this.createOrder(hideAvatar, this.question.price, 5, 20, payeeId)
          .then(() => {
            this.showPasswordInput = true;
          })
          .finally(() => {
            this.defaultLoading = false;
          });
      } else if (payWay === 'wxPay') {
        if (!this.forums.paycenter.wxpay_close) {
          return this.$message.warning(this.$t('pay.wxPayClose'));
        }
        this.createOrder(hideAvatar, this.question.price, 5, 10, payeeId)
          .then(() => {
            this.payOrder().then(
              wechatQrcode => {
                this.wechatQrCode = wechatQrcode;
                this.wxPayActive().then(
                  orderNo => {
                    this.question.orderId = orderNo;
                    this.question.isPaid = true;
                    this.publish();
                    console.log('支付成功');
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
    // 支付文本红包
    payText() {
      this.threadType = 0;
      this.rewardOrRed = 'redpacket';
      this.showCheckoutCounter = true;
    },
    // 支付帖子红包
    payPost() {
      this.threadType = 1;
      this.rewardOrRed = 'redpacket';
      this.showCheckoutCounter = true;
    },
    // 支付指定问答
    payQA() {
      this.threadType = 5;
      this.rewardOrRed = 'reward';
      this.showCheckoutCounter = true;
    },
    // 支付悬赏问答
    payQR() {
      this.threadType = 5;
      this.rewardOrRed = 'offerReward';
      this.showCheckoutCounter = true;
    },
    onPasswordInputCompleted(password) {
      this.payOrder(password).then(
        orderNo => {
          if (this.threadType === 0 || this.threadType === 1) {
            this.redpacket.orderId = orderNo;
            this.redpacket.isPaid = true;
          }
          if (this.threadType === 5) {
            this.question.orderId = orderNo;
            this.question.isPaid = true;
          }
          this.publish();
          console.log('支付成功');
        },
        () => console.log('支付失败')
      );
    },
    // 发布校验
    checkPublish() {
      if (!this.isLogin()) return;
      // 0 文字帖 1 帖子 2 视频 3 图片 4 音频 5 问答 6 商品
      if (!this.selectedId) {
        return this.$message.warning(this.$t('post.theClassifyCanNotBeBlank'));
      }
      if (this.post.text.length > this.typeInformation[this.type].textLimit) {
        return this.$message.warning(this.$t('post.messageLengthCannotOver'));
      }
      // 0 校验文字贴，含红包
      if (this.type === '0' && !this.post.text) {
        return this.$message.warning(this.$t('post.theContentCanNotBeBlank'));
      }
      if (
        this.type === '0'
        && !this.isEditor
        && this.redpacket.is_red_packet
        && this.redpacket.money < 0.01
      ) {
        return this.$message.warning('红包金额最低0.01元');
      }
      if (
        this.type === '0'
        && !this.isEditor
        && this.redpacket.is_red_packet
        && this.redpacket.number < 1
      ) {
        return this.$message.warning('红包个数最少1个');
      }
      if (
        this.type === '0'
        && !this.isEditor
        && this.redpacket.is_red_packet
        && !this.redpacket.isPaid
      ) {
        return this.payText();
      }
      // 1 校验长文贴，含红包
      if (this.type === '1' && this.post.text.length < 2) {
        return this.$message.warning(this.$t('post.theContentCanNotBeBlank'));
      }
      if (this.type === '1' && !this.post.title) {
        return this.$message.warning(this.$t('post.theTitleCanNotBeBlank'));
      }
      if (
        this.type === '1'
        && this.post.title
        && this.post.title.length > 150
      ) {
        return this.$message.warning(this.$t('post.titleLengthCannotOver'));
      }
      if (
        this.type === '1'
        && this.payment.paidType === 'attachmentPaid'
        && this.post.attachedList.length === 0
      ) {
        return this.$message.warning(
          this.$t('post.attachmentListCanNotBeEmptyWhileAttachmentPaid')
        );
      }
      // 校验帖子红包
      if (
        this.type === '1'
        && !this.isEditor
        && this.redpacket.is_red_packet
        && this.redpacket.money < 0.01
      ) {
        return this.$message.warning('红包金额最低0.01元');
      }
      if (
        this.type === '1'
        && !this.isEditor
        && this.redpacket.is_red_packet
        && this.redpacket.number < 1
      ) {
        return this.$message.warning('红包个数最少1个');
      }
      if (
        this.type === '1'
        && !this.isEditor
        && this.redpacket.is_red_packet
        && !this.redpacket.isPaid
      ) {
        return this.payPost();
      }
      // 2 校验视频
      if (this.type === '2' && this.post.videoList.length === 0) {
        return this.$message.warning(this.$t('post.videoCannotBeEmpty'));
      }
      // 3 校验图片
      if (this.type === '3' && this.post.imageList.length === 0) {
        return this.$message.warning(this.$t('post.imageCannotBeEmpty'));
      }
      // 4 校验音频
      if (this.type === '4' && !this.post.text) {
        return this.$message.warning(this.$t('post.theContentCanNotBeBlank'));
      }
      // 5 校验问答(含悬赏)
      if (
        this.type === '5'
        && this.question.questionType === 'offerReward'
        && this.question.price < 0.1
      ) {
        return this.$message.warning('悬赏金额最低0.1元');
      }
      if (
        this.type === '5'
        && this.question.questionType === 'offerReward'
        && this.question.price > 10000
      ) {
        return this.$message.warning('悬赏金额最高1w元');
      }
      if (
        this.type === '5'
        && this.question.questionType === 'offerReward'
        && !this.question.expired_at
      ) {
        return this.$message.warning('请选择悬赏时间');
      }
      if (
        this.type === '5'
        && this.question.questionType === 'offerReward'
        && !dayjs(this.question.expired_at).isAfter(dayjs().add(1, 'day'))
      ) {
        return this.$message.warning('悬赏结束时间不能少于一天!');
      }
      if (
        this.type === '5'
        && this.question.questionType === 'assigner'
        && !this.question.beUser.username
      ) {
        return this.$message.warning(this.$t('post.pleaseChooseBeAskedUser'));
      }
      if (this.type === '5' && !this.post.text) {
        return this.$message.warning(this.$t('post.theContentCanNotBeBlank'));
      }
      if (
        this.type === '5'
        && this.question.questionType === 'assigner'
        && this.question.paymentType === 'paid'
        && !this.question.isPaid
      ) {
        return this.payQA();
      }
      if (
        this.type === '5'
        && this.question.questionType === 'offerReward'
        && !this.question.isPaid
      ) {
        return this.payQR();
      }
      // 6 商品
      if (
        this.type === '6'
        && (
          Object.keys(this.product).length === 0
          || (this.product && this.product._jv && !this.product._jv.id)
        )
      ) {
        return this.$message.warning(this.$t('post.goodsEmpty'));
      }
      // 校验帖子中的收费项
      if (
        this.payment.paidType === 'paid'
        && this.payment.price === 0
      ) {
        return this.$message.warning(
          this.$t('post.paidTypePaidPriceCanNotBeZero')
        );
      }
      if (
        this.payment.paidType === 'paid'
        && this.payment.price < 0.1
      ) {
        return this.$message.warning(this.$t('post.paidAmountTooLow'));
      }
      if (
        this.payment.paidType === 'attachmentPaid'
        && this.payment.attachmentPrice === 0
      ) {
        return this.$message.warning(
          this.$t('post.paidTypeAttachmentPaidPriceCanNotBeZero')
        );
      }
      if (
        this.payment.paidType === 'attachmentPaid'
        && this.payment.attachmentPrice < 0.1
      ) {
        return this.$message.warning(this.$t('post.paidAmountTooLow'));
      }

      return 'success';
    },
    async publish() {
      if (this.onSave) return this.$message.warning('网络较慢，请稍候发布！');

      this.onPublish = true;
      clearInterval(this.timer2);
      this.timer2 = null;

      /* * FIXME 2021-03-04
      * 原有的前端页面逻辑是只通过create_thread_with_captcha来判断是否需要验证码。真实情况下，后台可以通过设置qcloud_captcha属性来判断是否需要验证码，此处前后端逻辑不一致。
      * 为了不影响发版进度，暂时将前端逻辑改为需满足qcloud_captcha、create_thread_with_captcha都为true才需要验证码。
      * 后期，此处逻辑需要再与产品、测试、后台同事勾兑清楚。
      */
      // const qcloud_captcha = this.forums && this.forums.qcloud && this.forums.qcloud.qcloud_captcha;
      // const create_thread_with_captcha = this.forums
      //   && this.forums.other
      //   && this.forums.other.create_thread_with_captcha;
      if (
        !this.isEditor
          && this.openCaptcha
          && !this.question.isPaid
          && !this.redpacket.isPaid
      ) {
        try {
          this.captchaInfo = await this.checkCaptcha();
        } catch (error) {
          this.onPublish = false;
          return;
        }
      }
      if (this.checkPublish() !== 'success') {
        this.onPublish = false;
        return;
      }

      // 处理详情页编辑发布
      if (this.isEditor) {
        return Promise.all([this.editThreadPublish(), this.editPostPublish()])
          .then(
            dataArray => {
              dataArray[0] && this.$router.push(`/thread/${dataArray[0]._jv.id}`);
            },
            e => this.handleError(e)
          )
          .finally(() => {
            this.onPublish = false;
          });
      }
      // 处理常规发布，草稿发布
      let params = this.handleParams();
      if (this.openCaptcha) {
        if (this.captchaInfo && Object.keys(this.captchaInfo).length) {
          params = { ...params, ...this.captchaInfo };
          // 清空历史数据
          this.captchaInfo = {};
        } else {
          this.onPublish = false;
          return;
        }
      }
      // 标记发布id、发布类型
      params.id = this.currentId;
      params.is_draft = 0;
      params.is_old_draft = this.currentId ? 1 : 0;

      return this.$store.dispatch('jv/post', params)
        .then(
          data => {
            this.$router.push(`/thread/${data._jv.id}`);
          },
          e => this.handleError(e)
        )
        .finally(() => {
          this.onPublish = false;
        });
    },
    // 处理参数
    handleParams() {
      let params = {
        _jv: {
          type: `threads`,
          relationships: {
            category: {
              data: { type: 'categories', id: this.subSelectedId || this.selectedId }
            }
          }
        },
        content: this.post.text
      };
      this.post.title ? params.title = this.post.title : '';
      params.type = this.type;
      params.is_anonymous = this.post.isAnonymous;
      params = this.publishPayment(params, this.payment);
      params = this.publishLocation(params, this.location);
      params = this.publishThreadResource(params, this.post);
      params = this.publishPostResource(params, this.post);
      params = this.publishRedPacket(params, this.redpacket);
      if (this.type === '5') params = this.publishQuestion(params, this.question);
      if (this.type === '6') params = this.publishProduct(params, this.product);

      return params;
    },
    editPostPublish() {
      // 用于 更新 content image attached
      let postParams = {
        _jv: {
          type: `posts`,
          relationships: {}
        },
        content: this.post.text,
        is_old_draft: 1
      };
      if (this.threadId) postParams._jv.id = this.threadId;
      postParams = this.publishPostResource(postParams, this.post);
      if (this.type === '6') {
        postParams = this.publishProduct(postParams, this.product);
      }
      this.deleteAttachmentsAfterEdit(
        this.editThread.firstPost.attachments,
        this.post.attachedList
      ); // 编辑主题时删除的附件，在发布的时候删除
      return this.$store.dispatch('jv/patch', [
        postParams, { url: `/posts/${this.post.id}` }
      ]);
    },
    async editThreadPublish() {
      // 用于 更新 title video category payment
      let threadParams = {
        _jv: {
          type: `threads`,
          relationships: {
            category: {
              data: {
                type: 'categories',
                id: this.subSelectedId || this.selectedId
              }
            }
          }
        },
        is_old_draft: 1
      };
      if (this.threadId) threadParams._jv.id = this.threadId;
      this.post.title ? threadParams.title = this.post.title : '';
      threadParams.type = this.type;
      threadParams.is_anonymous = this.post.isAnonymous;
      threadParams = this.publishPayment(threadParams, this.payment);
      threadParams = this.publishLocation(threadParams, this.location);
      threadParams = this.publishThreadResource(threadParams, this.post);
      if (this.openCaptcha) {
        try {
          this.captchaInfo = await this.checkCaptcha();
        } catch (e) {
          this.onPublish = false;
          return;
        }
        if (this.captchaInfo && Object.keys(this.captchaInfo).length) {
          threadParams = { ...threadParams, ...this.captchaInfo };
          // 清空历史数据
          this.captchaInfo = {};
        } else {
          this.onPublish = false;
          return;
        }
      }
      return this.$store.dispatch('jv/patch', [
        threadParams, { url: `/threads/${this.threadId}` }
      ]);
    },
    // 用户点击保存草稿
    async savePublish() {
      if (!this.selectedId) return this.$message.warning('请选择帖子分类');
      if (this.type === '1' && !this.post.title) {
        return this.$message.warning('请设置标题');
      }
      if (
        (this.type === '1' && this.post.text.length < 2)
          || !this.post.text
      ) return this.$message.warning('请输入内容');
      if (this.onSave) return this.$message.warning('网络较迟，请稍后点击！');
      this.onSave = true;
      this.postDraft('user');
    },
    // 自动保存草稿
    async autoSavePublish() {
      if (!this.selectedId) return;
      if (!this.post.text) return;
      if (this.type === '1' && !this.post.title) return;
      if (this.onSaving) return this.$message.warning('网络较迟，请稍后点击！');
      this.onSaving = true;
      this.postDraft('auto');
    },
    postDraft(role) {
      const params = this.handleParams();
      params.id = this.currentId;
      params.is_draft = 1;
      params.is_old_draft = 0;

      return this.$store.dispatch('jv/post', params)
        .then(
          data => {
            if (!this.currentId) {
              this.currentId = data._jv.id;
            }
            role === 'user' && this.$message.success('保存成功');
          },
          e => this.handleError(e)
        )
        .finally(e => {
          this.onSave = false;
          this.onSaving = false;
        });
    },
    // 监听自动保存
    onStartSave() {
      this.timer1 = setInterval(() => {
        if (!this.selectedId || !this.post.text) return;
        if (this.type === '1' && (!this.post.title || this.post.text < 2)) return;
        this.startSave();
        clearInterval(this.timer1);
        this.timer1 = null;
      }, 10000);
    },
    // 开启自动保存
    startSave() {
      this.timer2 = setInterval(() => {
        if (this.onSave || this.onSaving) return;
        this.autoSavePublish();
      }, 120000);
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/css/variable/color.scss';
/*IFTRUE_default*/
.page-post {
  min-width: 700px;
  width: 100%;
  height: 100%;
  margin: 60px 140px 30px 140px;
  > .title {
    font-weight: bold;
    font-size: 26px;
  }
}
.category-list {
  position: relative;
  margin-top: 50px;
  padding-bottom: 25px;
  border-bottom: 1px solid #F5F5F5;
  font-size:14px;
  color: #333;
  .category-title{
    position: absolute;
    left: 0;
    top: 0;
    font-size:16px;
    line-height: 33px;
  }
  .category-one {
    display:flex;
    flex-wrap: wrap;
    padding-left: 90px;
    margin-bottom: 14px;
    min-height: 33px;
  }
  .category-two {
    position: relative;
    display:flex;
    flex-wrap: wrap;
    padding: 20px 30px;
    background: #f5f5f5;
    border-radius: 6px;
    transition: all .5s;
    &::before{
      position: absolute;
      left: 20px;
      top: -27px;
      content: '';
      display: block;
      width: 0;
      height: 0;
      border: 14px solid #f5f5f5;
      border-top-color: transparent;
      border-right-color: transparent;
      border-left-color: transparent;
    }
  }
  .category {
    height: 33px;
    line-height: 33px;
    padding: 0 22px;
    margin-right: 13px;
    margin-bottom: 10px;
    font-size: 14px;
    background: #F5F5F5;
    border-radius: 17px;
    transition: all .5s;
    cursor: pointer;
    &:hover{
      background: #C4D4FF;
    }
    &.selected {
      color: #fff;
      background: $color-blue-base;
    }
  }
  .sub-category{
    color: #666;
    background: #fff;
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
@import '@/assets/css/pay_scss/postpay.scss';
/*FITRUE_pay*/
</style>
