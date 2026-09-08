<template>
  <div class="title">
    /*IFTRUE_default*/
    <avatar-component v-if="thread.thread && thread.thread.type !== 5" :author="author">
      {{ $t("topic.publishAt") }}
      {{ timerDiff(thread.thread ? thread.thread.createdAt : "") + $t("topic.before") }} .. （{{
        $t("topic.editAt")
      }}
      {{
        timerDiff(thread.firstPost ? thread.firstPost.updatedAt : "") +
          $t("topic.before")
      }}）
      归属地 {{ thread.author.province }}
    </avatar-component>
    <!--问答帖特殊展示-->
    <avatar-component v-else :author="author">
      <span v-if="thread.question && thread.question.type === 0">
        悬赏发布于{{ publishTime(thread.thread ? thread.thread.createdAt : "") }}
      </span>
      <span v-else>
        {{
          timerDiff(thread.thread ? thread.thread.createdAt : "") +
            $t("topic.before") +
            "..." +
            $t("topic.to")
        }}
        <nuxt-link :to="'/user/' + beAskedUser.id" class="be-ask-user">{{
          " @" + beAskedUser.username
        }}</nuxt-link>
        {{ " " + $t("topic.ask") }}
      </span>
    </avatar-component>
    /*FITRUE_default*/

    /*IFTRUE_pay*/
    <avatar-component
      v-if="thread.type !== 5"
      :author="author"
      :size="40"
      :round="true"
    >

      {{ $t("topic.publishAt") }}
      {{ timerDiff(thread.thread ? thread.thread.createdAt : "") + $t("topic.before") }} .. （{{
        $t("topic.editAt")
      }}
      {{
        timerDiff(thread.firstPost ? thread.firstPost.updatedAt : "") +
          $t("topic.before")
      }}）
      <!--span class="kuq-ziti-huise kuq-font-14">归属地： {{thread.author && thread.author.province}}</span-->

    </avatar-component>

    <avatar-component v-else :author="author" :size="40" :round="true">
      <span v-if="thread.question && thread.question.type === 0">
        悬赏发布于{{ publishTime(thread.thread ? thread.thread.createdAt : "") }}
      </span>
      <span v-else>
        {{
          timerDiff(thread.thread ? thread.thread.createdAt : "") + $t('topic.before') + '...' + $t('topic.to')
        }}
        <nuxt-link :to="'/user/' + beAskedUser.id" class="be-ask-user">
          {{ ' @' + beAskedUser.username }}
        </nuxt-link>
        {{ ' ' + $t('topic.ask') }}
      </span>
      <!--span class="kuq-ziti-huise kuq-font-14">归属地： {{thread.author && thread.author.province}}</span-->
    </avatar-component>

    /*FITRUE_pay*/
    <div class="container-management">
      /*IFTRUE_pay*/
      <div
        v-if="thread.category && thread.category.name"
        class="tag"
        @click="toCategoryPage"
      >{{ thread.category.name }}</div>
      /*FITRUE_pay*/
      <div v-if="thread.thread && thread.thread.isApproved === 0" class="checking">审核中</div>
      <el-dropdown
        v-show="
          managementList.length > 0 &&
            managementList.some(item => item.canOpera)
        "
        class="dropdown"
        placement="bottom"
        trigger="click"
        @command="handleCommand"
        @visible-change="visibile => (isManagementDrop = visibile)"
      >
        <div :class="{ management: true, 'on-drop': isManagementDrop }">
          <svg-icon
            type="setting"
            class="icon-setting"
            style="font-size: 16px"
          />
          <span> {{ $t("topic.management") }} </span>
        </div>
        <el-dropdown-menu slot="dropdown" style="padding: 10px">
          <el-dropdown-item
            v-for="(item, index) in managementList"
            :key="index"
            :command="{ command: item.command, item }"
            class="dropdown-item"
          >
            {{ item.text }}
          </el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
      <div class="report" @click="reportClick">
        <svg-icon type="report" class="icon-setting" style="font-size: 16px" />
        <span>{{ $t("report.reportTitle") }}</span>
      </div>
      <topic-report
        v-if="isReport"
        :thread-id="thread.id"
        :type="1"
        @close="isReport = false"
      />
      <div v-if="thread.isEssence" class="essence">
        /*IFTRUE_default*/
        <svg-icon style="font-size: 25px;" type="essence" />
        /*FITRUE_default*/
        /*IFTRUE_pay*/
        <img class="essence-img" src="../../assets/pay_imgs/essenc.png" alt="加精">
        /*FITRUE_pay*/
      </div>
    </div>
  </div>
</template>

