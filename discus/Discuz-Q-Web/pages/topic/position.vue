<template>
  <div v-loading="index_loading" class="location-container">
    <header v-if="location || address" class="location-info">
      <svg-icon type="location-map" class="icon" />
      <div class="info">
        <div class="location">{{ location }}</div>
        <div class="address">{{ address }}</div>
      </div>
    </header>
    <div class="container">
      <main class="cont-left">
        <div class="thread-count">{{ $t("home.threadCount", { total }) }}</div>
        <div class="post-list">
          <template v-for="(item, index) in threadsData">
            <!-- 语音贴 -->
            /*IFTRUE_default*/
            <post-item
              v-if="item.type === 4"
              :ref="
                `audio${item &&
                  item.threadAudio &&
                  item.threadAudio._jv &&
                  item.threadAudio._jv.id}`
              "
              :key="index"
              :item="item"
              @audioPlay="audioPlay"
            />
            <post-item v-else :key="index" :item="item" />
            /*FITRUE_default*/
            /*IFTRUE_pay*/
            <post-item-pay
              v-if="item.type === 4"
              :ref="
                `audio${item &&
                  item.threadAudio &&
                  item.threadAudio._jv &&
                  item.threadAudio._jv.id}`
              "
              :key="index"
              :item="item"
              @audioPlay="audioPlay"
            />
            <post-item-pay v-else :key="index" :item="item" />
            /*FITRUE_pay*/
          </template>
          <list-load-more
            :loading="loading"
            :has-more="hasMore"
            :page-num="pageNum"
            :length="threadsData.length"
            @loadMore="loadMore"
          />
        </div>
      </main>
      <aside class="cont-right">
        <div class="background-color">
          <advertising />
        </div>
        <div class="recommend-user background-color">
          <recommend-user :list="recommendUserData" />
        </div>
        <copyright :forums="forums" />
      </aside>
    </div>
  </div>
</template>

<script>
import handleError from '@/mixin/handleError';
import head from '@/mixin/head';
import env from '@/utils/env';
const threadInclude = 'user,user.groups,firstPost,firstPost.images,category'
  + ',threadVideo,question,question.beUser,question.beUser.groups,firstPost.postGoods,threadAudio';
