// import enLocale from './plugins/lang/en.js'
import zhLocale from './plugins/lang/zh.js';
import config from './config.js';
const path = require('path');

function resolve(dir) {
  return path.join(__dirname, dir);
}

const DEV_API_URL = config.DEV_API_URL;

const isProduction = process.env.NODE_ENV === 'production';

const proxyConfig = {
  '/api': {
    target: DEV_API_URL, // 目标服务器
    changeOrigin: true
  }
};
const plugins = [
  ['component',
    {
      libraryName: 'element-ui',
      styleLibraryName: 'theme-chalk'
    }
  ]
];
//  生产环境清除log
if (isProduction) {
  plugins.push('transform-remove-console');
}

// 条件编译
const conditionalCompiler = {
  loader: 'js-conditional-compile-loader',
  options: {
    default: process.env.SCENE === 'default',
    pay: process.env.SCENE === 'pay'
  }
};

export default {
  env: {
    domain: config.SSR_API_URL || DEV_API_URL,
    baseURL: '/'
  },
  /*
  ** Nuxt rendering mode
  ** See https://nuxtjs.org/api/configuration-mode
  */
  mode: 'universal',
  /*
  ** Nuxt target
  ** See https://nuxtjs.org/api/configuration-target
  */
  target: 'server',
  /*
  ** Headers of the page
  ** See https://nuxtjs.org/api/configuration-head
  */
  head: {
    // title: process.env.npm_package_name || '',
    // title: 'Discuz! Q',
    meta: [
      { charset: 'utf-8' },
      /*IFTRUE_default*/
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      /*FITRUE_default*/
      /*IFTRUE_pay*/
      { name: 'viewport', content: 'width=device-width, initial-scale=1, IE=edge, chrome=1' },
      /*FITRUE_pay*/
      { hid: 'description', name: 'description', content: process.env.npm_package_description || '' }
    ]
    // link: [
    //   { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
    // ]
  },
  general: {
    fallback: true
  },
  router: {
    middleware: 'header'
  },
  /*
  ** Global CSS
  */
  css: [
    'element-ui/lib/theme-chalk/index.css',
    '@/assets/css/reset.scss',
    'vditor/src/assets/scss/index.scss',
    'viewerjs/dist/viewer.css',
    'video.js/src/css/video-js.scss'
  ],
  /*
  ** Plugins to load before mounting the App
  ** https://nuxtjs.org/guide/plugins
  */
  plugins: [
    '@/plugins/element-ui',
    '@/plugins/svg-icon',
    '@/directive/permission',
    '@/plugins/route',
    '@/plugins/xss',
    { src: '@/plugins/cropper', ssr: false },
    { src: '@/plugins/viewer', ssr: false },
    { src: '@/plugins/dzqjs', ssr: false },
    { src: '@/plugins/statisticsCode', ssr: false },
    { src: '@/plugins/virtualScroller', ssr: false }
  ],
  /*
  ** Auto import components
  ** See https://nuxtjs.org/api/configuration-components
  */
  components: true,
  /*
  ** Nuxt.js dev-modules
  */
  buildModules: [],
  /*
  ** Nuxt.js modules
  */
  modules: [
    '@nuxtjs/axios',
    'nuxt-i18n',
    '@nuxtjs/proxy'
  ],
  i18n: {
    // locales: ['en', 'zh'],
    defaultLocale: 'zh',
    vueI18n: {
      fallbackLocale: 'zh',
      messages: {
        // en: {
        //   ...enLocale
        // },
        zh: {
          ...zhLocale
        }
      }
    }
  },

  axios: {
    // proxyHeaders: false
    proxy: !isProduction
  },
  proxy: isProduction ? {} : proxyConfig, // 生产环境会一直运行
  /*
  ** Build configuration
  ** See https://nuxtjs.org/api/configuration-build/
  */
  build: {
    // analyze: true,
    // 生产环境抽离css
    extractCSS: isProduction,
    optimization: {
      splitChunks: {
        chunks: 'all',
        minSize: 100 * 100,
        maxSize: 10000 * 100,
        cacheGroups: {
          // styles: {
          //   name: 'styles',
          //   test: /\.(css|vue|scss)$/,
          //   chunks: 'all',
          //   enforce: true
          // },
          // vendors: {
          //   chunks: 'initial',
          //   // 提升权重，先抽离第三方模块，再抽离公共模块，要不然执行抽离公共模块就截止不会往下执行
          //   priority: 100,
          //   test: /[\\/]node_modules[\\/]/
          // }
        }
      }
    },
    // transpile: [/^element-ui/],
    // element 按需加载
    babel: {
      plugins: plugins,
      presets({ isServer }) {
        const targets = isServer ? { node: '10' } : { ie: '11' };
        return [[require.resolve('@nuxt/babel-preset-app'), { targets }]];
      }
    },
    // svg处理
    extend(config, context) {
      // Run ESLint on save
      if (context.isDev && context.isClient) {
        config.module.rules.push({
          enforce: 'pre',
          test: /\.(js|vue)$/,
          loader: 'eslint-loader',
          exclude: /(node_modules)/
        });
      }

      config.module.rules.push({
        enforce: 'pre',
        test: /\.(js|vue|css|scss)$/,
        loader: conditionalCompiler,
        exclude: /(node_modules)/
      });

      // 排除 nuxt 原配置的影响,Nuxt 默认有vue-loader,会处理svg,img等
      // 找到匹配.svg的规则,然后将存放svg文件的目录排除
      const svgRule = config.module.rules.find(rule => rule.test.test('.svg'));
      svgRule.exclude = [resolve('assets/svg-icons')];

      // 添加loader规则
      config.module.rules.push({
        test: /\.svg$/, // 匹配.svg
        include: [resolve('assets/svg-icons')], // 将存放svg的目录加入到loader处理目录
        use: [{ loader: 'svg-sprite-loader', options: { esModule: false }}]
      });
    }
  }
};