<script>
import timerDiff from '@/mixin/timerDiff';
import dayjs from 'dayjs';

export default {
  name: 'TopicHeader',
  mixins: [timerDiff],
  props: {
    author: {
      type: Object,
      default: () => {}
    },
    thread: {
      type: Object,
      default: () => {}
    },
    managementList: {
      type: Array,
      default: () => []
    },
    beAskedUser: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      /* IFTRUE_pay */
      categoryId: '',
      search_ids: '',
      /* FITRUE_pay */
      isManagementDrop: false,
      isReport: false
    };
  },
  /* IFTRUE_pay */
  watch: {
    thread(val) {
      if (val && val.category) {
        this.categoryId = val.category.id;
      }
    }
  },
  /* FITRUE_pay */
  methods: {
    handleCommand({ command, item }) {
      if (command === 'toEdit') {
        return this.$router.push(
          `/thread/postpay?type=${this.thread.thread.type}`
          + `&operating=edit`
          + `&threadId=${this.thread.thread.id}`
          + `&categoryId=${this.thread.category.id}`
        );
      }
      if (command === 'isDeleted') return this.deleteConfirm(item);
      this.$emit('managementSelected', item);
    },
    deleteConfirm(item) {
      this.$confirm(
        this.$t('topic.confirmDelete'),
        this.$t('discuzq.msgBox.title'),
        {
          confirmButtonText: this.$t('discuzq.msgBox.confirm'),
          cancelButtonText: this.$t('discuzq.msgBox.cancel'),
          type: 'warning'
        }
      ).then(() => this.$emit('managementSelected', item));
    },
    publishTime(val) {
      return dayjs(val).format('YYYY-MM-DD HH:mm:ss');
    },
    /* IFTRUE_pay */
    async toCategoryPage() {
      await this.getSearchIds();
      if (this.search_ids === '') {
        this.search_ids = this.thread.category.id;
      }
      if (this.search_ids.indexOf(',') !== -1) {
        const searchIds = this.search_ids.split(',');
        const query = [];
        if (searchIds.length > 0) {
          searchIds.forEach(item => {
            query.push(`search_ids=${item}`);
          });
          this.$router.push(`/category/${this.thread.category.id}?${query.join('&')}`);
        }
      }
      this.$router.push({
        path: `/category/${this.thread.category.id}`,
        query: { search_ids: this.search_ids }
      });
    },
    async getSearchIds() {
      await this.$store.dispatch('jv/get', ['categories', {}]).then((res) => {
        const resData = [...res] || [];
        this.handleData(resData);
      }, (e) => {
        this.handleError(e);
      });
    },
    handleData(data) {
      data.forEach((item) => {
        if (this.categoryId === item.id) {
          this.search_ids = item.search_ids;
        }
      });
    },
    /* FITRUE_pay */
    reportClick() {
      this.isReport = true;
    }
  }
};
</script>

<style>
.dropdown-item {
  border-top: 1px solid #EDEDED;
  width: 98px;
  text-align: center;
}
.dropdown-item:first-child {
  border-top: none !important;
}
</style>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_default*/
.el-dropdown-menu__item:hover {
  background: #ffffff;
  color: $color-blue-base;
}
.title {
  height: 50px;
  display: flex;
  justify-content: space-between;

  .be-ask-user {
    color: $color-blue-base;
    cursor: pointer;
  }
  > .container-management {
    display: flex;
    .checking {
      color: red;
      font-size: 12px;
      margin-right: 0;
      margin-left: 20px;
      height: 16px;
      line-height: 16px;
      margin-top: 5px;
    }
    .report {
      margin-top: 5px;
      font-size: 12px;
      cursor: pointer;
      color: #d0d4dc;
      margin-left: 20px;
      margin-right: 0;
      height: 16px;
      display: flex;
      align-items: center;
      > span {
        margin-left: 5px;
      }
      .warning {
        height: 16px;
      }
    }
    .essence {
      margin-left: 20px;
      margin-right: 0;
    }
    > .dropdown {
      height: 20px;

      > .management {
        display: flex;
        align-items: center;
        margin-top: 5px;
        margin-left: 20px;
        margin-right: 0;
        font-size: 12px;
        cursor: pointer;
        color: $font-color-grey;
        > span {
          margin-left: 5px;
        }

        &:focus {
          border: none;
          outline: none;
        }

        &.on-drop {
          color: $color-blue-base;
          > .icon-setting {
            fill: $color-blue-base;
          }
        }
      }
    }
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
@import '@/assets/css/variable/color_pay.scss';
@import "@/assets/css/pay_scss/TopicHeader.scss";
/*FITRUE_pay*/
</style>
