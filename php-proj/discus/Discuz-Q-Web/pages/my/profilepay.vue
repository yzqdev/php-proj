<template>
  <div v-loading="loading" class="myprofile">
    <!--  资料head -->
    <div v-if="userInfo" class="myprofile-wrap myprofile-head">
      <Avatar
        :user="{
          id: userInfo.id,
          username: userInfo.username,
          avatarUrl: avataruserInfo.avatarUrl,
          isReal: avataruserInfo.isReal
        }"
        :size="65"
        :round="true"
        class="avatar"
      />
      <div class="user">

        <span class="username">
          昵称：{{ userInfo.nickname }}
        </span>

        <span class="username">
          登录名：{{ userInfo.username }}
          <span v-if="userInfo && userInfo.isReal" class="iden">
            <svg-icon type="auth" class="auth-icon" />
            <span class="real">{{ $t('modify.isrealname') }}</span>
          </span>
          <span
            v-else-if="
              userInfo
                && !userInfo.isReal
                && forums
                &&forums.qcloud
                && forums.qcloud.qcloud_faceid
            "
            class="iden"
          >
            <svg-icon type="warning" class="auth-icon" />
            <span class="nreal">{{ $t('modify.norealname') }}</span>
          </span>
        </span>

        <span class="userid">
          ID：{{userInfo.id}}
          {{ userInfo && userInfo.groupsName ? userInfo.groupsName : '' }}
            积分：{{userInfo.integralCount}}   成长值：{{userInfo && userInfo.growup.growupsCount}}
            （ {{userInfo && userInfo.growup.growups}} 级 {{userInfo && userInfo.growup.growupnema}}  ）
        </span>
      </div>
      <span class="setavatar">
        <span class="setbutton" @click="setAvatar">
          {{ $t('modify.setavatar') }}
        </span>
      </span>
      <show-avatar v-if="isShowAvatar" :user-id="userId" @change="changeShow" />
    </div>
    <!-- 个人数据 -->
    <div v-if="userInfo" class="myprofile-wrap noborder">
      <div class="myprofile-data">
        <div class="myprofile-title" @click="toTopic">
          <span>{{ $t('profile.topic') }}</span>
          <span class="num">{{ userInfo.threadCount }}</span>
        </div>
        <div class="myprofile-title" @click="toQuestion">
          <span>{{ $t('profile.question') }}</span>
          <span class="num">{{ userInfo.questionCount }}</span>
        </div>
        <div class="myprofile-title" @click="toFollowing">
          <span>{{ $t('profile.following') }}</span>
          <span class="num">{{ userInfo.followCount }}</span>
        </div>
        <div class="myprofile-title" @click="toFollowers">
          <span>{{ $t('profile.followers') }}</span>
          <span class="num">{{ userInfo.fansCount }}</span>
        </div>
        <div class="myprofile-title" @click="toLikes">
          <span>{{ $t('profile.likes') }}</span>
          <span class="num">{{ userInfo.likedCount }}</span>
        </div>
      </div>

    </div>
    <!-- 昵称 -->
    <div v-if="userInfo" class="myprofile-wrap">
      <span class="sign">{{ $t('profile.nickname') }}：</span>
      <div
        v-show="!isnickNameModify"
        class="sign-content"
        :class="{signcolor: userInfo.nickname ===''}"
      >
        {{ userInfo && userInfo.nickname ? userInfo.nickname:'' }}
      </div>
      <span v-show="userInfo && userInfo.canEditUsername" class="setavatar">
        <span class="setbutton" @click="nicknameModify">
          {{ (!isnickNameModify ? $t('profile.modify') : $t('profile.cancelModify')) }}
        </span>
      </span>
      <!-- 昵称弹框 -->
      <div v-show="isnickNameModify" class="myprofile-bomb-box">
        <el-dialog
          :title="$t('profile.nickname')"
          :visible.sync="isnickNameModify"
          width="620px"
          :before-close="nicknameModify"
        >
          <form class="form">
            <el-input
              ref="nickname"
              v-model="newnickName"
              :placeholder="$t('profile.nickname')"
              class="passbtom"
            />
            <el-button
              type="primary"
              class="ebutton"
              @click="nicknameSub"
            >
              {{ $t('profile.confirmModify') }}
            </el-button>
          </form>
        </el-dialog>
      </div>
    </div>
    <!-- 用户名 -->
    <div v-if="userInfo" class="myprofile-wrap">
      <span class="sign">{{ $t('profile.username') }}：</span>
      <div
        v-show="!isNameModify"
        class="sign-content"
        :class="{signcolor: userInfo.username ===''}"
      >
        {{ userInfo && userInfo.username ? userInfo.username:'' }}
      </div>
      <span v-show="userInfo && userInfo.canEditUsername" class="setavatar">
        <span class="setbutton" @click="usernameModify">
          {{ (!isNameModify ? $t('profile.modify') : $t('profile.cancelModify')) }}
        </span>
      </span>
      <!-- 弹框 -->
      <div v-show="isNameModify" class="myprofile-bomb-box">
        <el-dialog
          :title="$t('profile.username')"
          :visible.sync="isNameModify"
          width="620px"
          :before-close="usernameModify"
        >
          <form class="form">
            <el-input
              ref="username"
              v-model="newName"
              :placeholder="$t('modify.numbermodifitions')"
              class="passbtom"
            />
            <el-button
              type="primary"
              class="ebutton"
              @click="nameSub"
            >
              {{ $t('profile.confirmModify') }}
            </el-button>
          </form>
        </el-dialog>
      </div>
    </div>

    <!-- 签名 -->
    <div v-if="userInfo" class="myprofile-wrap myprofile-signature">
      <span class="sign">{{ $t('modify.signaturetitle') }}：</span>
      <div
        v-show="!isSignModify"
        class="sign-content"
        :class="{signcolor: userInfo.signature ===''}"
      >
        {{ userInfo.signature ? userInfo.signature : $t('modify.nosignature') }}
      </div>
      <span class="setavatar">
        <span class="setbutton" @click="signModify">
          {{ (!isSignModify ? $t('profile.modify') : $t('profile.cancelModify')) }}
        </span>
      </span>
      <div v-show="isSignModify" class="text">
        <el-dialog
          :title="$t('modify.signaturetitle')"
          :visible.sync="isSignModify"
          width="620px"
          :before-close="signModify"
        >
          <form>
            <el-input
              ref="sign"
              v-model="inputVal"
              type="textarea"
              :rows="5"
              :placeholder="$t('modify.inputsignautre')"
              @input="fun"
            />
            <div class="cannum">
              {{ $t('modify.canalsoinput')+ `${num-wordnumber}` + $t('modify.wordnumber') }}
            </div>
            <div class="confirmbtn">
              <div class="allbtn">
                <el-button
                  class="comfirm"
                  type="small"
                  @click="sigComfirm"
                >{{ this.$t('report.confirm') }}</el-button>
                <el-button
                  class="cancel"
                  type="small"
                  @click="signModify"
                >{{ this.$t('report.cancel') }}</el-button>
              </div>
            </div>
          </form>
        </el-dialog>
      </div>
    </div>
    <!-- 手机号 -->
    <div
      v-if="
        userInfo
          && forums
          && forums.qcloud
          && forums.qcloud.qcloud_sms
      "
      class="myprofile-wrap"
    >
      <span class="sign">{{ $t('profile.mobile') }}：</span>
      <div
        v-show="!isMobileModify"
        class="sign-content"
        :class="{signcolor: userInfo.mobile ===''}"
      >
        {{ userInfo.mobile ? userInfo.mobile : $t('modify.setphontitle') }}
      </div>
      <span class="setavatar">
        <span class="setbutton" @click="mobileModify">
          {{
            (!isMobileModify
              ? !userInfo.mobile
                ? $t('profile.bindingmobile')
                : $t('profile.modify')
              : $t('profile.cancelModify')) }}
        </span>
      </span>
      <!-- 修改手机号 -->
      <div
        v-show="(isMobileModify && userInfo.mobile)"
        class="myprofile-bomb-box"
      >
        <div class="pmobile">{{ userInfo.mobile }}</div>
        <verify-phone
          v-if="isMobileModify && userInfo.mobile"
          ref="verifyphone"
          :error="phoneError"
          :mobile="userInfo.mobile"
          @close="oldError"
          @password="oldVerify"
          @sendsms="sendsms2"
        />
      </div>
      <el-dialog
        :title="$t('profile.bindingmobile')"
        :visible.sync="isMobileVerify"
        width="620px"
        :before-close="mobileVerify"
      >
        <form class="form">
          <el-input
            v-model="newphon"
            :placeholder="$t('modify.newphonnumber')"
            :class="isChange ? 'phone-input phonechange': 'phone-input'"
            @input="changeinput"
          />
          <el-button
            class="count-b"
            :class="{disabled: !canClick2}"
            :disabled="!canClick2"
            size="middle"
            @click="sendsms"
          >
            {{ content2 }}
          </el-button>
          <el-input
            v-model="newVerifyCode"
            :placeholder="$t('modify.inputnewverifycode')"
            class="passbtom"
          />
          <el-button
            type="primary"
            class="ebutton"
            @click="newVerify"
          >
            {{ $t('profile.submitchange') }}
          </el-button>
        </form>
      </el-dialog>
      <!-- 新用户绑定手机号 -->
      <div
        v-if="(isMobileModify && !userInfo.mobile)"
        class="myprofile-bomb-box"
      >
        <el-dialog
          :title="$t('profile.bindingmobile')"
          :visible.sync="isMobileModify"
          width="620px"
          :before-close="mobileModify"
        >
          <form class="form">
            <el-input
              ref="oldphone"
              v-model="newphon"
              maxlength="11"
              :placeholder="$t('modify.newphon')"
              :class="isChange ? 'phone-input phonechange': 'phone-input'"
              @input="changeinput"
            />
            <el-button
              class="count-b"
              :class="{disabled: !canClick}"
              :disabled="!canClick"
              size="middle"
              @click="sendsms"
            >
              {{ content }}
            </el-button>
            <el-input
              v-model="setnum"
              :placeholder="$t('modify.newverifycode')"
              class="passbtom"
            />
            <el-button
              type="primary"
              class="ebutton"
              @click="dingphon"
            >
              {{ $t('profile.submitchange') }}
            </el-button>
          </form>
        </el-dialog>
      </div>
    </div>
    <!-- 密码 -->
    <div v-if="userInfo" class="myprofile-wrap">
      <span class="sign">{{ $t('profile.password') }}：</span>
      <div
        v-show="!isPassModify"
        class="sign-content"
        :class="{signcolor: !userInfo.hasPassword}"
      >
        {{
          userInfo && userInfo.hasPassword
            ? $t('profile.isset')
            : $t('profile.withoutsetpass')
        }}
      </div>
      <span class="setavatar">
        <span class="setbutton" @click="passModify">
          {{
            !isPassModify
              ? userInfo && userInfo.hasPassword
                ? $t("profile.modify")
                : "设置"
              : userInfo && userInfo.hasPassword
                ? $t("profile.cancelModify")
                : "取消设置"
          }}
        </span>
      </span>
      <div v-show="isPassModify" class="myprofile-bomb-box">
        <div v-if="userInfo && userInfo.hasPassword">
          <el-dialog
            :title="$t('profile.password')"
            :visible.sync="isPassModify"
            width="620px"
            :before-close="passModify"
          >
            <form class="form">
              <el-input
                ref="oldpass"
                v-model="oldPassWord"
                :placeholder="$t('modify.enterold')"
                class="passbtom"
                type="password"
                show-password
              />
              <el-input
                v-model="newPassWord"
                :placeholder="$t('modify.enterNew')"
                class="passbtom"
                type="password"
                show-password
              />
              <div :class="passerror ? 'rep passerr' : 'rep'">
                <el-input
                  v-model="renewPassword"
                  :placeholder="$t('modify.enterNewRepeat')"
                  :class="passerror ? 'passbtom inputerr':'passbtom'"
                  type="password"
                  show-password
                  @input="notsame"
                  @keyup.enter.native="passSub"
                />
                <div
                  v-if="passerror"
                  class="passerror"
                >{{ $t('modify.reenter') }}</div>
              </div>
              <el-button
                type="primary"
                class="ebutton"
                @click="passSub"
              >{{ $t('profile.submitchange') }}</el-button>
            </form>
          </el-dialog>
        </div>
        <!-- 未设置密码 -->
        <div v-else>
          <el-dialog
            :title="$t('profile.password')"
            :visible.sync="isPassModify"
            width="620px"
            :before-close="passModify"
          >
            <form class="form">
              <el-input
                v-model="newPassWord"
                :placeholder="$t('modify.enterNew')"
                class="passbtom"
                type="password"
                show-password
              />
              <div :class="passerror ? 'rep passerr' : 'rep'">
                <el-input
                  v-model="renewPassword"
                  :placeholder="$t('modify.enterNewRepeat')"
                  :class="passerror ? 'passbtom inputerr':'passbtom'"
                  type="password"
                  show-password
                  @input="notsame"
                  @keyup.enter.native="passSub2"
                />
                <div
                  v-if="passerror"
                  class="passerror"
                >{{ $t('modify.reenter') }}</div>
              </div>
              <el-button
                type="primary"
                class="ebutton"
                @click="passSub2"
              >{{ $t('modify.submit') }}</el-button>
            </form>
          </el-dialog>
        </div>
      </div>
    </div>
    <!-- 微信 -->
    <div
      v-if="
        userInfo
          && forums
          && forums.passport
          && forums.passport.oplatform_close
      "
      class="myprofile-wrap"
    >
      <span class="sign">{{ $t('profile.wechat') }}：</span>
      <div
        v-show="!isWechatModify"
        class="sign-content"
        :class="{signcolor: !userInfo.wechat}"
      >
        {{
          userInfo && userInfo.wechat
            ? userInfo.wechat.nickname
            : $t('profile.withoutbindwechat')
        }}
      </div>
      <el-button type="text" class="setavatar">
        <span class="setbutton" @click="wechatModify">
          {{
            !isWechatModify
              ? userInfo && userInfo.wechat && userInfo.wechat.nickname
                ? "解绑"
                : "绑定"
              : userInfo && userInfo.wechat && userInfo.wechat.nickname
                ? "取消解绑"
                : "取消绑定"
          }}
        </span>
      </el-button>
      <div
        v-show="
          isWechatModify
            && userInfo
            && !userInfo.wechat
        "
        class="wehcat-bind"
      >
        <el-dialog
          :visible.sync="isWechatModify"
          width="180px"
          :before-close="wechatModify"
        >
          <div class="qrcode-text">
            <svg-icon type="wechat-logo" class="wechat-logo" />
            <span class="qrtext">微信扫码绑定</span>
          </div>
          <el-image :src="wechatBind.base64_img" class="qr-code" />
          <div>请用微信扫一扫</div>
          <div class="scanqr">扫码上方二维码</div>
        </el-dialog>
      </div>
    </div>
    <!-- 实名验证 -->
    <!-- qcloud_faceid是否开启实名认证 -->
    <div
      v-if="
        userInfo
          && !userInfo.realname
          && forums
          && forums.qcloud
          && forums.qcloud.qcloud_faceid
      "
      class="myprofile-wrap"
    >
      <span class="sign">{{ $t('modify.realnametitle') }}：</span>
      <div
        v-show="!isRealModify"
        class="sign-content"
        :class="{signcolor: !userInfo.realname}"
      >
        {{
          userInfo && userInfo.realname
            ? $t('profile.isset')
            : $t('profile.withoutcertification')
        }}
      </div>
      <span class="setavatar">
        <span class="setbutton" @click="realModify">
          {{
            (!isRealModify
              ? $t('profile.tocertification')
              : $t('profile.cancelcertification'))
          }}
        </span>
      </span>
      <div v-show="isRealModify" class="myprofile-bomb-box">
        <el-dialog
          :title="$t('modify.realnametitle')"
          :visible.sync="isRealModify"
          width="620px"
          :before-close="realModify"
        >
          <form class="form">
            <el-input
              ref="realname"
              v-model="realName"
              :placeholder="$t('modify.realname')"
              class="passbtom"
            />
            <el-input
              v-model="idNumber"
              :placeholder="$t('modify.enteridnumber')"
              class="passbtom"
            />
            <el-button
              type="primary"
              class="ebutton"
              @click="realSub"
            >{{ $t('profile.comfirmsubmit') }}</el-button>
          </form>
        </el-dialog>
      </div>
    </div>
    <div
      v-if="
        userInfo
          && userInfo.realname
          && forums
          && forums.qcloud
          && forums.qcloud.qcloud_faceid
      "
      class="myprofile-wrap"
    >
      <span class="sign">{{ $t('modify.realnametitle') }}：</span>
      <div class="sign-content">
        {{
          userInfo && userInfo.realname
            ? userInfo.realname +`(${userInfo.identity})`
            :''
        }}
      </div>
    </div>
  </div>
