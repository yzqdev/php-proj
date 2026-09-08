<template>
  <div
    v-if="(forums.agreement && forums.agreement.register) || (forums.agreement && forums.agreement.privacy)"
    class="reg-agreement"
  >
    <el-checkbox
      v-if="check"
      v-model="checked"
      @change="rcheck"
    />
    <span class="agree">{{ $t('permission.user.agreement') }}</span>
    <span
      v-if="forums.agreement && forums.agreement.register"
      class="regagree"
      @click="open('register')"
    >{{ `《${this.$t('permission.user.agreementRegister')}》` }}
    </span>
    <span
      v-if="forums.agreement && forums.agreement.privacy"
      class="regagree"
      @click="open('privacy')"
    >{{ `《${this.$t('permission.user.agreementPrivacy')}》` }}
    </span>
  </div>
</template>

<script>
export default {
  name: 'RegAgreement',
  props: {
    check: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      forums: '',
      popTitle: '',
      popDetail: '',
      showagree: false,
      checked: true
    };
  },
  mounted() {
    this.getAttachMent();
  },
  methods: {
    getAttachMent() {
      const params = {
        include: 'users',
        'filter[tag]': 'agreement'
      };
      this.$store.dispatch('jv/get', ['forum', { params }]).then((res) => {
        this.forums = res;
      });
    },
    open(type) {
      this.$router.push(`/user/agreement?type=${type}`);
    },
    rcheck() {
      this.$emit('check', this.checked);
    }
  }
};
</script>
<style lang='scss' scoped>
@import "@/assets/css/variable/color.scss";
.reg-agreement {
  display: inline-block;
  /*IFTRUE_pay*/ 
  margin-top: 20px;
  /*FITRUE_pay*/ 
  .agree {
    color: #6d6d6d;
    margin-left:6px;
  }
  .regagree {
    /*IFTRUE_default*/
    color: #409eff;
    cursor: pointer;
    margin-left: -3px;
    &:hover {
      color: $color-blue-base;
    }
    /*FITRUE_default*/
    /*IFTRUE_pay*/ 
    color: rgba(47, 133, 244, 0.8);
    cursor: pointer;
    &:hover {
      color: #1878f3;
    }
    /*FITRUE_pay*/ 
  }
  .moreheight {
    position: absolute;
    bottom: -2650px;
  }
  .content {
    height: 500px;
    width: 1005px;
    white-space: pre-wrap;
    line-height: 30px;
    padding-top: 31px;
    padding: 50px 40px 0 40px;
    color: #6d6d6d;
    @media screen and (max-width: 1005px) {
      width: 100%;
      min-width: 768px;
    }
  }
  ::v-deep .el-checkbox__label {
    padding-left: 0px;
  }
}
</style>
