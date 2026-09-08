<template>
  <div class="site-info-container">
    <div v-if="forums">
      <div class="logo-content">
        /*IFTRUE_default*/
        <img
          :src="
            forums.set_site && forums.set_site.site_logo
              ? forums.set_site.site_logo
              : require('@/assets/logo.png')
          "
          alt="logo"
          class="logo"
        >
        /*FITRUE_default*/
        /*IFTRUE_pay*/
        <img
          :src="
            forums.set_site && forums.set_site.site_logo
              ? forums.set_site.site_logo
              : require('@/assets/pay_imgs/logo_pay.png')
          "
          alt="logo"
          class="logo"
        >
        /*FITRUE_pay*/
      </div>
      <div class="site-info">
        <div class="avatar">
          <avatar
            v-if="forums.set_site && forums.set_site.site_author"
            :user="{
              id: forums.set_site.site_author.id,
              username: forums.set_site.site_author.username,
              avatarUrl: forums.set_site.site_author.avatar
            }"
            :size="40"
            :round="true"
          />
          <avatar
            v-else
            :user="{ id: 0, username: '无', avatarUrl: '' }"
            :prevent-jump="true"
            :size="40"
            :round="true"
          />
          <div
            v-if="forums.set_site && forums.set_site.site_author"
            class="right"
          >
            <div class="label">{{ $t("site.circlemaster") }}</div>
            <div class="value name text-hidden">
              {{ forums.set_site && forums.set_site.site_author.username }}
            </div>
          </div>
        </div>
        <div class="member" @click="jump2ManagePage">
          <div class="label">{{ $t("home.theme") }}</div>
          <div class="value">
            {{ forums.other && forums.other.count_users }}
          </div>
        </div>
        <div class="threads">
          <div class="label">{{ $t("manage.contents") }}</div>
          <div class="value">
            {{ forums.other && forums.other.count_threads }}
          </div>
        </div>
        <div class="create-time">
          <div class="label">{{ $t("manage.creationtime") }}</div>
          <div
            v-if="forums && forums.set_site && forums.set_site.site_install"
            class="value"
          >
            {{
              (forums.set_site && forums.set_site.site_install).substr(0, 10)
            }}
          </div>
        </div>
      </div>
      <div class="site-detail">
        <div class="header">
          <div class="title base-font-size">
            {{ $t("manage.siteintroduction") }}
          </div>
          <div v-if="+groupsId === 1" class="modify" @click="handleModify">
            {{ $t("profile.modify") }}
          </div>
        </div>
        <div
          v-if="forums && forums.set_site && forums.set_site.site_introduction"
          class="content base-font-size"
        >
          {{ forums.set_site.site_introduction }}
        </div>
        <div v-else class="content base-font-size grey-color">
          {{ $t("modify.noSiteInfo") }}
        </div>
        <el-dialog
          :title="$t('modify.modifySiteInfo')"
          :visible.sync="isModify"
          width="620px"
          custom-class="custom-dialog"
          append-to-body
          :close-on-click-modal="false"
        >
          <div class="dialog-main">
            <el-input
              v-model="inputInfo"
              type="textarea"
              :rows="5"
              class="custom-input"
              :placeholder="$t('modify.siteInfoPlaceholder')"
            />
          </div>
          <div class="dialog-footer">
            <el-button
              type="primary"
              size="medium"
              :loading="loading"
              @click="confirmModify"
            >
              {{ $t("discuzq.ok") }}
            </el-button>
            <el-button size="medium" @click="isModify = false">
              {{ $t("discuzq.msgBox.cancel") }}
            </el-button>
          </div>
        </el-dialog>
      </div>
      <div v-if="forums && forums.set_site" class="circlemode">
        <div class="title base-font-size">
          {{ $t("site.circlemode") }} \ {{ $t("site.price") }}
        </div>
        <div
          v-if="forums.set_site && forums.set_site.site_mode === 'public'"
          class="content base-font-size grey-color"
        >
          {{ $t("site.publicmode") }} \ {{ $t("post.free") }}
        </div>
        <div v-else class="content base-font-size grey-color">
          {{ $t("site.paymentmode") }} \ {{ $t("post.yuanItem")
          }}{{ forums.set_site.site_price }} （{{
            forums.set_site.site_expire
              ? $t("site.periodvalidity") +
                forums.set_site.site_expire +
                $t("site.day")
              : $t("site.permanent")
          }}）
        </div>
      </div>
      <div class="permission">
        <div class="title base-font-size">
          {{ $t("manage.myRole") }} \ {{ $t("site.permission") }}
        </div>
        <div v-if="userInfo" class="user-detail">
          <div class="avatar">
            <avatar
              :user="{
                id: userInfo.id,
                username: userInfo.username,
                avatarUrl: userInfo.avatarUrl
              }"
              :prevent-jump="true"
              :size="50"
              :round="true"
            />
          </div>
          <div class="user-info">
            <div class="name base-font-size">{{ userInfo.username }}</div>
            <div class="role">
              {{ $t("site.role") }}
              {{
                (forums.user &&
                  forums.user.groups &&
                  forums.user.groups.length > 0 &&
                  forums.user.groups[0].name) ||
                  ""
              }}
            </div>
            <div v-if="userInfo.joinedAt" class="join-time">
              {{ $t("manage.joinedTime") }}
              {{ userInfo.joinedAt.substr(0, 10) }}
              <div
                v-if="forums && forums.set_site.site_mode === 'pay'"
                class="inline"
              >
                , {{
                  $t("site.periodvalidity") +
                    $t("site.to") + '：' +
                    handleExpiredAt(userInfo.expiredAt)
                }}
                <!-- <template v-if="userInfo.expiredAt">
                  ({{ $t('pay.surplus') + (handleDays('userInfo.expiredAt') > 0
                  ? handleDays('userInfo.expiredAt') : 0) + $t('site.day') }})
                  </template> -->
                <!-- （{{
                  forums.set_site.site_expire
                    ? $t("pay.surplus") +
                      forums.set_site.site_expire +
                      $t("site.day")
                    : $t("site.permanent")
                }}） -->
              </div>
              <div
                v-if="forums.set_site && forums.set_site.site_mode === 'pay' && forums.set_site.site_expire !== ''"
                class="inline renewal"
                @click="renewal"
              >
                {{ day === '' ? $t('site.renewal') : $t('site.renewalTime', { num:day }) }}
              </div>
            </div>
            <div class="permission-list">
              <div
                v-for="(item, index) in permissionList"
                :key="index"
                class="item"
              >
                <!-- {{ $t(`permission.${item.permission.replace(/\./g, '_')}`) }} -->
                {{ handlePermission(item.permission) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 收银台 -->
    <topic-checkout-counter
      v-if="showCheckoutCounter"
      :thread-type="10"
      :user="currentUser || {}"
      :be-asked-user="{}"
      :amount="0"
      :show-anonymous="false"
      :show-wechat-pay="canWechatPay"
      reward-or-pay="renewal"
      :offer-money="+2"
      :lowest-price="price"
      @close="showCheckoutCounter = false"
      @paying="paying"
    />

    <!-- 微信支付 -->
    <topic-wx-pay
      v-if="showWxPay"
      :qr-code="wechatQrCode"
      @close="showWxPay = false"
    />

    <!-- 密码 -->
    <topic-password
      v-if="showPasswordInput"
      :price="price"
      :password-error.sync="passwordError"
      :password-error-tip="passwordErrorTip"
      @close="showPasswordInput = passwordError = false"
      @password="onPasswordInputCompleted"
      @findPassword="onFindPassword"
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
import handleError from '@/mixin/handleError';
import head from '@/mixin/head';
import { timestamp2day } from '@/utils/time';
import payment from '@/mixin/payment';

export default {
  layout: 'center_layout',
  name: 'SiteInfo',
  mixins: [handleError, head, payment],
  data() {
    return {
      showCheckoutCounter: false,
      title: this.$t('manage.circleinfo'),
      userId: this.$store.state.user.info.id, // 获取当前登陆用户的ID
      groupsId: '',
      inputInfo: '',
      isModify: false,
      loading: false,
      permissionList: [], // 用户权限
      showWxPay: false,
      wechatQrCode: '',
      showPasswordInput: false,
      passwordError: false,
      findPassword: false,
      passwordErrorTip: ''
    };
  },
  computed: {
    currentUser() {
      return this.$store.state.user.info.attributes || {};
    },
    forums() {
      const forums = this.$store.state.site.info.attributes;
      return forums;
    },
    userInfo() {
      return this.$store.state.user.info.attributes || {};
    },
    day() {
      const info = this.$store.state.site.info.attributes;
      if (info && info.set_site && info.set_site.site_expire) {
        return info.set_site.site_expire;
      }
      return '';
    },
    price() {
      const info = this.$store.state.site.info.attributes;
      if (info && info.set_site && info.set_site.site_price) {
        return info.set_site.site_price;
      }
      return 0;
    },
    canWechatPay() {
      if (this.forums && this.forums.paycenter) {
        return this.forums.paycenter.wxpay_close;
      } else {
        return false;
      }
    }
  },
  mounted() {
    this.inputInfo
      = this.forums
      && this.forums.set_site
      && this.forums.set_site.site_introduction;
    this.groupsId
      = this.forums
      && this.forums.user
      && this.forums.user.groups
      && this.forums.user.groups.length > 0
      && this.forums.user.groups[0].id;
    this.getPermissions();
    this.reloadForums();
  },
  methods: {
    jump2ManagePage() {
      if (this.forums && this.forums.other) {
        if (this.forums.other.can_edit_user_group || this.forums.other.can_edit_user_status) {
          this.$router.push('/manage');
        }
      }
    },
    async reloadForums() {
      try {
        await this.$store.dispatch('site/getSiteInfo');
      } catch (err) {
        console.log('getUserInfo err', err);
      }
    },
    handleModify() {
      this.isModify = !this.isModify;
      if (this.isModify) {
        this.inputInfo
          = this.forums
          && this.forums.set_site
          && this.forums.set_site.site_introduction;
      }
    },
    confirmModify() {
      if (this.loading) return;
      const params = {
        data: [
          {
            attributes: {
              key: 'site_introduction',
              value: this.inputInfo,
              tag: 'default'
            }
          }
        ]
      };
      this.loading = true;
      this.$store
        .dispatch('jv/post', [{ _jv: { type: 'settings' }}, { data: params }])
        .then(async() => {
          try {
            await this.$store.dispatch('site/getSiteInfo');
          } catch (error) {
            console.log(error);
          }
          this.isModify = false;
          this.$message.success(this.$t('discuzq.msgBox.modifySuccess'));
        })
        .catch(e => {
          this.handleError(e);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    // 调用 用户组权限 接口
    getPermissions() {
      const params = {
        'filter[type]': 'invite',
        include: ['permission']
      };
      this.$store.dispatch('jv/get', ['groups', { params }]).then(res => {
        // console.log(res);
        const groupsId
          = this.forums
          && this.forums.user
          && this.forums.user.groups
          && this.forums.user.groups.length > 0
          && this.forums.user.groups[0].id;
        let list = [];
        res.forEach(item => {
          if (+item._jv.id === +groupsId) {
            list = item.permission;
            list = list.filter(item => {
              return item.permission !== 'createThread';
            });
          }
        });
        this.permissionList = list.filter(item => {
          return item.permission !== 'user.edit';
        });
      });
    },
    handleExpiredAt(date) {
      if (date) {
        return date.substr(0, 10);
      }
      return this.$t('site.permanent');
    },
    handlePermission(str) {
      if (str.includes('canBeAsked.money')) {
        return this.$t(`permission.canBeAsked_money`, { lowPrice: str.match(/\d+$/)[0] });
      }
      return this.$t(`permission.${str.replace(/\./g, '_')}`);
    },
    handleDays(date) {
      const _date = Math.round(new Date(date) / 1000);
      return timestamp2day(_date);
    },
    renewal() {
      this.showCheckoutCounter = true;
    },
    /**
     * 进入支付
     *
     * 1. 创建订单
     * 2. 调用支付接口（订单号）
     * 3. 调用轮询订单状态接口查看支付结果
     */
    paying({ payWay, hideAvatar, rewardAmount }) {
      if (payWay === 'walletPay') {
        this.createOrder(hideAvatar, rewardAmount, 8, 20)
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
        this.createOrder(hideAvatar, rewardAmount, 8, 10)
          .then(() => {
            this.payOrder().then(
              wechatQrcode => {
                this.wechatQrCode = wechatQrcode;
                this.wxPayActive().then(
                  () => {
                    this.getUserInfo();
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
      this.showCheckoutCounter = false;
    },
    onPasswordInputCompleted(password) {
      this.payOrder(password).then(
        () => {
          this.getUserInfo();
          console.log('支付成功');
        },
        () => console.log('支付失败')
      );
    },
    getUserInfo() {
      const userId = this.$store.state.user.info.id;
      this.$store.dispatch('user/getUserInfo', userId).then(
        () => {
          this.$store.commit('session/SET_USER_ID', userId);
          this.loading = false;
        },
        e => this.$store.commit('user/SET_USER_INFO', {})
      );
    }
  }
};
</script>
<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_pay*/
@import "@/assets/css/variable/color.scss";
/*FITRUE_pay*/

.site-info-container {
  margin-top: 40px !important;
  margin-left: 30px;
  padding-right: 30px;
  padding-bottom: 30px;
  @media screen and (max-width: 1005px) {
    margin-left: 15px;
    padding-right: 15px;
    padding-bottom: 20px;
  }
}
.logo-content{
  font-size:0;
}
.logo {
  max-width: 294px;
  max-height: 55px;
}
.grey-color {
  color: $font-color-grey;
}
.title {
  color: #6D6D6D;
}
// 重置element
.el-button--primary {
  background-color: #1878f3 !important;
  border-color: #1878f3 !important;
  &:hover {
    opacity: 0.8;
  }
}
.base-font-size {
  font-size: 16px;
  @media screen and (max-width: 1005px) {
    font-size: 14px;
  }
}
.site-info {
  display: flex;
  justify-content: space-between;
  margin-top: 40px;
  .avatar {
    display: flex;
    align-items: center;
    .right {
      margin-left: 10px;
      .name {
        max-width: 150px;
      }
    }
  }
  .label {
    color: #6d6d6d;
  }
  .value {
    color: $font-color-grey;
    font-size: 18px;
    margin-top: 2px;
  }
}
.site-detail {
  margin-top: 30px;
  padding-bottom: 27.5px;
  border-bottom: 1px solid $line-color-base;
  .header {
    display: flex;
    justify-content: space-between;
    .modify {
      /*IFTRUE_default*/
      color: $color-blue-base;
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      color: #1878f3;
      /*IFTRUE_pay*/
      cursor: pointer;
      outline: none;
    }
  }
  .content {
    margin-top: 16px;
    color: #000;
    line-height: 24px;
    ::v-deep.el-textarea__inner {
      font-family: inherit;
    }
    .el-button {
      margin-top: 16px;
    }
    &.grey-color {
      color: $font-color-grey;
    }
  }
}
.circlemode {
  margin-top: 20px;
  border-bottom: 1px solid $border-color-base;
  padding-bottom: 20px;
  .content {
    margin-top: 14px;
    margin-left: 20px;
  }
}
.permission {
  margin-top: 20px;
  .user-detail {
    display: flex;
    margin-top: 16px;
    .avatar {
      flex: 0 0 50px;
    }
    .user-info {
      margin-left: 15px;
      .role,
      .join-time {
        color: $font-color-grey;
      }
      .name{
        font-weight: bold;
      }
      .inline {
        display: inline-block;
      }
      .renewal {
        color: #0000bf;
        cursor: pointer;
      }
      .permission-list {
        margin-top: 15px;
        .item {
          display: inline-block;
          font-size: 12px;
          color: #777777;
          background-color: #f7f7f7;
          padding: 6.5px 10px;
          line-height: 1;
          margin-right: 10px;
          margin-bottom: 10px;
          border-radius: 2px;
        }
      }
    }
  }
}

::v-deep .custom-dialog {
  .el-dialog__header {
    font-weight: bold;
    .el-dialog__title {
      color: #6d6d6d;
    }
  }
  .el-dialog__body {
    padding: 0;
  }
  .custom-input {
    textarea {
      background: #f4f5f6;
      border: 1px solid #dcdfe6;
      border-radius: 2px;
      font-family: inherit;
    }
  }
  .dialog-main {
    padding: 30px 20px 45px;
  }
  .dialog-footer {
    background: #f5f6f7;
    text-align: right;
    padding: 10px 20px;
  }
}
</style>