</template>

<script>
import { status } from '@/store/modules/jsonapi-vuex/index';
import head from '@/mixin/head';
import handleError from '@/mixin/handleError';
import bindWechat from '@/mixin/bindWechat';
const tcaptchs = process.client ? require('@/utils/tcaptcha') : '';
export default {
  name: 'Profile',
  layout: 'center_layout',
  mixins: [head, handleError, bindWechat, tcaptchs],
  data() {
    return {
      title: this.$t('profile.myprofile'),
      userInfo: '',
      num: 140,
      wordnumber: '',
      signcontent: '',
      content: this.$t('modify.sendVerifyCode'),
      content2: this.$t('modify.sendVerifyCode'),
      canClick: true,
      canClick2: false,
      captcha: null, // 腾讯云验证码实例
      ticket: '',
      randstr: '',
      newVerifyCode: '', // 新手机验证码
      newPhoneNumber: '', // 新绑定的手机
      newphon: '', // 初始绑定的手机号
      setnum: '', // 初始绑定手机号时收到的验证码
      novice: '',
      oldPassWord: '',
      newPassWord: '',
      renewPassword: '', // 重复新密码
      realName: '', // 真实姓名
      idNumber: '', // 身份证
      newName: '',
      newnickName: '', // 昵称修改
      isSignModify: false,
      isMobileModify: false,
      isPassModify: false,
      isWechatModify: false,
      isRealModify: false,
      isNameModify: false, // 是否可修改
      isnickNameModify: false, // 是否可修改
      isMobileVerify: false, // 旧手机是否验证成功
      loading: true,
      rebind: false, // 是否修改手机号
      isShowAvatar: false, // 是否设置头像
      passerror: false,
      isChange: false,
      phoneError: false
    };
  },
  computed: {
    inputVal: {
      get() {
        return this.signcontent;
      },
      set(newValue) {
        this.signcontent = newValue;
      }
    },
    forums() {
      return this.$store.state.site.info.attributes || {};
    },
    userId() {
      return this.$store.getters['session/get']('userId');
    },
    avataruserInfo() {
      return this.$store.state.user.info.attributes || {};
    }
  },
  watch: {
    userId(id) {
      if (id) this.userinfo();
    }
  },
  mounted() {
    if (this.userId) this.userinfo();
    const { phonebind } = this.$route.query;
    if (phonebind) this.isMobileModify = true;
  },
  methods: {
    countDown(interval) {
      if (!this.canClick) return;
      this.canClick = false;
      this.content = interval + this.$t('modify.retransmission');
      const clock = setInterval(() => {
        interval--;
        this.content = interval + this.$t('modify.retransmission');
        if (interval < 0) {
          clearInterval(clock);
          this.content = this.$t('modify.sendVerifyCode');
          // this.totalTime = 60;
          this.canClick = true;
        }
      }, 1000);
    },
    countDown2(interval) {
      if (!this.canClick2) return;
      this.canClick2 = false;
      this.content2 = interval + this.$t('modify.retransmission');
      const clock = window.setInterval(() => {
        interval--;
        this.content2 = interval + this.$t('modify.retransmission');
        if (interval < 0) {
          window.clearInterval(clock);
          this.content2 = this.$t('modify.sendVerifyCode');
          // this.totalTime = 60;
          this.canClick2 = true;
        }
      }, 1000);
    },
    userinfo() {
      const params = {
        include: 'groups,wechat'
      };
      this.$store.dispatch('jv/get', [`users/${this.userId}`, { params }]).then(
        (res) => {
          this.loading = false;
          this.userInfo = res;
          this.signcontent = this.userInfo.signature;
          this.userInfo.groupsName = this.userInfo.groups
            ? this.userInfo.groups[0].name
            : '';
          this.wordnumber = this.signcontent.length;
        },
        (e) => {
          const { response: { data: { errors }}} = e;
          if (
            errors
            && Array.isArray(errors)
            && errors.length > 0
            && errors[0]
          ) {
            const error = errors[0].detail && errors[0].detail.length > 0
              ? errors[0].detail[0]
              : errors[0].code;
            if (error === 'Invalid includes [wechat]') {
              this.$message.error(error);
              localStorage.removeItem('access_token');
              localStorage.removeItem('user_id');
              window.location.replace('/');
            } else {
              const errorText = errors[0].detail && errors[0].detail.length > 0
                ? errors[0].detail[0]
                : this.$t(`core.${error}`);
              this.$message.error(errorText);
            }
          }
        }
      );
    },
    setAvatar() {
      this.isShowAvatar = true;
    },
    changeShow(val) {
      this.isShowAvatar = val;
      this.$store.dispatch('user/getUserInfo', this.userId);
    },
    // 获取输入字数长度
    fun(e) {
      this.wordnumber = e.length;
    },
    // 修改签名
    signModify() {
      this.isSignModify = !this.isSignModify;
      this.$nextTick(() => {
        this.$refs.sign.focus();
      });
      // this.inputVal = this.signcontent
    },
    // 签名确认修改
    sigComfirm() {
      const params = {
        _jv: {
          type: 'users',
          id: this.userId
        },
        signature: this.inputVal
      };
      status
        .run(() => this.$store.dispatch('jv/patch', params))
        .then(
          (res) => {
            if (res) {
              this.isSignModify = !this.isSignModify;
              this.$message.success(this.$t('modify.modificationsucc'));
              this.userinfo();
            }
          }, (e) => this.handleError(e));
    },
    // 手机位数简单校验
    changeinput() {
      this.isChange = true;
      if (this.newphon === '') this.isChange = false;
      setTimeout(() => {
        this.newphon = this.newphon.replace(/[^\d]/g, '');
      }, 30);
      if (this.newphon.length < 11) {
        this.canClick = true;
        this.canClick2 = false;
      } else if (this.newphon.length === 11) {
        this.canClick = true;
        this.canClick2 = true;
        this.novice = this.newphon.replace(/\s+/g, '');
      }
    },
    // 发送短信接口
    sendsms() {
      if (/^1(3|4|5|6|7|8|9)\d{9}$/.test(this.novice)) {
        if (this.forums.qcloud.qcloud_captcha) {
          this.tcaptcha();
        } else {
          if (this.rebind) {
            this.sendVerifyCode2();
          } else {
            this.setphon();
          }
        }
      } else {
        this.$message.error('手机号错误');
      }
    },
    // 发送短信接口2
    sendsms2() {
      this.newphon = '';
      if (this.forums.qcloud.qcloud_captcha) {
        this.tcaptcha();
      } else {
        // 直接修改手机验证码发送
        this.sendVerifyCode();
      }
    },
    // 腾讯验证码
    tcaptcha() {
      // eslint-disable-next-line no-undef
      this.captcha = new TencentCaptcha(
        this.forums.qcloud.qcloud_captcha_app_id,
        (res) => {
          if (res.ret === 0) {
            this.ticket = res.ticket;
            this.randstr = res.randstr;
            // 验证通过后发布
            if (this.novice) {
              if (this.rebind) {
                this.sendVerifyCode2();
              } else {
                // 新手机验证码发送
                this.setphon();
              }
            } else {
              // 修改手机验证码发送
              this.sendVerifyCode();
            }
          }
        }
      );
      // 显示验证码
      this.captcha.show();
    },
    // 新手机号发送验证码
    setphon() {
      const params = {
        _jv: {
          type: 'sms/send'
        },
        mobile: this.novice,
        type: 'bind',
        captcha_ticket: this.ticket,
        captcha_rand_str: this.randstr
      };
      const postphon = status.run(() =>
        this.$store.dispatch('jv/post', params)
      );
      postphon.then(
        (res) => {
          if (res.interval) this.countDown(res.interval);
          this.ticket = '';
          this.randstr = '';
        },
        (e) => this.handleError(e)
      );
    },
    dingphon() {
      if (this.newphon) {
        this.bindphon();
      }
    },
    // 验证手机号
    bindphon() {
      const params = {
        _jv: {
          type: 'sms/verify'
        },
        mobile: this.newphon,
        code: this.setnum,
        type: 'bind'
      };
      const postphon = status.run(() =>
        this.$store.dispatch('jv/post', params)
      );
      postphon.then(
        (res) => {
          if (res) {
            this.isMobileModify = !this.isMobileModify;
            this.$message.success(this.$t('modify.phontitle'));
            this.userinfo();
            this.$store.dispatch('user/getUserInfo', this.userId);
            this.newphon = '';
          }
        },
        (e) => this.handleError(e)
      );
    },
    // 修改手机号
    mobileModify() {
      this.isMobileModify = !this.isMobileModify;
      // this.$nextTick(() => {
      //   this.$refs.oldphone.focus();
      // });
    },
    mobileVerify() {
      this.isMobileVerify = !this.isMobileVerify;
    },
    // 旧手机验证码发送
    sendVerifyCode() {
      this.canClick = true;
      const params = {
        _jv: {
          type: 'sms/send'
        },
        type: 'verify',
        captcha_ticket: this.ticket,
        captcha_rand_str: this.randstr
      };
      const postphon = status.run(() =>
        this.$store.dispatch('jv/post', params)
      );
      postphon.then(
        (res) => {
          if (res.interval) this.countDown(res.interval);
          this.rebind = true;
          this.ticket = '';
          this.randstr = '';
        },
        (e) => this.handleError(e)
      );
    },
    // 新手机验证码发送
    sendVerifyCode2() {
      const params = {
        _jv: { type: 'sms/send' },
        mobile: this.novice,
        type: 'rebind',
        captcha_ticket: this.ticket,
        captcha_rand_str: this.randstr
      };
      const postphon = status.run(() =>
        this.$store.dispatch('jv/post', params)
      );
      postphon.then(
        (res) => {
          if (res.interval) this.countDown2(res.interval);
          this.rebind = true;
          this.ticket = '';
          this.randstr = '';
        },
        (e) => this.handleError(e)
      );
    },
    // 手机号确认修改
    mobileComfirm() {
      this.oldVerify();
    },
    oldError() {
      this.isMobileModify = false;
      this.phoneError = false;
    },
    oldVerify(oldVerifyCode = '') {
      const params = {
        _jv: { type: 'sms/verify' },
        code: oldVerifyCode,
        type: 'verify'
      };
      this.$store.dispatch('jv/post', params).then(
        (res) => {
          if (res) {
            this.isMobileModify = false;
            this.isMobileVerify = true;
          }
        }, (e) => {
          this.$refs.verifyphone.empty();
          this.phoneError = true;
          this.handleError(e);
        });
    },
    // 验证新手机号
    newVerify() {
      const params = {
        _jv: {
          type: 'sms/verify'
        },
        mobile: this.newphon,
        code: this.newVerifyCode,
        type: 'rebind'
      };
      const postphon = status.run(() =>
        this.$store.dispatch('jv/post', params)
      );
      postphon.then(
        (res) => {
          if (res) {
            this.isMobileModify = false;
            this.isMobileVerify = false;
            this.$message.success(this.$t('modify.phontitle'));
            this.newVerifyCode = '';
            this.newphon = '';
            this.userinfo();
            this.$store.dispatch('user/getUserInfo', this.userId);
          }
        },
        (e) => {
          this.handleError(e);
          this.newVerifyCode = '';
          this.newphon = '';
        });
    },
    // 修改密码
    passModify() {
      this.isPassModify = !this.isPassModify;
      if (this.$refs.oldpass) {
        this.$nextTick(() => {
          this.$refs.oldpass.focus();
        });
      }
    },
    // 密码为空校验
    passSub() {
      if (
        this.oldPassWord
        && this.newPassWord
        && this.renewPassword
        && this.newPassWord === this.renewPassword
      ) {
        this.passwordComfirm();
      } else if (!this.oldPassWord) {
        this.$message.error(this.$t('modify.oldpassword'));
      } else if (!this.newPassWord) {
        this.$message.error(this.$t('modify.newpassword'));
      } else if (this.newPassWord !== this.renewPassword) {
        this.$message.error(this.$t('modify.masstext'));
        this.passerror = true;
      }
    },
    notsame() {
      if (this.newPassWord !== this.renewPassword) {
        this.passerror = true;
      } else {
        this.passerror = false;
      }
    },
    passSub2() {
      if (
        this.newPassWord
        && this.renewPassword
        && this.newPassWord === this.renewPassword
      ) {
        this.passwordComfirm();
      } else if (!this.newPassWord) {
        this.$message.error(this.$t('modify.newpassword'));
      } else if (this.newPassWord !== this.renewPassword) {
        this.$message.error(this.$t('modify.masstext'));
        this.passerror = true;
      }
    },
    passwordComfirm() {
      const params = {
        _jv: {
          type: 'users',
          id: this.userId
        },
        password: this.oldPassWord,
        newPassword: this.newPassWord,
        password_confirmation: this.renewPassword
      };
      const postphon = status.run(() =>
        this.$store.dispatch('jv/patch', params)
      );
      postphon.then(
        (res) => {
          if (res) {
            this.$message.success(this.$t('modify.titlepassword'));
            this.isPassModify = !this.isPassModify;
            this.oldPassWord = '';
            this.newPassWord = '';
            this.renewPassword = '';
            this.passerror = false;
            this.userinfo();
          }
        },
        (e) => this.handleError(e)
      );
    },
    // 微信
    wechatModify() {
      this.isWechatModify = !this.isWechatModify;
      if (this.wehcatTimer) {
        window.clearInterval(this.wehcatTimer);
      }
      if (this.isWechatModify && !this.userInfo.wechat) {
        this.createQRcode();
      } else if (
        this.isWechatModify
        && this.userInfo.wechat
        && this.userInfo.wechat
      ) {
        this.$confirm(`
          <i
            class="el-icon-warning"
            style=" width: 25px;height: 25px;font-size: 25px;color: #E6A23C;position: absolute;left: 0px;"></i>
          <p style="margin-left:40px;">确定解除微信绑定?</p>
          <p style="margin-bottom:50px;margin-left:40px;margin-top:10px;">
            解绑后，您的微信号将不再绑定当前账号，即无法再登录当前账号，确认解除绑定吗？
          </p>`,
        {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          dangerouslyUseHTMLString: true
        })
          .then(() => {
            this.$store.dispatch('jv/delete', [`users/${this.userId}/wechat`])
              .then((res) => {
                if (res) {
                  this.isWechatModify = false;
                  this.userinfo();
                  this.$message({
                    type: 'success',
                    message: '解绑成功!'
                  });
                }
              }, e => this.handleError(e));
          })
          .catch(() => {
            this.isWechatModify = false;
            this.$message({
              type: 'info',
              message: '已取消解绑'
            });
          });
      }
    },
    // 实名认证
    realModify() {
      this.isRealModify = !this.isRealModify;
      this.$nextTick(() => {
        this.$refs.realname.focus();
      });
    },
    // 实名不为空验证
    realSub() {
      if (this.realName && this.idNumber) {
        if (this.idNumber.length < 18 || this.idNumber.length > 18) {
          this.$message.error(this.$t('modify.idtitl'));
        } else {
          this.authentication();
        }
      } else if (!this.realName) {
        this.$message.error(this.$t('modify.emptyname'));
      } else if (!this.idNumber) {
        this.$message.error(this.$t('modify.idcardisempty'));
      }
    },
    authentication() {
      const params = {
        _jv: {
          type: 'users/real'
        },
        realname: this.realName,
        identity: this.idNumber
      };
      const patchname = status.run(() =>
        this.$store.dispatch('jv/patch', params)
      );
      patchname.then(
        (res) => {
          this.isRealModify = !this.isRealModify;
          this.userinfo();
          this.$store.dispatch('user/getUserInfo', this.userId);
          this.$message.success(this.$t('modify.nameauthensucc'));
        }, (e) => {
          // eslint-disable-next-line object-curly-spacing
          const {
            response: {
              data: { errors }
            }
          } = e;
          if (errors[0].statusCode === 422 && errors[0].detail) {
            this.$message.error(errors[0].detail[0]);
          } else if (errors[0].status === '422' && errors[0].detail) {
            this.$message.error(errors[0].detail[0]);
          } else if (errors[0].detail) {
            this.$message.error(errors[0].detail);
          }
        }
      );
    },
    // 昵称修改
    nicknameModify() {
      this.isnickNameModify = !this.isnickNameModify;
      this.$nextTick(() => {
        this.$refs.nickname.focus();
      });
    },
    // 昵称提交
    nicknameSub() {
      if (!this.newnickName) {
        this.$message.error(this.$t('modify.emptyname'));
      } else if (this.newnickName === this.userInfo.nickname) {
        this.$message.error(this.$t('modify.repeatname'));
      } else {
        this.changenickname();
      }
    },
    changenickname() {
      const params = {
        _jv: {
          type: 'users',
          id: this.userId
        },
        nickname: this.newnickName
      };
      const patchname = status.run(() =>
        this.$store.dispatch('jv/patch', params)
      );
      patchname.then((res) => {
        if (res) {
          this.isnickNameModify = !this.isnickNameModify;
          this.$message.success(this.$t('modify.modifysucc'));
        }
        this.userinfo();
        this.$store.dispatch('user/getUserInfo', this.userId);
      },
      (e) => this.handleError(e)
      );
    },
    // 用户名修改
    usernameModify() {
      this.isNameModify = !this.isNameModify;
      this.$nextTick(() => {
        this.$refs.username.focus();
      });
    },
    // 用户名提交
    nameSub() {
      if (!this.newName) {
        this.$message.error(this.$t('modify.emptyname'));
      } else if (this.newName === this.userInfo.username) {
        this.$message.error(this.$t('modify.repeatname'));
      } else {
        this.changename();
      }
    },
    changename() {
      const params = {
        _jv: {
          type: 'users',
          id: this.userId
        },
        username: this.newName
      };
      const patchname = status.run(() =>
        this.$store.dispatch('jv/patch', params)
      );
      patchname.then((res) => {
        if (res) {
          this.isNameModify = !this.isNameModify;
          this.$message.success(this.$t('modify.modifysucc'));
        }
        this.userinfo();
        this.$store.dispatch('user/getUserInfo', this.userId);
      },
      (e) => this.handleError(e)
      );
    },
    toTopic() {
      this.$router.push(`/user/${this.userId}?current=1`);
    },
    toFollowing() {
      this.$router.push(`/user/${this.userId}?current=4`);
    },
    toFollowers() {
      this.$router.push(`/user/${this.userId}?current=5`);
    },
    toLikes() {
      this.$router.push(`/user/${this.userId}?current=3`);
    },
    toQuestion() {
      this.$router.push(`/user/${this.userId}?current=2`);
    }
  }
};
</script>