export default {
  layout: 'custom_layout',
  name: 'Position',
  mixins: [head, handleError],
  // 异步数据用法
  async asyncData({ store, query }, callback) {
    if (!env.isSpider) {
      callback(null, {});
    }
    const threadsParams = {
      include: threadInclude,
      'filter[isApproved]': 1,
      'filter[isDeleted]': 'no',
      'filter[isDisplay]': 'yes',
      'page[limit]': 10,
      'filter[location]': `${query.longitude},${query.latitude}`
    };
    const userParams = {
      include: 'groups',
      limit: 4
    };
    try {
      const resData = {};
      const threadsData = await store.dispatch('jv/get', [
        'threads',
        { params: threadsParams }
      ]);
      const recommendUser = await store.dispatch('jv/get', [
        'users/recommended',
        { params: userParams }
      ]);
      // 处理一下data
      if (Array.isArray(threadsData)) {
        resData.threadsData = threadsData.slice(0, 10);
      } else if (threadsData && threadsData._jv && threadsData._jv.json) {
        const _threadsData = threadsData._jv.json.data || [];
        _threadsData.forEach((item, index) => {
          _threadsData[index] = {
            ...item,
            ...item.attributes,
            firstPost: item.relationships.firstPost.data,
            user: item.relationships.user.data,
            groups: item.relationships.groups.data,
            _jv: { id: item.id }
          };
        });
        resData.threadsData = _threadsData;
      }
      if (Array.isArray(recommendUser)) {
        resData.recommendUserData = recommendUser;
      } else if (recommendUser && recommendUser._jv && recommendUser._jv.json) {
        const _recommendUser = recommendUser._jv.json.data || [];
        _recommendUser.forEach((item, index) => {
          _recommendUser[index] = { ...item, ...item.attributes };
        });
        resData.recommendUserData = _recommendUser;
      }
      callback(null, resData);
    } catch (error) {
      callback(null, {});
    }
  },
  data() {
    return {
      title: this.$t('home.location'),
      loading: false,
      index_loading: true,
      threadsData: [], // 主题列表
      total: 0,
      recommendUserData: [], // 推荐用户列表
      pageNum: 1, // 当前页码
      pageSize: 10, // 每页多少条数据
      hasMore: false,
      longitude: '', // 经度
      latitude: '', // 纬度
      location: '', // 位置
      address: '', // 详细地址
      currentAudioId: '' // 当前播放语音id
    };
  },
  computed: {
    forums() {
      return this.$store.state.site.info.attributes || {};
    }
  },
  watch: {
    $route: 'init'
  },
  mounted() {
    this.init();
  },
  methods: {
    init() {
      this.threadsData = [];
      if (
        this.$route.query
        && this.$route.query.longitude
        && this.$route.query.latitude
      ) {
        this.longitude = this.$route.query.longitude;
        this.latitude = this.$route.query.latitude;
        this.getThreadsList();
      }
    },
    // 非置顶主题
    getThreadsList() {
      this.loading = true;
      const params = {
        include: threadInclude,
        'filter[isApproved]': 1,
        'filter[isDeleted]': 'no',
        'filter[isDisplay]': 'yes',
        'page[number]': this.pageNum,
        'page[limit]': this.pageSize,
        'filter[location]': `${this.longitude},${this.latitude}`
      };
      this.$store
        .dispatch('jv/get', ['threads', { params }])
        .then(
          res => {
            this.hasMore = res.length === this.pageSize;
            this.total = res._jv.json.meta.threadCount;
            if (this.pageNum === 1) {
              this.threadsData = res;
              if (!this.address && res.length > 0) {
                this.address = res[0].address;
              }
              if (!this.location && res.length > 0) {
                this.location = res[0].location;
              }
            } else {
              this.threadsData = [...this.threadsData, ...res];
            }
            this.pageNum += 1;
            if (res._jv) {
              this.hasMore
                = this.threadsData.length < res._jv.json.meta.threadCount;
            }
          },
          e => {
            this.handleError(e);
          }
        )
        .finally(() => {
          this.loading = false;
          this.index_loading = false;
        });
    },
    loadMore() {
      this.getThreadsList();
    },
    // 语音互斥播放
    audioPlay(id) {
      if (this.currentAudioId && this.currentAudioId !== id) {
        this.$refs[`audio${this.currentAudioId}`][0].pause();
      }
      this.currentAudioId = id;
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
@mixin background() {
  background: #fff;
  border-radius: 5px;
  box-shadow: 0 3px 3px rgba(0, 0, 0, 0.03);
}
.app-cont {
  box-shadow: none;
}
.location-container {
  background: #f4f5f6;
  width: 100%;
  .container {
    display: flex;
    width: 100%;
  }
  .location-info {
    @include background();
    display: flex;
    margin-bottom: 15px;
    padding: 22px 20px 25px;
    .icon {
      font-size: 30px;
      margin-right: 20px;
    }
    .location {
      font-size: 18px;
      font-weight: bold;
      line-height: 24px;
      @media screen and (max-width: 1005px) {
        font-size: 16px;
      }
    }
    .address {
      margin-top: 10px;
    }
  }
  .cont-left {
    flex: auto;
    @include background();
    .thread-count {
      font-size: 18px;
      font-weight: bold;
      padding: 20px 15px 16.5px;
      border-bottom: 1px solid $border-color-base;
      @media screen and (max-width: 1005px) {
        font-size: 16px;
      }
    }
  }
  .cont-right {
    margin-left: 15px;
    width: 300px;
    flex: 0 0 300px;
    @media screen and (max-width: 1005px) {
      width: 220px;
      flex: 0 0 220px;
    }
    .background-color {
      @include background();
      margin-bottom: 3px;
    }
    .category {
      margin-bottom: 0;
    }
  }
}
.empty-icon {
  margin-right: 6px;
}
</style>
