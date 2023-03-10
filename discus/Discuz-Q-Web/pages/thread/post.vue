<template>
  <div v-loading="defaultLoading" class="page-post">
    <div v-if="type" class="title">
      {{ $t(`post.${typeInformation[type].headerText}`) }}
    </div>
    <!-- <div v-loading="categoryList.length === 0" class="category-list">
      <template v-for="(category, index) in categoryList">
        <span
          v-if="category.canCreateThread"
          :key="index"
          :class="{
            category: true,
            selected: categorySelectedId === category._jv.id
          }"
          @click="categorySelectedId = category._jv.id"
        >{{ category.name }}</span>
      </template>
    </div> -->
    <!-- (扩展二级分类列表) -->
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
    <editor
      :type-information="typeInformation[type]"
      :edit-resource-show="editResourceShow"
      :question.sync="question"
      :product.sync="product"
      :payment.sync="payment"
      :on-publish="onPublish"
      :is-edit="isEditor"
      :post.sync="post"
      @publish="publish"
    />
    <topic-checkout-counter
      v-if="showCheckoutCounter"
      :thread-type="5"
      :user="currentUser || {}"
      :amount="0"
      :be-asked-user="question.beUser || {}"
      :show-anonymous="false"
      :show-wechat-pay="canWechatPay"
      reward-or-pay="reward"
      @close="showCheckoutCounter = false"
      @paying="paying"
    />
    <topic-password
      v-if="showPasswordInput"
      :price="question.price"
      :password-error.sync="passwordError"
      :password-error-tip="passwordErrorTip"
      @close="showPasswordInput = passwordError = false"
      @password="onPasswordInputCompleted"
      @findPassword="onFindPassword"
    />
    <topic-wx-pay
      v-if="showWxPay"
      :qr-code="wechatQrCode"
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
  </div>
</template>

<script>
const threadInclude = 'firstPost,firstPost.images,firstPost.attachments,category,threadVideo';
import head from '@/mixin/head';
import publishResource from '@/mixin/publishResource';
import handleError from '@/mixin/handleError';
import isLogin from '@/mixin/isLogin';
import tencentCaptcha from '@/mixin/tencentCaptcha';
import payment from '@/mixin/payment';