<style lang='scss' scoped>
@import "@/assets/css/variable/color.scss";
@import "@/assets/css/variable/color_pay.scss";
::v-deep input::-ms-reveal {
  display: none;
}
.myprofile {
  margin-top: 40px !important;
  padding: 0 58px;
  @media screen and (max-width: 1005px) {
    padding: 0 30px;
  }
}
// 资料项容器
.myprofile-wrap {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  min-height: 76px;
  border-bottom: 1px solid $line-color-base;
  &.noborder{
    border-bottom: none;
  }
  // 标志
  .sign {
    margin-right: 10px;
    font-size: 14px;
    color: $font-color-grey;
  }
  // 标志内容
  .sign-content{
    flex: 1;
    font-size: 16px;
    color: #000000;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .signcolor {
    color: #8590a6;
  }
  // 按钮
  .setavatar {
    margin-left: 10px;
    font-size: 14px;
    color: $font-color-grey;
    .setbutton {
      display: block;
      width: 100px;
      text-align: right;
      line-height: 50px;
      cursor: pointer;
      &:hover{
        color: $color-red-pay;
      }
    }
  }
  .text {
    margin-right: 100px;
    .cannum {
      text-align: right;
      margin: 10px 0;
    }
    .confirmbtn {
      background: #f5f6f7;
      display: flex;
      justify-content: flex-end;
      position: absolute;
      width: 100%;
      left: 0;
      .allbtn {
        margin: 10px 19px;
      }
      .comfirm {
        width: 70px;
        height: 35px;
        background: $color-background-hover-red2;
        color: $color-white;
        border-radius: 2px;
        border: none;
        &:hover{
          background:$color-red-pay;
        }
      }
      .cancel {
        width: 70px;
        height: 35px;
        border-radius: 2px;
        margin-left: 5px;
        &:hover{
          color: $color-333;
        }
      }
    }
  }

  .form{
    width:300px;
    margin: 0 auto;
  }
  // 弹框
  .myprofile-bomb-box {
    margin-left: 17px;
    font-size: 16px;
    margin-right: 55px;
    padding-bottom: 20px;
    margin-top: 15px;
    width: 460px;
    overflow: hidden;
    overflow-wrap: break-word;
    @media screen and (max-width: 1005px) {
      width: 390px;
    }
    color: #000000;
    .form{
      width:300px;
      margin: 0 auto;
    }
  }
    // 弹框按钮
  .ebutton {
    display: block;
    width: 300px;
    margin-bottom: 48px;
    margin-top: 10px;
    color: $color-333;
    background: $color-white!important;
    border: 1px solid $color-red-pay !important;
    transition: all .3s;
    span{
      font-size: 16px;
    }
    &:hover {
      color: $color-white;
      background: $color-red-pay !important;
    }
  }

  .phone-input {
    width: 209px;
    margin-bottom: 10px;
    ::v-deep.el-input__inner {
      border-right: none;
      border-bottom-right-radius: 0px;
      border-top-right-radius: 0px;
    }
  }
  .count-b {
    width: 90px;
    height: 40px;
    padding: 0;
    margin-left: -4px;
    color: #606162;
    vertical-align: top;
    border-bottom-left-radius: 0px;
    border-top-left-radius: 0px;
  }
  .disabled {
    background-color: $color-background-hover-red1;
    border-color: $color-background-hover-red1;
    color: #b5b5b5;
    cursor: not-allowed; // 鼠标变化
  }
  .passbtom {
    width: 300px;
    margin-bottom: 10px;
  }
  .phonechange {
    font-size: 20px;
    font-weight: bold;
    ::v-deep .el-input__inner {
      color: #000000;
    }
  }
  .rep {
    position: relative;
    .passerror {
      position: absolute;
      bottom: -10px;
      left: 18px;
      font-size: 14px;
      color: #fa5151;
    }
  }
  .passerr {
    margin-bottom: 30px;
    height: 56px;
  }
  .inputerr {
    ::v-deep .el-input__inner {
      border: 1px solid #fa5151;
      color: #fa5151;
    }
  }
}

