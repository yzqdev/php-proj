<template>
  <div class="notice-container">
    <!-- 消息菜单导航 -->
    <el-tabs v-model="activeName" @tab-click="handleClick">
      <el-tab-pane v-for="(item, index) in noticeTypeList" :key="index" :name="item.value">
        <span slot="label">
          <el-badge
            :is-dot="(
              (item.value !== 'chat'
                && userInfo.typeUnreadNotifications
                && userInfo.typeUnreadNotifications[item.value])
              || (item.value === 'rewarded'
                && userInfo.typeUnreadNotifications
                && userInfo.typeUnreadNotifications.threadrewarded)
              || (item.value === 'rewarded'
                && userInfo.typeUnreadNotifications
                && userInfo.typeUnreadNotifications.receiveredpacket)
            )
              ? true
              : false
            "
            class="badge-item"
          >{{ item.label }}</el-badge>
        </span>
      </el-tab-pane>
    </el-tabs>
    <!-- 消息列表 -->
    <div class="notice-list">
      <!-- 私信 -->
      <template v-if="activeName === 'chat'">
        <div v-for="(item, index) in dialog.list" :key="index" class="notice-item">
          <chat-item :user-id="userInfo.id" :item="item" @show-chat-box="showChatBox" />
          <div class="delete" @click="handleDelete(item._jv && item._jv.id, index, 'chat')">
            <svg-icon type="close" />
          </div>
        </div>
        <list-load-more
          class="moreheight"
          :loading="dialog.loading"
          :has-more="dialog.hasMore"
          :page-num="dialog.pageNum"
          :surplus="surplus"
          :length="dialog.list.length"
          @loadMore="loadMoreDialog"
        />
      </template>
      <!-- 除私信之外的消息 -->
      <template v-else>
        <div v-for="(item, index) in noticeList" :key="index" class="notice-item">
          <notice-item :item="item" />
          <div class="delete" @click="handleDelete(item.id, index, 'notice')">
            <svg-icon type="close" />
          </div>
        </div>
        <list-load-more
          class="moreheight"
          :loading="loading"
          :has-more="hasMore"
          :page-num="pageNum"
          :surplus="surplus"
          :length="noticeList.length"
          @loadMore="loadMore"
        />
      </template>
    </div>
    <!-- 聊天框 -->
    <chat-box v-if="chatting" :dialog="dialogData || {}" @close="closeChatBox" />
  </div>
</template>