export default {
  name: 'Post',
  mixins: [
    head,
    tencentCaptcha,
    handleError,
    publishResource,
    isLogin,
    payment
  ],
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
        attachedList: []
      },
      payment: {
        paidType: 'free', price: 0, freeWords: 0, attachmentPrice: 0 }, // free 免费， paid 全部付费，attachmentPaid 文章免费，附件付费
      location: { latitude: '', location: '', longitude: '' },
      product: {},
      question: {
        beUser: '',
        orderId: '',
        price: '',
        isOnlooker: false,
        paymentType: 'free',
        isAnonymous: false,
        isPaid: false
      },
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
          showImage: false,
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

        // 语音帖不需要发帖
        // 4: { type: 4,
        // headerText: 'postImage', textLimit: 450,
        // showPayment: true, showTitle: false, showImage: true, showVideo: false,
        //   showAttached: false, showEmoji: true, showTopic: true, showCaller: true, placeholder: '请输入您要发表的内容 ...' },

        // TODO 问答帖
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

        // TODO 商品帖
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
        }
      },
      // categorySelectedId: '',
      isEditor: false,
      onPublish: false,
      showCheckoutCounter: false,
      showPasswordInput: false,
      showWxPay: false,
      wechatQrCode: '',
      defaultLoading: false,
      passwordError: false,
      findPassword: false,
      passwordErrorTip: ''
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
    }
  },
  watch: {
    categoryId(val) {
      this.initCategoryId(val);
    }
  },
  mounted() {
    if (['0', '1', '2', '3', '5', '6'].indexOf(this.type) < 0) {
      return this.$router.replace('/error');
    }
    this.getCategoryList();
    this.getThread();
    if (this.beaskId) {
      this.getBeAskUserInfo();
    }
  },
  methods: {
    getBeAskUserInfo() {
      const params = {
        include: 'groups'
      };
      this.$store
        .dispatch('jv/get', [`users/${this.beaskId}`, { params }])
        .then(res => {
          if (res) {
            this.question.beUser = res;
            console.log('提问', this.question);
          }
        })
        .catch(err => {
          this.handleError(err);
        });
    },
    getCategoryList() {
      this.$store.dispatch('jv/get', ['categories', {}]).then(
        res => {
          this.categoryList = res;
          // const arr = [];
          // for (let i = 0; i < res.length; i++) {
          //   if (res[i].canCreateThread === true) {
          //     arr.push(res[i]);
          //   }
          // }
          // if (this.categoryId) {
          //   this.categorySelectedId = this.categoryId;
          // } else {
          //   this.categorySelectedId = arr[0]._jv.id;
          // }
          this.categoryId && this.initCategoryId(this.categoryId);
        },
        e => this.handleError(e)
      );
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
    getThread() {
      if (!this.threadId) return;
      return this.$store
        .dispatch('jv/get', [
          `threads/${this.threadId}`,
          { params: { include: threadInclude }}
        ])
        .then(
          data => {
            if (data.isDeleted) return this.$router.push('/');
            this.initData(data);
            this.isEditor = true;
            if (this.textarea) {
              this.$nextTick(() => {
                this.textarea.style.height = `${this.textarea.scrollHeight}px`;
              });
            } // TODO 重置textarea 待优
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
          },
          e => this.handleError(e)
        );
    },
    initData(data) {
      this.editThread = data;
      // this.categorySelectedId = data.category._jv.id;
      this.post.title = data.title;
      this.post.text = data.firstPost.content;
      this.post.id = data.firstPost._jv.id;
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
    initThreadResource(target, resource, key = '') {
      resource.forEach(item => {
        const attached = {
          name: key === 'videoList' ? item.file_name : item.fileName,
          url: key === 'videoList' ? item.media_url : item.thumbUrl,
          id: key === 'videoList' ? item.file_id : item._jv.id,
          size: key === 'videoList' ? '' : item.fileSize,
          deleted: false // 用于图片 upload 组件的样式
        };
        target.push(attached);
      });
    },
    paying({ payWay, hideAvatar, rewardAmount }) {
      if (!rewardAmount || parseFloat(rewardAmount) === 0) {
        return this.$message.error(this.$t('pay.AmountCannotBeLessThan0'));
      }
      this.question.price = rewardAmount;
      this.showCheckoutCounter = false;
      const payeeId = this.question.beUser._jv.id;
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
    payQA() {
      this.showCheckoutCounter = true;
    },
    onPasswordInputCompleted(password) {
      this.payOrder(password).then(
        orderNo => {
          this.question.orderId = orderNo;
          this.question.isPaid = true;
          this.publish();
          console.log('支付成功');
        },
        () => console.log('支付失败')
      );
    },
    checkPublish() {
      if (!this.isLogin()) return;
      // 0 文字帖 1 帖子 2 视频 3 图片 4 音频 5 问答 6 商品
      // if (!this.categorySelectedId) {
      //   return this.$message.warning(this.$t('post.theClassifyCanNotBeBlank'));
      // }
      if (!this.selectedId) {
        return this.$message.warning(this.$t('post.theClassifyCanNotBeBlank'));
      }
      if (this.post.text.length > this.typeInformation[this.type].textLimit) {
        return this.$message.warning(this.$t('post.messageLengthCannotOver'));
      }
      if (this.type === '0' && !this.post.text) {
        return this.$message.warning(this.$t('post.theContentCanNotBeBlank'));
      }

      if (this.type === '1' && !this.post.text) {
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

      if (this.type === '2' && this.post.videoList.length === 0) {
        return this.$message.warning(this.$t('post.videoCannotBeEmpty'));
      }
      if (this.type === '3' && this.post.imageList.length === 0) {
        return this.$message.warning(this.$t('post.imageCannotBeEmpty'));
      }
      if (this.type === '4' && !this.post.text) {
        return this.$message.warning(this.$t('post.theContentCanNotBeBlank'));
      }

      if (this.type === '5' && !this.question.beUser.username) {
        return this.$message.warning(this.$t('post.pleaseChooseBeAskedUser'));
      }
      if (this.type === '5' && !this.post.text) {
        return this.$message.warning(this.$t('post.theContentCanNotBeBlank'));
      }
      if (
        this.type === '5'
        && this.question.paymentType === 'paid'
        && !this.question.isPaid
      ) {
        return this.payQA();
      } // 付费问答帖，开启收银台
      if (
        this.type === '6'
        && (Object.keys(this.product).length === 0
          || (this.product && this.product._jv && !this.product._jv.id))
      ) {
        return this.$message.warning(this.$t('post.goodsEmpty'));
      }
      if (this.payment.paidType === 'paid' && this.payment.price === 0) {
        return this.$message.warning(
          this.$t('post.paidTypePaidPriceCanNotBeZero')
        );
      }
      if (this.payment.paidType === 'paid' && this.payment.price < 0.1) {
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
      if (this.forums.other.create_thread_with_captcha && !this.question.isPaid) {
        try {
          this.captchaInfo = await this.checkCaptcha();
        } catch (error) {
          this.onPublish = false;
          return;
        }
      }

      if (this.checkPublish() !== 'success') return;
      this.onPublish = true;
      if (this.isEditor) {
        return Promise.all([this.editThreadPublish(), this.editPostPublish()])
          .then(
            dataArray => {
              this.$router.push(`/thread/${dataArray[0]._jv.id}`);
            },
            e => this.handleError(e)
          )
          .finally(() => {
            this.onPublish = false;
          });
      }
      let params = {
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
        content: this.post.text
      };
      this.post.title ? (params.title = this.post.title) : '';
      params.type = this.type;
      params = this.publishPayment(params, this.payment);
      params = this.publishLocation(params, this.location);
      params = this.publishThreadResource(params, this.post);
      params = this.publishPostResource(params, this.post);

      if (this.type === '5') {
        params = this.publishQuestion(params, this.question);
      }
      if (this.type === '6') params = this.publishProduct(params, this.product);
      if (this.forums.other.create_thread_with_captcha) {
        if (this.captchaInfo && Object.keys(this.captchaInfo).length) {
          params = { ...params, ...this.captchaInfo };
          // 清空历史数据
          this.captchaInfo = {};
        } else {
          this.onPublish = false;
          return;
        }
      }
      return this.$store
        .dispatch('jv/post', params)
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
    editPostPublish() {
      // 用于 更新 content image attached
      let postParams = {
        _jv: {
          type: `posts`,
          relationships: {}
        },
        content: this.post.text
      };
      if (this.threadId) postParams._jv.id = this.threadId;
      postParams = this.publishPostResource(postParams, this.post);
      if (this.type === '6') {
        postParams = this.publishProduct(postParams, this.product);
      }
      // this.deleteAttachmentsAfterEdit(this.editThread.firstPost.images, this.post.imageList) // 编辑主题时删除的图片，在发布的时候删除
      this.deleteAttachmentsAfterEdit(
        this.editThread.firstPost.attachments,
        this.post.attachedList
      ); // 编辑主题时删除的附件，在发布的时候删除
      return this.$store.dispatch('jv/patch', [
        postParams,
        { url: `/posts/${this.post.id}` }
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
        }
      };
      if (this.threadId) threadParams._jv.id = this.threadId;
      this.post.title ? (threadParams.title = this.post.title) : '';
      threadParams.type = this.type;
      threadParams = this.publishPayment(threadParams, this.payment);
      threadParams = this.publishLocation(threadParams, this.location);
      threadParams = this.publishThreadResource(threadParams, this.post);
      if (this.forums.other.create_thread_with_captcha) {
        try {
          threadParams = await this.checkCaptcha(threadParams);
        } catch (e) {
          this.onPublish = false;
          return;
        }
      }
      return this.$store.dispatch('jv/patch', [
        threadParams,
        { url: `/threads/${this.threadId}` }
      ]);
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
.page-post {
  min-width: 700px;
  width: 100%;
  height: 100%;
  margin: 60px 140px 30px 140px;
  > .title {
    font-weight: bold;
    font-size: 26px;
  }
  // > .category-list {
  //   min-height: 25px;
  //   margin-top: 50px;
  //   border-bottom: 1px solid #f5f5f5;
  //   padding-bottom: 20px;
  //   > .category {
  //     display: inline-block;
  //     height: 25px;
  //     line-height: 25px;
  //     padding: 0 10px;
  //     border-radius: 12.5px;
  //     background: $background-color-grey;
  //     font-size: 14px;
  //     color: #777777;
  //     margin-right: 10px;
  //     margin-bottom: 10px;
  //     cursor: pointer;
  //     &.selected {
  //       color: white;
  //       background: $color-blue-base;
  //     }
  //   }
  // }
}
// 分类列表
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
    cursor: pointer;
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
</style>