// 1 个人资料头
.myprofile-head {
  margin-bottom: 34px;
  border-bottom: none;
  .avatar{
    font-size: 20px;
  }
  .user {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-left: 20px;
    font-size: 16px;
    .nickname {
      font-size: 22px;
      font-weight: bold;
      color: #484848;
      line-height: 34px;
      // 实名
      .iden {
        margin-left: 5px;
        display: inline-block;
        font-weight: 100;
        font-size: 14px;
        .auth-icon {
          width: 16px;
          height: 16px;
          margin-right: 2px;
        }
        .nreal {
          color: #fa5151;
          line-height: 34px;
        }
      }
    }
    .username {
      font-size: 16px;
      font-weight: bold;
      color: #000;
      line-height: 34px;
      // 实名
      .iden {
        margin-left: 5px;
        display: inline-block;
        font-weight: 100;
        font-size: 14px;
        .auth-icon {
          width: 16px;
          height: 16px;
          margin-right: 2px;
        }
        .nreal {
          color: #fa5151;
          line-height: 34px;
        }
      }
    }
    .userid {
      font-size: 16px;
      color: #999999;
      line-height: 30px;
    }
  }
}

// 2 个人资料数据
.myprofile-data {
  flex: 1;
  margin-bottom: 16px;
  display: flex;
  justify-content: space-around;
  align-items: center;
  background: #f9fafb;
  border-bottom: none;
  .myprofile-title{
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 90px;
    font-size: 16px;
    color: #999999;
    cursor: pointer;
    transition: background .5s;
    &:hover{
      background: $color-background-hover-red1;
      font-size: 18px;
    }
    .num{
      margin-top: 16px;
      font-size: 14px;
      color: #000;
    }
  }
}

