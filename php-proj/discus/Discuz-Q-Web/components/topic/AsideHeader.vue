<template>
  <div class="author">
    <div class="title">{{ $t("topic.aboutAuthor") }}</div>
    /*IFTRUE_default*/
    <avatar-component :author="author" class="author-info">
    /*FITRUE_default*/
    /*IFTRUE_pay*/
    <avatar-component :author="author" :round="true" class="author-info">
    /*FITRUE_pay*/
      {{ $t("topic.activeAt") }} {{ timerDiff(author.updatedAt)
      }}{{ $t("topic.before") }}
    </avatar-component>
    <div class="signature">{{ author.signature }}</div>
    <div class="container-billBoard">
      <div
        v-for="(item, index) in billboard"
        :key="index"
        class="billBoard-item"
        @click="toprofile(index)"
      >
        <div class="text">{{ item.text }}</div>
        <div class="count">{{ item.count }}</div>
      </div>
    </div>
    <div v-if="canOpera" class="buttons">
      <el-button
        type="primary"
        :loading="followLoading"
        @click="followStatus === 0 ? $emit('follow') : $emit('unFollow')"
      >
        {{
          followStatus === 0
            ? "+ " + $t("profile.following")
            : followStatus === 1
              ? $t("profile.followed")
              : $t("profile.mutualfollow")
        }}
      </el-button>
      <el-button @click="openChatBox">{{ $t("topic.sendMessage") }}</el-button>
    </div>
    <chat-box
      v-if="chatting"
      :dialog="dialog || {}"
      @close="chatting = false"
    />
  </div>
</template>

<script>
const include = 'groups,dialog';
import timerDiff from '@/mixin/timerDiff';
import handleError from '@/mixin/handleError';

export default {
  name: 'AsideHeader',
  mixins: [handleError, timerDiff],
  props: {
    author: {
      type: Object,
      default: () => {}
    },
    billboard: {
      type: Array,
      default: () => []
    },
    followStatus: {
      type: Number,
      default: 0
    },
    canOpera: {
      type: Boolean,
      default: false
    },
    followLoading: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      dialog: { id: '', name: '' },
      chatting: false
    };
  },
  watch: {
    author: {
      handler() {
        this.getAuthorInfo();
      }
    },
    deep: true
  },
  mounted() {
    this.getAuthorInfo();
  },
  methods: {
    getAuthorInfo() {
      if (!this.author.id || (this.author.id && this.author.id === -1)) return;
      return this.$store
        .dispatch('jv/get', [
          `users/${this.author.id}`,
          { params: { include }}
        ])
        .then(
          res => {
            if (res.dialog) this.dialog.id = res.dialog._jv.id;
            this.dialog.name = this.author.username;
          },
          e => this.handleError(e)
        );
    },
    openChatBox() {
      this.getAuthorInfo();
      this.chatting = true;
    },
    toprofile(index) {
      if (!this.author.id || parseInt(this.author.id) < 0) return;
      this.$router.push(`/user/${this.author.id}?current=${index + 1}`);
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_pay*/
@import '@/assets/css/variable/color_pay.scss';
/*FITRUE_pay*/

.author {
  margin-top: 0;
  padding: 20px;
  background: #ffffff;
  border-radius: 3px;
  box-shadow: 0 3px 3px rgba(0, 0, 0, 0.03);
  > .title {
    font-weight: bold;
    font-size: 16px;
    color: #6d6d6d;
  }

  > .author-info {
    margin-top: 20px;
  }

  > .signature {
    font-size: 12px;
    word-break: break-all;
    margin-top: 10px;
    color: $font-color-grey;
  }

  > .container-billBoard {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid $border-color-base;
    display: flex;
    justify-content: space-between;

    > .billBoard-item {
      flex: 1;
      text-align: center;
      cursor: pointer;

      > .text {
        color: $font-color-grey;
        font-size: 12px;
      }

      > .count {
        font-weight: bold;
        font-size: 16px;
        line-height: 22px;
      }
    }
  }
  /*IFTRUE_default*/
  > .buttons {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;

    ::v-deep.el-button {
      width: 120px;
    }

    > button {
      height: 40px;
      &:nth-child(1) {
        color: white;
      }
    }

    ::v-deep.el-button--default {
      color: $color-blue-base;
      border: 1px solid $color-blue-base;
      &:hover {
        background: #e5f2ff !important;
        border: 1px solid #d4e6fc;
      }
    }

    @media screen and (max-width: 1005px) {
      ::v-deep.el-button {
        width: 85px;
      }
    }
  }
  /*FITRUE_default*/
  /*IFTRUE_pay*/
  > .buttons {
    display: flex;
    justify-content: space-around;
    margin-top: 20px;
    border-top: 1px solid $border-color-base;
    .el-button {
      margin-top: 20px;
      width: 110px;
      height: 40px;
      color: $color-red-pay;
      background: white !important;
      border: 1px solid $color-red-pay !important;
      border-radius: 20px !important;
      transition: all .5s;
      &:hover {
        background: $color-red-pay !important;
        color: white;
      }
    }
    @media screen and (max-width: 1005px) {
      ::v-deep.el-button {
        width: 85px;
      }
    }
  }
  /*FITRUE_pay*/
}
</style>
