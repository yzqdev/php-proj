<template>
  <div class="container">
    /*IFTRUE_default*/
    <main class="cont-left">
      <index-filter
        :show-sort="siteOpenSortStatus"
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
          <div class="top-label">{{ $t("home.sticky") }}</div>
          <p
            :to="`/thread/${item && item.pid}`"
            target="_blank"
            class="top-title"
            @click="handleThreadsStickyClick(item)"
          >
            <span
              v-html="
                $xss(formatRichText(item.title))
              "
            />
          </p>
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
      <!-- ssr 不支持虚拟列表 -->
      <template v-if="isSpider">
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
      </template>
      <!-- 长列表优化 -->
      <dynamic-scroller v-else :items="threadsData" :min-item-size="20" page-mode>
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
      <div class="background-color">
        <recommend-user :list="recommendUserData" />
      </div>
      <copyright :forums="forums" />
    </aside>
    /*FITRUE_default*/
    /*IFTRUE_pay*/
    <main class="cont-center">
      <el-carousel v-if="posterData.length > 0" :interval="5000" arrow="always" class="kuq-yuanjiao-10 kuq-xia-10">
        <el-carousel-item v-for="(item, index) in posterData" :key="index">
          <nuxt-link :to="`/thread/${item && item.pid}`">
            <img :src="item.url" class="kuq-yuanjiao-10">
          </nuxt-link>
        </el-carousel-item>
      </el-carousel>
      <!--赞助-->
      <div v-show="forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].value == 1">
        <el-row v-if="userId" class="kuq-xia-10" >
          <el-col :span="24" v-if="forums && forums.other && forums.other.can_create_thread_ask === false">
            <el-card shadow="never">
              <div class="kuq-buhuanhang">
                <div
                  v-if="forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].hangshu == 1"
                  class="kuq-zuobian kuq-juzhong kuq-baifei"
                >
                  <span class="kuq-font-16">
                    {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].shuoming}}
                  </span>
                  <br>
                  <el-link type="danger" href="/my/vip" class="kuq-font-16 kuq-shang-10">
                    {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].zanzhu}}
                  </el-link>
                  <br>
                  <div class="kuq-juzhong kuq-shang-10">
                    {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].fuwu}}
                  </div>
                </div>
                <div
                  v-else
                  class="kuq-zuobian kuq-juzhong kuq-baifei"
                >
                  <span class="kuq-font-16">
                    {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].shuoming}}
                    <el-link :underline="false" type="danger" href="/my/vip" class="kuq-font-16">
                      {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].zanzhu}}
                    </el-link>
                  </span>
                  <br>
                  <div class="kuq-juzhong kuq-shang-10">
                    {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].fuwu}}
                  </div>
                </div>
                <div v-if="forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].hangshu == 1">
                  <el-link :underline="false" type="danger" href="/my/vip" class="kuq-font-16 kuq-youbian kuq-shang-20">
                    <el-button  type="danger" round>
                      我要提问 +
                    </el-button>
                  </el-link>
                </div>
                <div v-else>
                  <el-link :underline="false" type="danger" href="/my/vip" class="kuq-font-16 kuq-youbian kuq-shang-5">
                    <el-button  type="danger" round>
                      我要提问 +
                    </el-button>
                  </el-link>
                </div>

              </div>
              <div class="kuq-clear"></div>
            </el-card>
          </el-col>
          <el-col :span="24" v-else >
            <el-card shadow="never">
              <div class="kuq-zuobian" >
                <el-link type="danger" href="/thread/postpay?type=14" class="kuq-font-16 kuq-shang-10">
                  {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].tishi}}
                </el-link>
              </div>
              <el-link :underline="false" type="danger" href="/thread/postpay?type=14" class="kuq-font-16 kuq-youbian">
                <el-button  type="danger" round>
                  我要提问 +
                </el-button>
              </el-link>
              <div class="kuq-clear"></div>
            </el-card>
          </el-col>
        </el-row>
        <el-row v-else class="kuq-xia-10">
          <el-col :span="24">
            <el-card shadow="never ">
              <div class="kuq-buhuanhang">
                <div
                  v-if="forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].hangshu == 1"
                  class="kuq-zuobian kuq-juzhong kuq-baifei "
                >
                  <span class="kuq-font-16">
                    {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].shuoming}}
                  </span>
                  <br>
                  <el-link type="danger" href="/my/vip" class="kuq-font-16 kuq-shang-10">
                    {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].zanzhu}}
                  </el-link>
                  <br>

                  <div class="kuq-juzhong kuq-shang-10">
                    {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].fuwu}}
                  </div>
                </div>
                <div
                  v-else
                  class="kuq-zuobian kuq-juzhong kuq-baifei "
                >
                  <span class="kuq-font-16">
                    {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].shuoming}}
                    <el-link :underline="false" type="danger" href="/my/vip" class="kuq-font-16">
                      {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].zanzhu}}
                    </el-link>
                  </span>
                  <br>
                  <div class="kuq-juzhong kuq-shang-10">
                    {{forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].fuwu}}
                  </div>
                </div>
                <div v-if="forums && forums.set_site && forums.set_site.site_ask && forums.set_site.site_ask[0].hangshu == 1">
                  <el-link :underline="false" type="danger" href="/my/vip" class="kuq-font-16 kuq-youbian kuq-shang-20">
                    <el-button  type="danger" round>
                      我要提问 +
                    </el-button>
                  </el-link>
                </div>
                <div v-else>
                  <el-link :underline="false" type="danger" href="/my/vip" class="kuq-font-16 kuq-youbian kuq-shang-5">
                    <el-button  type="danger" round>
                      我要提问 +
                    </el-button>
                  </el-link>
                </div>
              </div>
              <div class="kuq-clear"></div>
            </el-card>
          </el-col>
        </el-row>
      </div>
      <!--赞助-->
      <div class="kuq-clear"></div>
      <div class="cont-center-head">
        <index-filter
          :show-sort="siteOpenSortStatus"
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
              <span
                v-html="
                  $xss(formatRichText(item.title))
                "
              />
            </nuxt-link>
          </div>
        </div>
        <div v-if="total > 0" class="new-post">
          <div class="new-post-cont">
            <div class="new-post-left">
              {{ $t('home.hasNewContent', { total }) }}
            </div>
            <div class="refresh" @click="reloadThreadsList">
              <svg-icon class="refresh-icon" type="refresh" />
              <span class="refresh-text">{{ $t('home.clickRefresh') }}</span>
            </div>
          </div>
        </div>
      </div>
      <!-- ssr 不支持虚拟列表 -->
      <template v-if="isSpider">
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
      </template>
      <!-- 长列表优化 -->
      <dynamic-scroller
        class="scroller"
        v-else
        :items="threadsData"
        :min-item-size="120"
        :item-height="10"
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
      <div class="background-color"
         v-show="forums && forums.set_site && forums.set_site.site_weixinapp == 1">
        <weixinapp />
      </div>
      <div class="background-color"
        v-show="forums && forums.set_site && forums.set_site.site_weixinkefu == 1">
        <weixinlianxi />
      </div>
      <div v-show="recommendContentData.legnth !== 0" class="background-color">
        <recommend-content :list="recommendContentData" />
      </div>
      <div class="background-color kuq-shang-10">
        <advertising />
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
import handleError from '@/mixin/handleError';
import handleThreadList from '@/mixin/dataProcessing';
import loginAbout from '@/mixin/loginAbout';
import env from '@/utils/env';
import apiRequest from '../api/new-api';
// import PostItems from '../components/PostItems.vue';
// import PostItemPays from '../components/PostItemPays.vue';
// import CategoryPays from '../components/index/CategoryPays.vue';
// const threadInclude = 'user,user.groups,firstPost,firstPost.images,firstPost.attachments,category,'
//   + 'threadVideo,question,question.beUser,question.beUser.groups,firstPost.postGoods,threadAudio';
export default {
  name: 'Index',
  layout: 'custom_layout',
  mixins: [handleError, handleThreadList, loginAbout],
  // 异步数据用法
  async asyncData({ store, query }, callback) {
    if (!env.isSpider) {
      callback(null, {});
    }
    // const threadsStickyParams = {
    //   'filter[isSticky]': 'yes',
    //   'filter[isApproved]': 1,
    //   'filter[isDeleted]': 'no',
    //   'page[number]': 1,
    //   include: 'firstPost'
    // };
    // const threadsParams = {
    //   include: threadInclude,
    //   'filter[isSticky]': 'no',
    //   'filter[isApproved]': 1,
    //   'filter[isDeleted]': 'no',
    //   'filter[isDisplay]': 'yes',
    //   'filter[isSort]': store.getters['site/getSortStatus'],
    //   'page[number]': 1,
    //   'page[limit]': 10
    // };
    const threadsParams = {
      page: 1,
      perPage: 10,
      filter: {
        sticky: 0,
        essence: 0
      }
    };
    if (store.getters['site/getSortStatus'] === 1) {
      threadsParams.homeSequence = 1;
    }
    const userParams = {
      include: 'groups',
      limit: 4
    };
    try {
      const resData = {};
      const siteInfo = await store.dispatch('site/getSiteInfo');
      const threadsStickyData = await apiRequest.readStickList();
      const threadsData = await apiRequest.readThreadList({ params: threadsParams });

      // const threadsData = await store.dispatch('jv/get', [
      //   'threads', { params: threadsParams }
      // ]);
      const categoryData = await apiRequest.readCategories();
      const recommendUser = await store.dispatch('jv/get', [
        'users/recommended',
        { params: userParams }
      ]);
      const recommendContent = await store.dispatch('jv/get', [
        'threads/recommend', {}
      ]);
      // 1 head信息
      if (siteInfo && siteInfo.attributes && siteInfo.attributes.set_site) {
        resData.headTitle
          = siteInfo.attributes.set_site.site_title
          || siteInfo.attributes.set_site.site_name
          || 'Discuz! Q';
        resData.headKeywords = siteInfo.attributes.set_site.site_keywords;
        resData.headDesc = siteInfo.attributes.set_site.site_introduction;
      }
      // 2 处理置顶数据
      if (Array.isArray(threadsStickyData.Data)) {
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
      // 3 处理主题数据
      if (Array.isArray(threadsData)) {
        threadsData.Data.pageData.forEach(item => {
          item.id = item.thread && item.thread.pid;
          if (item.attachment.length > 0) {
            item.attachment = item.attachment.filter(attachmentItem => attachmentItem.thumbUrl);
          }
        });
        resData.threadsData = threadsData.Data.pageData;
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
      // 4 处理分类数据
      if (Array.isArray(categoryData.Data)) {
        resData.categoryData = categoryData.Data;
      } else if (categoryData && categoryData._jv && categoryData._jv.json) {
        const _categoryData = categoryData._jv.json.data || [];
        _categoryData.forEach((item, index) => {
          _categoryData[index] = {
            ...item,
            ...item.attributes,
            _jv: { id: item.id }
          };
        });
        resData.categoryData = _categoryData;
      }
      // 5 处理推荐内容
      if (Array.isArray(recommendContent)) {
        resData.recommendContentData = recommendContent;
      } else if (
        recommendContent
        && recommendContent._jv
        && recommendContent._jv.json
      ) {
        const _recommendUser = recommendContent._jv.json.data || [];
        _recommendUser.forEach((item, index) => {
          _recommendUser[index] = { ...item, ...item.attributes };
        });
        resData.recommendContentData = _recommendUser;
      }
      // 6 处理推荐用户
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
      // siteClose: false,
      isSpider: env.isSpider,
      loading: false,
      threadsStickyData: [], // 置顶主题列表
      threadsData: [], // 主题列表
      posterData: [], // 海报列表
      categoryData: [], // 分类列表
      recommendUserData: [], // 推荐用户列表
      recommendContentData: [], // 推荐内容列表
      pageNum: 1, // 当前页码
      pageSize: 10, // 每页多少条数据
      threadType: '', // 主题类型 0普通 1长文 2视频 3图片（'' 不筛选）
      sort: '', // 排序
      threadEssence: '', // 是否精华帖
      siteOpenSortStatus: false, // 智能排序开关状态
      threadSort: 0, // 是否添加排序请求 0-不添加 1-添加
      fromUserId: 0, // 关注人id
      hasMore: false,
      timer: null, // 轮询获取新主题 定时器
      threadCount: 0, // 主题总数
      total: 0, // 新的主题数，通过轮询获取
      headTitle: '\u200E',
      headKeywords: '',
      headDesc: '',
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
  watch: {
    forums: {
      handler(val) {
        if (val && val.set_site) {
          this.headTitle
            = val.set_site.site_title || val.set_site.site_name || 'Discuz! Q';
          this.headKeywords = val.set_site.site_keywords;
          this.headDesc = val.set_site.site_introduction;
        }
      },
      deep: true
    }
  },
  mounted() {
    /* IFTRUE_test */
    console.log(333333);
    /* FITRUE_test */
    if (this.forums && this.forums.set_site) {
      this.headTitle
        = this.forums.set_site.site_title
        || this.forums.set_site.site_name
        || 'Discuz! Q';
      this.userDATA = this.forums.set_site.site_keywords;
      this.headKeywords = this.forums.set_site.site_keywords;
      this.headDesc = this.forums.set_site.site_introduction;
    }
    if (this.threadsStickyData.length === 0) {
      this.getThreadsSticky();
    }
    // if (this.threadsData.length === 0) {
    //   this.getThreadsList('first');
    // } else {
    //   if (this.threadsData.length === this.pageSize) {
    //     this.hasMore = true;
    //   }
    // }
    // 数据预加载相关，不参与界面渲染，无需放入data
    this.preThreadsData = null;
    this.preThreadsLoading = false;
    this.initThread();
    this.getPosterData();
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
          attention: this.fromUserId
        }
      };
      if (this.threadSort === 1) {
        params.homeSequence = 1;
      }
      if (this.threadType || this.threadType === 0) {
        params.filter.types = [this.threadType];
      }
      if (this.sort || this.sort === 0) {
        params.filter.sort = this.sort;
      }
      return params;
    },
    // 海报主题
    getPosterData() {
      // this.posterData = [];
      const params = {};
      this.$store.dispatch('jv/get', [`poster.v2`, {}]).then((res) => {
        this.posterData = res._jv.json.Data;
      });
      /*apiRequest.readStickList().then((res) => {
        this.posterData = [...res.Data];
      });*/
    },
    // 置顶主题
    getThreadsSticky() {
      this.threadsStickyData = [];
      apiRequest.readStickList().then((res) => {
        this.threadsStickyData = [...res.Data];
      });
      // const params = {
      //   'filter[isSticky]': 'yes',
      //   'filter[isApproved]': 1,
      //   'filter[isDeleted]': 'no',
      //   include: ['firstPost']
      // };
      // this.$store.dispatch('jv/get', ['threads', { params }]).then(data => {
      //   this.threadsStickyData = [...data];
      //   console.log(this.threadsStickyData, '置顶老数据');
      // });
    },
    initThread() {
      this.$store.dispatch('site/getSiteInfo').then(res => {
        // 获取智能排序开启状态
        this.threadSort = res.attributes.set_site.site_open_sort;
        this.siteOpenSortStatus = this.threadSort === 1;

        if (this.threadsData.length === 0) {
          this.getThreadsList('first');
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
      });
    },
    // 这里的逻辑拷贝自 loginAbout
    handleThreadsStickyClick(item) {

      // 先判断是否游客可以访问
      if (!item.canViewPosts) {

        // 登录态可以访问
        if (!this.$store.getters['session/get']('isLogin')) {
          if (process.client) {
            this.$message.warning(this.$t('core.not_authenticated'));
            window.setTimeout(() => {
              this.headerTologin();
            }, 1000);
          }
        } else {
          this.$message.warning(this.$t('home.noPostingTopic'));
        }
        return false;

      } else {

        // 游客可以访问
        // 需要跳转到的路由，如果有权限
        const jumpUrl = `/thread/${item && item.pid}`;
        window.open(jumpUrl, '_black');
      }
    },
    // 非置顶主题
    async getThreadsList(type) {
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
        this.$nextTick(() => {
          if (type && type === 'first') {
            if (this.timer) {
              clearInterval(this.timer);
            }
            this.timer = setInterval(() => {
              this.autoLoadThreads();
            }, 50000);
          }
        });
      } catch (e) {
        this.handleError(e);
      }
    },
    scrollDdata() {
      this.preLoadThreads();
    },
    // onLoading({ done, pushItems }) {
    //   pushItems(this.threadsData);
    //   const { pageNum } = this;
    //   console.log('this.hasMore', this.hasMore);
    //   done(pageNum >= 3);
    // },
    // 点击加载更多
    loadMore() {
      // 在这里加1的话，遇到加载不成功的会不断加页码
      this.getThreadsList();
    },
    // 轮询查看是否有新主题
    autoLoadThreads() {
      const params = this.getThreadsParams();
      params.page = 1;
      // const params = {
      //   'filter[isSticky]': 'no',
      //   'filter[isApproved]': 1,
      //   'filter[isDeleted]': 'no',
      //   'filter[isDisplay]': 'yes',
      //   'filter[type]': this.threadType,
      //   'filter[isSort]': this.threadSort,
      //   'filter[isEssence]': this.threadEssence,
      //   'filter[fromUserId]': this.fromUserId,
      //   'page[limit]': 1
      // };
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
    // 重新加载列表
    reloadThreadsList() {
      this.pageNum = 1;
      this.preThreadsData = null;
      this.total = 0;
      this.threadsData = [];
      this.getThreadsList();
    },
    // 点击分类
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
      this.threadSort = 0;
      this.threadEssence = '';
      this.fromUserId = 0;
      if (val === 'isSort') this.threadSort = 1;
      if (val === 'isEssence') this.threadEssence = 'yes';
      if (val === 'followed') {
        this.fromUserId = 1;
      } else {
        this.fromUserId = 0;
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
  },
  head() {
    return {
      title: this.headTitle,
      meta: [
        { hid: 'keywords', name: 'keywords', content: this.headKeywords },
        { hid: 'description', name: 'description', content: this.headDesc }
      ]
    };
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
@import "@/assets/css/pay_scss/kuq.scss";
@mixin background() {
  background: #fff;
  border-radius: 5px;
  box-shadow: 0 3px 3px rgba(0, 0, 0, 0.03);
}
/*IFTRUE_default*/
.app-cont {
  box-shadow: none;
}
.container{
  display:flex;
  background: #F4F5F6;
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
        cursor: pointer;
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
      padding: 10px 20px 0;
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
  .el-carousel {
    position: relative;
    border-radius: 10px;
  }
}

/*FITRUE_default*/
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
@import "@/assets/css/pay_scss/pages_index.scss";
@import "@/assets/css/pay_scss/kuq.scss";
/*FITRUE_pay*/
.scroller {

}
</style>
