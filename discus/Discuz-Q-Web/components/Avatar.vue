<template>
  <a
    v-if="user"
    class="avatar-a"
    :href="
      preventJump || isAnonymous ? 'javascript:void(0)' : '/user/' + user.id
    "
    :class="[sizeClass]"
  >
    <img
      v-if="(avatarUrl && !errorUrl) || (avatar && !errorUrl)"
      :src="user.avatarUrl || user.avatar"
      class="img"
      :class="[sizeClass, roundClass]"
      :alt="user.username"
      @error="error"
    >
    <span
      v-else-if="styleText"
      :class="['avatar', sizeClass, roundClass]"
      :style="styleText"
    >
      {{ usernameAt }}
    </span>
    <svg-icon
      v-if="user.isReal"
      type="auth"
      :class="{ 'auth-icon': true, 'auth-icon-round': round }"
    />
  </a>
</template>

<script>
import stringToColor from '@/utils/stringToColor';

export default {
  name: 'Avatar',
  props: {
    user: {
      type: [Object, String],
      default: () => {}
    },
    size: {
      type: [Number, String],
      default: 50
    },
    round: {
      type: Boolean,
      default: false
    },
    preventJump: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      errorUrl: false,
      sizes: {
        100: 'font-size: 40px;line-height: 100px;border-radius: 2px',
        80: 'font-size: 32px;line-height: 80px;border-radius: 2px',
        70: 'font-size: 28px;line-height: 70px;border-radius: 2px',
        60: 'font-size: 26px;line-height: 60px;border-radius: 2px',
        50: 'font-size: 24px;line-height: 50px;border-radius: 2px',
        45: 'font-size: 22px;line-height: 45px;border-radius: 2px',
        40: 'font-size: 20px;line-height: 40px;border-radius: 2px',
        43: 'font-size: 20px;line-height: 40px;border-radius: 2px',
        30: 'font-size: 18px;line-height: 30px;border-radius: 2px',
        35: 'font-size: 18px;line-height: 30px;border-radius: 2px',
        20: 'font-size: 10px;line-height: 20px;border-radius: 2px'
      }
    };
  },
  computed: {
    avatarUrl() {
      return (
        this.user.avatarUrl
        && this.user.avatarUrl.indexOf('/static/noavatar.gif') !== 0
      );
    },
    usernameAt() {
      return this.user.username
        ? this.user.username.charAt(0).toUpperCase()
        : '';
    },
    styleText() {
      return `background-color: #${stringToColor(this.usernameAt)};${
        this.sizes[this.size]
      }`;
    },
    sizeClass() {
      return [
        `avatar-size-${this.size}`,
        this.preventJump ? 'prevent-jump' : ''
      ];
    },
    isAnonymous() {
      // return (
      //   this.user._jv && this.user._jv.id && parseInt(this.user._jv.id) < 0
      // );
      return !this.user.id || parseInt(this.user.id) < 0;
    },
    roundClass() {
      return this.round ? 'round' : '';
    },
    avatar() {
      return (
        this.user.avatar
        && this.user.avatar.indexOf('/static/noavatar.gif') !== 0
      );
    }
  },
  methods: {
    error() {
      this.errorUrl = true;
    }
  }
};
</script>

<style lang="scss" scoped>
.avatar-size-100 {
  width: 100px;
  height: 100px;
  border-radius: 2px;
}
.avatar-size-80 {
  width: 80px;
  height: 80px;
  border-radius: 2px;
}

.avatar-size-70 {
  width: 70px;
  height: 70px;
  border-radius: 2px;
}

.avatar-size-65 {
  width: 65px;
  height: 65px;
  border-radius: 2px;
}

.avatar-size-60 {
  width: 60px;
  height: 60px;
  border-radius: 2px;
}
.avatar-size-50 {
  width: 50px;
  height: 50px;
  border-radius: 2px;
}
.avatar-size-45 {
  width: 45px;
  height: 45px;
  border-radius: 2px;
}
.avatar-size-43 {
  width: 45px;
  height: 43px;
  border-radius: 2px;
}
.avatar-size-40 {
  width: 40px;
  height: 40px;
  border-radius: 2px;
}
.avatar-size-35 {
  width: 35px;
  height: 35px;
  border-radius: 2px;
}
.avatar-size-30 {
  width: 30px;
  height: 30px;
  border-radius: 2px;
}
.avatar-size-20 {
  width: 20px;
  height: 20px;
  border-radius: 2px;
}

.round {
  border-radius: 50% !important;
}
.avatar-a {
  display: block;
  position: relative;
}
.avatar {
  color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
}
.auth-icon {
  position: absolute;
  right: -3px;
  bottom: -3px;
  width: 11px;
  height: 13px;
}
.auth-icon-round {
  right: 0;
  bottom: 0;
}
.img {
  object-fit: cover;
}
.prevent-jump {
  cursor: default;
}
</style>