::v-deep .el-input__inner {
  border-radius: 2px;
  &:focus {
    background: transparent !important;
    border-color: $color-background-hover-red1;
  }
}
::v-deep .el-button {
  border-radius: 2px;
  &:hover{
    border-color: $color-background-hover-red1;
    background: $color-background-hover-red1;
  }
  &.setavatar:hover{
    border: none;
    background: none;
  }
}

::v-deep .el-textarea__inner {
  font-family: inherit;
  &:focus{
    border-color: $color-background-hover-red2;
  }
}
::v-deep.warningicon{
  width: 25px;
  height: 25px;
  font-size: 25px;
  color: #E6A23C;
  position: absolute;
  left: 0px;
}
.wehcat-bind {
  padding-top: 20px;
  width: 155px;
  text-align: center;
  ::v-deep .el-dialog__header{
    padding: 0;
  }
  ::v-deep .el-dialog__body{
    padding: 0;
  }
  ::v-deep .el-icon-close{
    display:none;
  }
  .qrcode-text{
    padding-top: 15px;
    .qrtext{
      vertical-align: super;
    }
  }
  .wechat-logo{
    width:25.5px;
    height: 25.5px;
  }
  .scanqr{
    padding-bottom:22px;
  }
}
.qr-code {
  width: 155px;
  height: 155px;
  margin-bottom: -5px;
  img {
    width: 100%;
    height: 100%;
  }
}
</style>