<script>
import head from '@/mixin/head';
import handleError from '@/mixin/handleError';
export default {
  name: 'Notice',
  layout: 'center_layout',
  mixins: [head, handleError],
  data() {
    return {
      title: this.$t('profile.notice'),
      activeName: 'chat',
      // 消息类型
      noticeTypeList: [
        { // 私信
          label: this.$t('notice.message'),
          value: 'chat'
        }, { // @我的
          label: this.$t('notice.relate'),
          value: 'related'
        }, { // 回复我的
          label: this.$t('notice.reply'),
          value: 'replied'
        }, { // 点赞我的
          label: this.$t('notice.like'),
          value: 'liked'
        }, { // 财务通知
          label: this.$t('notice.reward'),
          value: 'rewarded'
        }, { // 问答通知
          label: this.$t('notice.questionsAnswers'),
          value: 'questioned'
        }, { // 系统通知
          label: this.$t('notice.system'),
          value: 'system'
        }
      ],
      noticeList: [], // 消息列表
      pageNum: 1, // 第几页
      pageSize: 10, // 每页几条数据
      loading: false, // 是否加载消息
      hasMore: false, // 是否存在更多消息
      surplus: 0, // 剩余多少
      dialog: { // 私信相关数据
        pageNum: 1,
        pageSize: 10,
        list: [],
        hasMore: false,
        loading: false
      },
      chatting: false, // 是否显示聊天框
      dialogData: { id: '', name: '' }, // 聊天数据
      emojiList: [] // 表情
    };
  },
  computed: {
    userInfo() {
      return this.$store.state.user.info.attributes || {};
    },
    userId() {
      return this.$store.getters['session/get']('userId');
    }
  },
  mounted() {
    this.getDialogList();
    this.getEmojiList();
  },
  methods: {
    // 获取会话列表
    getDialogList() {
      this.dialog.loading = true;
      const params = {
        include: 'dialogMessage,sender,recipient,sender.groups,recipient.groups',
        sort: '-dialogMessageId',
        'page[number]': this.dialog.pageNum,
        'page[limit]': this.dialog.pageSize
      };
      this.$store.dispatch('jv/get', ['dialog', { params }]).then(async(data) => {
        this.dialog.hasMore = data.length === this.dialog.pageSize;
        if (this.dialog.pageNum === 1) {
          this.dialog.list = data;
        } else {
          this.dialog.list = [...this.dialog.list, ...data];
        }
        this.dialog.pageNum += 1;
        if (data._jv && data._jv.json && data._jv.json.meta) {
          this.dialog.hasMore = this.dialog.list.length < data._jv.json.meta.total
          && this.dialog.list.length >= this.dialog.pageSize;
          this.surplus = data._jv.json.meta.total - this.dialog.list.length;
        }
      }, (e) => {
        this.handleError(e);
      })
        .finally(() => {
          this.dialog.loading = false;
        });
    },
    // 获取消息列表
    getNoticeList() {
      this.loading = true;
      const params = {
        'filter[type]': this.activeName,
        'page[number]': this.pageNum,
        'page[limit]': this.pageSize
      };
      // 财务通知里面包括提现信息
      if (this.activeName === 'rewarded') {
        params['filter[type]'] = 'rewarded,withdrawal,threadrewarded,receiveredpacket,threadrewardedexpired';
      }
      this.$store.dispatch('jv/get', ['notification', { params }]).then(async(data) => {
        this.hasMore = data.length === this.pageSize;
        if (this.pageNum === 1) {
          this.noticeList = data;
        } else {
          this.noticeList = [...this.noticeList, ...data];
        }
        this.getContentEmoji();
        if (data._jv && data._jv.json && data._jv.json.meta) {
          this.hasMore = this.noticeList.length < data._jv.json.meta.total && this.noticeList.length >= this.pageSize;
          this.surplus = data._jv.json.meta.total - this.noticeList.length;
        }
        this.pageNum += 1;
        try {
          await this.$store.dispatch('user/getUserInfo', this.userInfo.id);
        } catch (err) {
          console.log('getUserInfo err', err);
        }
      }, (e) => {
        this.handleError(e);
      })
        .finally(() => {
          this.loading = false;
        });
    },
    // 加载消息列表 除私信之外的消息
    loadMore() {
      this.getNoticeList();
    },
    // 加载私信列表
    loadMoreDialog() {
      this.getDialogList();
    },
    // 删除
    handleDelete(id, index, type) {
      this.$confirm(this.$t('topic.confirmDelete'), this.$t('discuzq.msgBox.title'), {
        confirmButtonText: this.$t('discuzq.msgBox.confirm'),
        cancelButtonText: this.$t('discuzq.msgBox.cancel')
      }).then(() => {
        if (type === 'chat') { // 删除私信
          this.$store.dispatch('jv/delete', `dialog/${id}`).then((res) => {
            if (res) {
              this.$message.success(this.$t('topic.deleteSuccess'));
              this.dialog.list.splice(index, 1);
            }
          });
        } else { // 删除其他消息
          this.$store.dispatch('jv/delete', `notification/${id}`).then((res) => {
            if (res) {
              this.$message.success(this.$t('topic.deleteSuccess'));
              this.noticeList.splice(index, 1);
            }
          });
        }
      })
        .catch(() => {});
    },
    // 点击切换菜单
    handleClick(e) {
      if (e.name !== 'chat') { // 私信
        this.pageNum = 1;
        this.noticeList = [];
        this.getNoticeList();
      } else { // 其他消息
        this.dialog.pageNum = 1;
        this.dialog.list = [];
        this.getDialogList();
      }
    },
    // 显示聊天框
    showChatBox(item) {
      if (!item) return;
      this.dialogData.id = item._jv ? item._jv.id : '';
      if (this.userId * 1 !== item.sender_user_id) {
        this.dialogData.name = item.sender ? item.sender.username : '';
      } else {
        this.dialogData.name = item.recipient ? item.recipient.username : '';
      }
      this.chatting = true;
    },
    // 关闭聊天框
    closeChatBox() {
      this.chatting = false;
      this.dialog.pageNum = 1;
      this.getDialogList();
    },
    getEmojiList() {
      this.$store.dispatch('jv/get', ['emoji', {}]).then(data => {
        this.emojiList = data;
      });
    },
    // 解析系统消息中的表情
    getContentEmoji() {
      this.noticeList.forEach(noticeItem => {
        if (noticeItem.type === 'system') {
          const systemCon = noticeItem;
          // 获取表情请求
          this.emojiList.forEach(item => {
            // 如果内容中存在表情code，则将code替换为图片
            if (systemCon.content.includes(item.code)) {
              systemCon.content = systemCon.content.replace(
                item.code,
                `<img style="display:inline-block;vertical-align:top;" src="${item.url}" class="qq-emotion"/>`
              );
            }
          });
        }
      });
    }
  }
};
</script>
<style lang='scss' scoped>
@import '@/assets/css/variable/color.scss';
.notice-container{
  .el-tabs ::v-deep{
    .el-tabs__header{
      margin: 0;
    }
    .el-tabs__active-bar, .el-tabs__nav-wrap::after {
      height: 0;
    }
    .el-tabs__nav-wrap{
      border-bottom: 1px solid $line-color-base;
      padding-bottom:5px;
      padding-left: 30px;
    }
    .el-tabs__item{
      font-size: 16px;
      color: #B5B5B5;
      padding: 0 25px;
      &.is-active{
        color:#000;
        font-size: 18px;
        font-weight:bold;
      }
    }
  }
  .badge-item{
    ::v-deep .el-badge__content.is-fixed{
      top: 10px;
    }
  }
  .notice-list{
    min-height: 400px;
    .notice-item{
      position: relative;
      transition: all 0.2s ease-in-out;
      &:hover{
        background: #FAFBFC;
        .delete{
          display: block;
        }
      }
      .delete{
        display: none;
        position: absolute;
        /*IFTRUE_default*/
        top:32px;
        /*FITRUE_default*/
        /*IFTRUE_pay*/
        top:70px;
        /*FITRUE_pay*/
        right: 32px;
        cursor: pointer;
        color: #6d6d6d;
      }
    }
    .moreheight {
      padding-top: 34px;
    }
  }
}
</style>
