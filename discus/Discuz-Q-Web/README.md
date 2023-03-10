# Discuz-Q-Web

## Build Setup

```bash
# install dependencies
$ yarn install

# serve with hot reload at localhost:3000
$ yarn dev

# build for production and launch server
$ yarn build
$ yarn start

# generate static project
$ yarn generate
```

For detailed explanation on how things work, check out [Nuxt.js docs](https://nuxtjs.org).


## 部署流程

### 打包前配置

修改根目录下面的```config.js```配置文件，其中```DEV_API_URL```是本地二次开发的时候需要配置的api请求域名，```SSR_API_URL```是在生产环境开启ssr模式的时候需要配置的api请求域名

只有生产环境使用了ssr，才需要配置```SSR_API_URL```,静态化部署可以忽略

### 安装依赖

```
npm install
```

## 包含ssr的部署
### 本地运行

```
npm run dev:pay
```
### 运行打包命令
```
npm run build:pay-spa
```
### 开启服务

用```pm2```之类的进程管理工具开启一个```node```服务

```
 pm2 start npm --name "DiscuzQ" -- run start
```

## 不包含ssr的spa模式部署

```
npm run build:pay-spa
```

生成```dist```目录，访问```index.html```即可

## 多场景开发规范

1. [开发规范](./docs/MANY-SCENARIOS.md)


## 项目版本管理
1. [Git 分支开发工作流](./docs/GIT_BRANCH_FLOW.md)

## 项目目录结构说明
```
|-- api                       api 存放目录以及对于请求数据的处理存放目录
| |-- request.js              api: 请求之前或者之后处理函数
|-- assets                    资源存放目录
| |-- css                     公共样式存放目录
| | |-- variable              公共样式变量目录
| | | |-- color.scss          styles: 颜色
| | | |-- mixin.scss          styles: mixin函数
| | |-- reset.scss            styles: 重置页面样式
| |-- svg-icons               svg存放目录
|-- components                页面公用的组件存放的目录
|-- directive                 指令存放的目录
| |-- permission.js           按钮鉴权指令
|-- dist                      构建之后生成的目录
|-- layouts                   网站布局文件存放目录
| |-- center_layout.vue       个人中心布局文件
| |-- custom_layout.vue       首页等双列布局文件
| |-- default.vue             默认布局文件
| |-- error_layout.vue        错误页等没有头部底部布局文件
|-- library                   存放一些第三方依赖库等
|-- middleware                存放应用的中间件
|-- mixin                     存放mixin文件
|-- pages                   
| |-- pages                   项目的开发目录
| |-- _.vue                   404页面
| |--index.vue                首页
|-- plugins                   插件存放目录
| |-- lang                    国际化设置目录（语言设置）
|-- static                    用于存放应用的静态文件，此类文件不会被 Nuxt.js 调用 Webpack 进行构建编译处理。服务器启动的时候，该目录下的文件会映射至应用的根路径 / 下
|-- utils                     常用函数存放目录
|-- store                     数据状态管理目录
| |-- modules
| |-- types
|-- .editorconfig             编辑器配置推荐
|-- .eslintrc.js              eslint 配置文件
|-- .gitignore                git 提交忽略文件
|-- config.js                 项目配置文件
|-- nuxt.config.js            用于组织 Nuxt.js 应用的个性化配置，以便覆盖默认配置
|-- package.json              用于描述应用的依赖关系和对外暴露的脚本接口
|-- README.md                 项目说明文件
```

## 依赖
1. [nuxt.js](https://nuxtjs.org/)：基于vue的开发框架
2. [vue](https://cn.vuejs.org/index.html)：JavaScript 渐进式框架
3. [vuex](https://vuex.vuejs.org/zh/)：状态管理
4. [vue-i18n](https://kazupon.github.io/vue-i18n/zh/)：国际化
5. [jsonapi-vuex](https://github.com/mrichar1/jsonapi-vuex)：允许通过 vuex store 访问 [JSON:API](https://jsonapi.org/) web 服务中的数据。Discuz Q 接口使用的是 JSON:API 规范，所以使用这个库更加方便处理该请求和数据。
6. [nuxtjs-axios](http://www.axios-js.com/zh-cn/docs/nuxtjs-axios.html)：基于 promise 的 HTTP 库
7. [element-ui](https://element.eleme.cn/)：UI框架
8. [vue-cropper](https://www.npmjs.com/package/cropperjs)：图片裁剪插件
9. [v-viewer](https://github.com/mirari/v-viewer)：图片点击放大预览插件
10. [xss](https://www.npmjs.com/package/xss)：防xss注入插件
11. [dayjs](https://www.npmjs.com/package/dayjs)：处理时间和日期的 JavaScript 库
12. [babel-plugin-transform-remove-console](https://www.npmjs.com/package/babel-plugin-transform-remove-console)：用来移除项目中所有的 console 代码的插件
