<template>
  <div class="container">
    /*IFTRUE_default*/
    <main class="cont-left">
      <index-filter
        @onChangeFilter="onChangeFilter"
        @onChangeType="onChangeType"
        @onChangeSort="onChangeSort"
      />
      <div v-if="threadsStickyData.length > 0" class="list-top">
        <div
          v-for="(item, index) in threadsStickyData"
          :key="index"
          class="list-top-item"
        >
          <div class="top-label">{{ $t("home.sticky") }}</div>
          <nuxt-link
            :to="`/thread/${item && item.pid}`"
            target="_blank"
            class="top-title"
          >
            <div
              v-html="
                $xss(formatRichText(item.title))
              "
            />
          </nuxt-link>
        </div>
      </div>
      <div v-if="total > 0" class="new-post">
        <div class="new-post-cont">
          {{ $t("home.hasNewContent", { total }) }}
          <span class="refresh" @click="reloadThreadsList">{{
            $t("home.clickRefresh")
          }}</span>
        </div>
      </div>
      <div v-if="isSpider" class="post-list">
        <template v-for="(item, index) in threadsData">
          <post-items
            v-if="item.type === 4"
            :ref="`audio${ item.thread && item.thread.extension
              && item.thread.extension.video && item.thread.extension.video.pid}`"
            :key="index"
            :item="item"
            @audioPlay="audioPlay"
          />
          <post-items v-else :key="index" :item="item" />
        </template>
      </div>
      <!-- 长列表优化 -->
      <dynamic-scroller v-else :items="threadsData" :min-item-size="120" page-mode>
        <template v-slot="{ item, index, active }">
          <dynamic-scroller-item
            :item="item"
            :active="active"
            :data-index="index"
          >
            <post-items
              v-if="item.thread.type === 4"
              :ref="
                `audio${item.thread
                  && item.thread.extension
                  && item.thread.extension.video
                  && item.thread.extension.video.pid}`
              "
              :key="index"
              :item="item"
              :lazy="false"
              @audioPlay="audioPlay"
            />
            <post-items
              v-else
              :key="index"
              :item="item"
              :lazy="false"
              @showVideoFn="showVideoFn"
            />
          </dynamic-scroller-item>
        </template>
        <template #after>
          <list-load-more
            :loading="loading"
            :has-more="hasMore"
            :page-num="pageNum"
            :length="threadsData.length"
            @loadMore="loadMore"
            @scrollDdata="scrollDdata"
          />
        </template>
      </dynamic-scroller>
    </main>
    <aside class="cont-right">
      <div class="category background-color">
        <!-- <category :list="categoryData" @onChange="onChangeCategory" /> -->
        <category-pays :list="categoryData" @onChange="onChangeCategory" />
      </div>
      <div class="background-color">
        <advertising />
      </div>
      <div class="recommend-user background-color">
        <recommend-user :list="recommendUserData" />
      </div>
      <copyright :forums="forums" />
    </aside>
    /*FITRUE_default*/
    /*IFTRUE_pay*/
    <main class="cont-center">
      <div class="cont-center-head">
        <index-filter
          :show-default="false"
          @onChangeFilter="onChangeFilter"
          @onChangeType="onChangeType"
          @onChangeSort="onChangeSort"
        />
        <div v-if="threadsStickyData.length > 0">
          <div
            v-for="(item, index) in threadsStickyData"
            :key="index"
            class="list-top-item"
          >
            <div class="top-label">{{ $t('home.sticky') }}</div>
            <nuxt-link
              :to="`/thread/${item && item.pid}`"
              target="_blank"
              class="top-title"
            >
              <span v-html="$xss(formatRichText(item.title))" />
            </nuxt-link>
          </div>
        </div>
        <div v-if="total > 0" class="new-post">
          <div class="new-post-cont">
            {{ $t('home.hasNewContent', { total }) }}
            <span class="refresh" @click="reloadThreadsList">
              {{ $t('home.clickRefresh') }}
            </span>
          </div>
        </div>
      </div>
      <div v-if="isSpider" class="post-list">
        <template v-for="(item, index) in threadsData">
          <post-items
            v-if="item.type === 4"
            :ref="`audio${ item.thread && item.thread.extension
              && item.thread.extension.video && item.thread.extension.video.pid}`"
            :key="index"
            :item="item"
            @audioPlay="audioPlay"
          />
          <post-items v-else :key="index" :item="item" />
        </template>
      </div>
      <!-- 长列表优化 -->
      <dynamic-scroller
        v-else
        :items="threadsData"
        :min-item-size="120"
        page-mode
      >
        <template v-slot="{ item, index, active }">
          <dynamic-scroller-item
            :item="item"
            :active="active"
            :data-index="index"
          >
            <post-item-pays
              v-if="item.thread.type === 4"
              :ref="
                `audio${item.thread
                  && item.thread.extension
                  && item.thread.extension.video
                  && item.thread.extension.video.pid}`
              "
              :key="index"
              :item="item"
              :lazy="false"
              @audioPlay="audioPlay"
            />
            <post-item-pays
              v-else
              :key="index"
              :item="item"
              :lazy="false"
              @showVideoFn="showVideoFn"
            />
          </dynamic-scroller-item>
        </template>
        <template #after>
          <list-load-more
            :loading="loading"
            :has-more="hasMore"
            :page-num="pageNum"
            :length="threadsData.length"
            @loadMore="loadMore"
            @scrollDdata="scrollDdata"
          />
        </template>
      </dynamic-scroller>
    </main>
    <aside class="cont-left">
      <div class="category background-color">
        <category-pays :list="categoryData" @onChange="onChangeCategory" />
      </div>
    </aside>
    <div class="cont-right">
      <div class="background-color">
        <advertising />
      </div>
      <div class="background-color">
        <recommend-content :list="recommendContentData" />
      </div>
      <copyright :forums="forums" />
    </div>
    /*FITRUE_pay*/
    <!-- 视频播放弹窗 -->
    <dplayer-pop
      v-if="showVideoPop"
      :cover-url="threadVideo.coverUrl"
      :url="threadVideo.mediaUrl"
      @remove="showVideoPop = false"
    />
  </div>
</template>

<script>
import s9e from '@/utils/s9e';
import head from '@/mixin/head';
import handleThreadList from '@/mixin/dataProcessing';
import handleError from '@/mixin/handleError';
import env from '@/utils/env';
import apiRequest from '../../api/new-api';
// const threadInclude = 'user,user.groups,firstPost,firstPost.images,category,'
//   + 'threadVideo,question,question.beUser,question.beUser.groups,firstPost.postGoods,threadAudio';
export default {
  name: 'Index',
  layout: 'custom_layout',
  mixins: [head, handleError, handleThreadList],
  // 异步数据用法
  async asyncData({ store, params, query }, callback) {
    if (!env.isSpider) {
      callback(null, {});
    }
    // const threadsStickyParams = {
    //   'filter[isSticky]': 'yes',
    //   'filter[isApproved]': 1,
    //   'filter[isDeleted]': 'no',
    //   // 'filter[categoryId]': params.id,
    //   'filter[categoryId]': query.search_ids,
    //   'page[number]': 1,
    //   include: 'firstPost'
    // };
    const threadsStickyParams = {};
    let searchidNum = [];
    if (query.search_ids || query.search_ids === '1') {
      if (typeof query.search_ids === 'string') {
        searchidNum = [query.search_ids];
      } else {
        searchidNum = query.search_ids;
      }
    } else {
      searchidNum = [];
    }
    if (searchidNum.length > 0) {
      threadsStickyParams.categoryids = searchidNum;
    }
    const threadsParams = {
      page: 1,
      filter: {
        sticky: 0,
        essence: 0
      }
    };
    /*IFTRUE_default*/
    const userParams = {
      include: 'groups',
      limit: 4
    };
    /*FITRUE_default*/
    try {
      const resData = {};
      // const threadsStickyData = await store.dispatch('jv/get', [
      //   'threads',
      //   { params: threadsStickyParams }
      // ]);
      const threadsStickyData = await apiRequest.readStickList({ params: threadsStickyParams });
      const threadsData = await apiRequest.readThreadList({ params: threadsParams });
      const categoryData = await apiRequest.readCategories();
      /*IFTRUE_default*/
      const recommendUser = await store.dispatch('jv/get', [
        'users/recommended',
        { params: userParams }
      ]);
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      const recommendContent = await store.dispatch('jv/get', [
        'threads/recommend', {}
      ]);
      /*FITRUE_pay*/
      // 1 置顶主题
      if (Array.isArray(threadsStickyData)) {
        resData.threadsStickyData = threadsStickyData.Data;
      } else if (
        threadsStickyData
        && threadsStickyData._jv
        && threadsStickyData._jv.json
      ) {
        const _threadsStickyData = threadsStickyData._jv.json.data || [];
        _threadsStickyData.forEach((item, index) => {
          _threadsStickyData[index] = {
            ...item,
            ...item.attributes,
            firstPost: item.relationships.firstPost.data,
            jv: { id: item.id }
          };
        });
        resData.threadsStickyData = _threadsStickyData;
      }
      // 2 非置顶主题
      if (Array.isArray(threadsData)) {
        resData.threadsData = threadsData.Data;
        threadsData.Data.forEach(item => {
          item.id = item.thread && item.thread.pid;
          if (item.attachment.length > 0) {
            item.attachment = item.attachment.filter(attachmentItem => attachmentItem.thumbUrl);
          }
        });
      } else if (threadsData && threadsData._jv && threadsData._jv.json) {
        const _threadsData = threadsData._jv.json.data || [];
        _threadsData.forEach((item, index) => {
          _threadsData[index] = {
            ...item,
            ...item.attributes,
            firstPost: item.relationships.firstPost.data,
            user: item.relationships.user.data,
            _jv: { id: item.id }
          };
        });
        resData.threadsData = _threadsData;
      }
      // 3 分类数据（扩展二级分类）
      if (Array.isArray(categoryData)) {
        resData.categoryData = categoryData.Data;
        // 用于head title 显示
        // const currentCategory = categoryData.find(item => {
        //   return item._jv && item._jv.id === params.id;
        // });
        const _title = [];
        categoryData.Data.forEach(item => {
          _title.push({
            id: item && +item.pid,
            name: item.name
          });
          item.children && item.children.forEach(c => {
            _title.push({
              id: +c.pid,
              name: `${item.name} - ${c.name}`
            });
          });
        });
        const currentCategory = _title.find(item => {
          return item.id === +params.id;
        });
        if (currentCategory) {
          resData.title = currentCategory.name || '分类';
        }
      } else if (categoryData && categoryData._jv && categoryData._jv.json) {
        const _categoryData = categoryData._jv.json.data || [];
        // _categoryData.forEach((item, index) => {
        //   _categoryData[index] = {
        //     ...item,
        //     ...item.attributes,
        //     _jv: { id: item.id }
        //   };
        //   if (item.id === params.id) {
        //     resData.title = item.attributes.name || '分类';
        //   }
        // });
        const _title = [];
        _categoryData.forEach(item => {
          _title.push({
            id: item._jv && +item._jv.id,
            name: item.name
          });
          item.children && item.children.forEach(c => {
            _title.push({
              id: +c.id,
              name: `${item.name} - ${c.name}`
            });
          });
        });
        const currentCategory = _title.find(item => {
          return item.id === +params.id;
        });
        if (currentCategory) {
          resData.title = currentCategory.name || '分类';
        }
        resData.categoryData = _categoryData;
      }
      // 4 推荐用户 或 内容
      /*IFTRUE_default*/
      if (Array.isArray(recommendUser)) {
        resData.recommendUserData = recommendUser;
      } else if (recommendUser && recommendUser._jv && recommendUser._jv.json) {
        const _recommendUser = recommendUser._jv.json.data || [];
        _recommendUser.forEach((item, index) => {
          _recommendUser[index] = { ...item, ...item.attributes };
        });
        resData.recommendUserData = _recommendUser;
      }
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      if (Array.isArray(recommendContent)) {
        resData.recommendContentData = recommendContent;
      } else if (
        recommendContent && recommendContent._jv && recommendContent._jv.json) {
        const recommendContent = recommendContent._jv.json.data || [];
        recommendContent.forEach((item, index) => {
          recommendContent[index] = {
            ...item,
            ...item.attributes
          };
        });
        resData.recommendContentData = recommendContent;
      }
      /*FITRUE_pay*/
      callback(null, resData);
    } catch (error) {
      callback(null, {
        _error__abc: {
          error_keys: Object.keys(error),
          error: String(error),
          errno: error.errno,
          code: error.code,
          syscall: error.syscall,
          address: error.address,
          port: error.port,
          config: error.config,
          request_domain: (error.request || {}).domain,
          request_keys: Object.keys(error.request || {}),
          response_keys: Object.keys(error.response || {})
        }
      });
    }
  },
  data() {
    return {
      isSpider: env.isSpider,
      loading: false,
      threadsStickyData: [], // 置顶主题列表
      threadsData: [], // 主题列表
      categoryData: [], // 分类列表
      /*IFTRUE_default*/
      recommendUserData: [], // 推荐用户列表
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      recommendContentData: [], // 推荐内容列表
      /*FITRUE_pay*/
      pageNum: 1, // 当前页码
      pageSize: 10, // 每页多少条数据
      categoryId: 0, // 分类id 0全部
      threadType: '', // 主题类型 0普通 1长文 2视频 3图片（'' 不筛选）
      sort: '', // 排序
      threadEssence: '', // 是否精华帖
      fromUserId: 0, // 关注人id
      hasMore: false,
      timer: null, // 轮询获取新主题 定时器
      threadCount: 0, // 主题总数
      total: 0, // 新的主题数，通过轮询获取
      currentAudioId: '',
      showVideoPop: false, // 显示视频弹窗
      threadVideo: {}, // 当前显示的视频信息
      threadIdSet: new Set(), // 新建一个集合，存放当前主题id，用来去除重复项
      newData: ''
    };
  },
  computed: {
    userId() {
      return this.$store.getters['session/get']('userId');
    },
    forums() {
      return this.$store.state.site.info.attributes || {};
    }
  },
  mounted() {
    if (this.$route.params.id) {
      this.categoryId = this.$route.params.id;
      this.getCategoryList();
    }
    if (this.threadsStickyData.length === 0) {
      this.getThreadsSticky();
    }
    if (this.threadsData.length === 0) {
      this.getThreadsList();
    } else {
      if (this.newData.currentPage < this.newData.totalPage) {
        this.hasMore = true;
      }
      if (this.timer) {
        clearInterval(this.timer);
      }
      this.timer = setInterval(() => {
        this.autoLoadThreads();
      }, 30000);
    }
    // 数据预加载相关，不参与界面渲染，无需放入data
    this.preThreadsData = null;
    this.preThreadsLoading = false;
  },
  destroyed() {
    clearInterval(this.timer);
  },
  methods: {
    preLoadThreads() {
      if (this.preThreadsData || this.preThreadsLoading) {
        return;
      }
      this.preThreadsLoading = true;
      const params = this.getThreadsParams();
      return apiRequest
        .readThreadList({ params })
        .then(result => {
          this.preThreadsData = {
            [params.page]: result
          };
        })
        .catch(() => {
          this.preThreadsData = null;
        })
        .finally(() => {
          this.preThreadsLoading = false;
        });
    },
    getThreadsParams() {
      let params = {};
      params = {
        page: this.pageNum,
        perPage: this.pageSize,
        filter: {
          sticky: 0,
          essence: this.threadEssence === 'yes' ? 1 : 0,
          attention: this.fromUserId || 0
        }
      };
      let searchidNum = [];
      if (this.$route.query.search_ids || this.$route.query.search_ids === '1') {
        if (typeof this.$route.query.search_ids === 'string') {
          searchidNum = [this.$route.query.search_ids];
        } else {
          searchidNum = this.$route.query.search_ids;
        }
      } else {
        searchidNum = [];
      }
      if (searchidNum.length > 0) {
        params.filter.categoryids = searchidNum;
      }
      if (this.threadType || this.threadType === 0) {
        params.filter.types = [this.threadType];
      }
      if (this.sort || this.sort === 0) {
        params.filter.sort = this.sort;
      }
      return params;
    },
    // 置顶主题
    getThreadsSticky() {
      this.threadsStickyData = [];
      const pages = {};
      let searchidNum = [];
      if (this.$route.query.search_ids || this.$route.query.search_ids === '1') {
        if (typeof this.$route.query.search_ids === 'string') {
          searchidNum = [this.$route.query.search_ids];
        } else {
          searchidNum = this.$route.query.search_ids;
        }
      } else {
        searchidNum = [];
      }
      if (searchidNum.length > 0) {
        pages.categoryids = searchidNum;
      }
      apiRequest.readStickList({ params: pages }).then(data => {
        this.threadsStickyData = [...data.Data];
      });
    },
    // 非置顶主题
    async getThreadsList() {
      let result;
      try {
        if (this.preThreadsData && this.preThreadsData[this.pageNum]) {
          result = this.preThreadsData[this.pageNum];
          this.preThreadsData = null;
        } else {
          // 这是清空，是处理快速滚动特殊情况下，
          // preThreadsData有数据，但是页数不匹配的情况
          this.preThreadsData = null;
          const params = this.getThreadsParams();
          result = await apiRequest.readThreadList({ params });
        }
        const newRes = result.Data.pageData.map(item => {
          const newItem = item;
          newItem.id = item.thread.pid;
          if (newItem.attachment.length > 0) {
            newItem.attachment = newItem.attachment.filter(attachmentItem => attachmentItem.thumbUrl);
          }
          return newItem;
        });
        this.handleThreadList(result.Data, newRes, 'threadsData');
        if (this.timer) {
          clearInterval(this.timer);
        }
        this.timer = setInterval(() => {
          this.autoLoadThreads();
        }, 30000);
      } catch (e) {
        this.handleError(e);
      }
      // this.loading = true;
      // const params = this.getThreadsParams();
      // apiRequest.readThreadList({ params })
      //   .then(
      //     data => {
      //       const newRes = data.Data.pageData.map(item => {
      //         const newItem = item;
      //         newItem.id = item.thread.pid;
      //         if (newItem.attachment.length > 0) {
      //           for (let i = 0; i < newItem.attachment.length; i++) {
      //             if (!newItem.attachment[i].thumbUrl) {
      //               newItem.attachment.splice(i, 1);
      //             }
      //           }
      //         }
      //         return newItem;
      //       });
      //       this.handleThreadList(data.Data, newRes, 'threadsData');
      //       if (this.timer) {
      //         clearInterval(this.timer);
      //       }
      //       this.timer = setInterval(() => {
      //         this.autoLoadThreads();
      //       }, 30000);
      //     },
      //     e => {
      //       this.handleError(e);
      //     }
      //   )
      //   .finally(() => {
      //     this.loading = false;
      //   });
    },
    // 获取分类列表 用于head title显示
    getCategoryList() {
      apiRequest.readCategories().then(
        res => {
          const resData = [...res.Data] || [];
          this.categoryData = resData;
          // const currentCategory = resData.find(item => {
          //   return item._jv && +item._jv.id === +this.categoryId;
          // });
          const _title = [];
          resData.forEach(item => {
            _title.push({
              id: item && +item.pid,
              name: item.name
            });
            item.children && item.children.forEach(c => {
              _title.push({
                id: +c.pid,
                name: `${item.name} - ${c.name}`
              });
            });
          });
          const currentCategory = _title.find(item => {
            return item.id === +this.categoryId;
          });
          if (currentCategory) {
            this.title = currentCategory.name;
          }
        },
        e => {
          this.handleError(e);
        }
      );
    },
    // 点击加载更多
    loadMore() {
      // 在这里加1的话，遇到加载不成功的会不断加页码
      //  this.pageNum++
      this.getThreadsList();
    },
    // 轮询查看是否有新主题
    autoLoadThreads() {
      // const params = {
      //   'filter[isSticky]': 'no',
      //   'filter[isApproved]': 1,
      //   'filter[isDeleted]': 'no',
      //   'filter[isDisplay]': 'yes',
      //   // 'filter[categoryId]': this.categoryId,
      //   'filter[categoryId]': this.$route.query.search_ids,
      //   'filter[type]': this.threadType,
      //   'filter[isEssence]': this.threadEssence,
      //   'filter[fromUserId]': this.fromUserId,
      //   'page[limit]': 1
      // };
      const params = this.getThreadsParams();
      params.page = 1;
      apiRequest.readThreadList({ params })
        .then(
          data => {
            if (data && data.Data) {
              const _threadCount = (data && data.Data && data.Data.totalCount) || 0;
              if (this.threadCount > 0) {
                this.total
                  = _threadCount - this.threadCount > 0
                    ? _threadCount - this.threadCount
                    : 0;
              }
            }
          });
    },
    scrollDdata() {
      this.preLoadThreads();
    },
    // 重新加载列表
    reloadThreadsList() {
      this.pageNum = 1;
      this.total = 0;
      this.threadsData = [];
      this.preThreadsData = null;
      this.getThreadsList();
    },
    // 点击分类
    // onChangeCategory(id) {
    //   if (id !== 0) {
    //     this.$router.push(`/category/${id}`);
    //   } else {
    //     this.$router.push('/');
    //   }
    // },
    onChangeCategory(ids) {
      if (ids.id !== 0) {
        this.$router.push({
          path: `/category/${ids.id}`,
          query: { search_ids: ids.search_ids }
        });
      } else {
        this.$router.push('/');
      }
    },
    // 筛选
    onChangeFilter(val) {
      this.preThreadsData = null;
      this.threadEssence = '';
      this.fromUserId = 0;
      if (val === 'isEssence') {
        this.threadEssence = 'yes';
      } else if (val === 'followed') {
        this.fromUserId = 1;
      }
      this.reloadThreadsList();
    },
    // 类型
    onChangeType(val) {
      this.threadType = val;
      this.reloadThreadsList();
    },
    // 排序
    onChangeSort(val) {
      if (val === '-createdAt') {
        this.sort = 2;
      } else if (val === '-updatedAt') {
        this.sort = 3;
      } else {
        this.sort = 1;
      }
      this.reloadThreadsList();
    },
    // 处理富文本里的图片宽度自适应
    // 1.去掉img标签里的style、width、height属性
    // 2.img标签添加style属性：max-width:100%;height:auto
    // 3.修改所有style里的width属性为max-width:100%
    // 4.去掉<br/>标签			 * @param html			 * @returns {void|string|*}
    formatRichText(html) {
      // eslint-disable-next-line no-param-reassign
      return s9e.parse(html);
    },
    // 语音互斥播放
    audioPlay(id) {
      if (this.currentAudioId && this.currentAudioId !== id) {
        this.$refs[`audio${this.currentAudioId}`] && this.$refs[`audio${this.currentAudioId}`].pause();
      }
      this.currentAudioId = id;
    },
    // 视频播放弹窗
    showVideoFn(video) {
      this.threadVideo = video;
      this.showVideoPop = true;
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
/*IFTRUE_default*/
.app-cont {
  box-shadow: none;
}
.container {
  display: flex;
  background: #f4f5f6;
  width: 100%;
  .cont-left {
    flex: auto;
    @include background();
    .hide {
      position: relative;
      opacity: 0;
      &.show {
        opacity: 1;
      }
    }
    .list-top-item {
      border-bottom: 1px solid $line-color-base;
      padding: 10.5px 22px;
      display: flex;
      align-items: center;
      .top-label {
        color: $font-color-grey;
        margin-right: 20px;
      }
      .top-title {
        color: #000;
        flex: 1;
        max-width: 425px;
        overflow: hidden;
        // text-overflow:ellipsis;
        // white-space: nowrap;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
        font-size: 14px !important;
        max-height: 21px;
        ::v-deep p {
          font-size: 14px !important;
        }

        ::v-deep img {
          height: 19px;
        }
        @media screen and (max-width: 1005px) {
          ::v-deep img {
            height: 18px;
          }
        }
      }
      &:hover {
        .top-title {
          color: $color-blue-base;
        }
      }
    }
    .new-post {
      padding: 10px 20px;
      .new-post-cont {
        background: #fdf6ec;
        border-radius: 5px;
        text-align: center;
        line-height: 19px;
        color: #e6a23c;
        padding: 8px 0;
        .refresh {
          color: $color-blue-base;
          cursor: pointer;
        }
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
/*FITRUE_default*/
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
@import "@/assets/css/pay_scss/category_id.scss";
/*FITRUE_pay*/
</style>
