/**
 * 模板配置
 */
import webDb from "../../helpers/webDbHelper";
import appFetch from "../../helpers/axiosHelper";

export default {
  /**
   * [路由器模板配置]
   * @type {Object}
   *
   * site为模块名，index为页面名称，拼接后路径为site/index
   */
  template: {
    //后台模块
    admin_site: {
      js: [],
      css: ["../../../static/css/reset.css"],
      admin: {
        comLoad: function (resolve) {
          require(["../view/site/IndexView"], resolve);
        },
        children: {
          //全局
          home: {
            comLoad: function (resolve) {
              require(["../view/site/home/homeView"], resolve);
            },
            metaInfo: {
              title: "管理中心首页",
              name: "controlCenter",
              attribution: "首页"
            }
          },
          "data-panel": {
            comLoad: function (resolve) {
              require(["../view/site/home/dataPanelView"], resolve);
            },
            metaInfo: {
              title: "数据看板",
              name: "dataPanel",
              attribution: "首页"
            }
          },
          "site-set": {
            comLoad: function (resolve) {
              require(["../view/site/global/siteSetView"], resolve);
            },
            metaInfo: {
              title: "站点设置",
              name: "siteSet",
              attribution: "全局",
              alias: "站点信息"
            }
          },
          "site-theme": {
            comLoad: function (resolve) {
              require(["../view/site/global/siteSet/siteThemeView"], resolve);
            },
            metaInfo: {
              title: "站点设置",
              name: "siteSet",
              attribution: "全局",
              alias: "主题设置"
            }
          },
          "site-function-set": {
            comLoad: function (resolve) {
              require(["../view/site/global/siteSet/siteFunctionSetView"], resolve);
            },
            metaInfo: {
              title: "站点设置",
              name: "siteSet",
              attribution: "全局",
              alias: "功能设置"
            }
          },
          "site-sort-set": {
            comLoad: function (resolve) {
              require(["../view/site/global/siteSet/siteSortSetView"], resolve);
            },
            metaInfo: {
              title: "站点设置",
              name: "siteSet",
              attribution: "全局",
              alias: "首页数据设置"
            }
          },
          "site-integral-set": {
            comLoad: function (resolve) {
              require(["../view/site/global/siteSet/siteIntegralSetView"], resolve);
            },
            metaInfo: {
              title: "站点设置",
              name: "siteSet",
              attribution: "全局",
              alias: "积分设置"
            }
          },
          "site-growup-set": {
            comLoad: function (resolve) {
              require(["../view/site/global/siteSet/siteGrowupSetView"], resolve);
            },
            metaInfo: {
              title: "站点设置",
              name: "siteSet",
              attribution: "全局",
              alias: "成长值设置"
            }
          },
          "site-signin-set": {
            comLoad: function (resolve) {
              require(["../view/site/global/siteSet/siteSigninSetView"], resolve);
            },
            metaInfo: {
              title: "站点设置",
              name: "siteSet",
              attribution: "全局",
              alias: "签到设置"
            }
          },
          "site-tubiao": {
            comLoad: function (resolve) {
              require(["../view/site/global/siteTubiaoView"], resolve);
            },
            metaInfo: {
              title: "站点设置",
              name: "siteSet",
              attribution: "全局",
              alias: "H5图标设置"
            }
          },
          "site-weitubiao": {
            comLoad: function (resolve) {
              require(["../view/site/global/siteWeiTubiaoView"], resolve);
            },
            metaInfo: {
              title: "站点设置",
              name: "siteSet",
              attribution: "全局",
              alias: "小程序图标设置"
            }
          },
          // "operation-log": {
          //   comLoad: function (resolve) {
          //     require([
          //       "../view/site/global/operationLogView"
          //     ], resolve);
          //   },
          //   metaInfo: {
          //     title: "操作日志",
          //     name: "operationLog",
          //     attribution: "全局",
          //   }
          // },
          "sign-up-set": {
            comLoad: function (resolve) {
              require(["../view/site/global/signUpSetView"], resolve);
            },
            metaInfo: {
              title: "注册与登录设置",
              name: "signUpSet",
              attribution: "全局"
            }
          },
          "registration-btn" : {
            comLoad: function (resolve) {
              require([
                "../view/site/global/registrationLoginPage/expandPageView"
              ], resolve);
            },
            metaInfo: {
              title: "注册与登录设置",
              name: "signUpSet",
              attribution: "全局"
            }
          },
          "worth-mentioning-set": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/worthMentioningSet/worthMentioningSetView"
              ], resolve);
            },
            metaInfo: {
              title: "第三方登录设置",
              name: "worthMentioningSet",
              attribution: "全局"
            }
          },
          "worth-mentioning-config/h5wx": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/worthMentioningSet/worthMentioningConfigH5WxView"
              ], resolve);
            },
            metaInfo: {
              title: "第三方登录设置",
              name: "worthMentioningSet",
              attribution: "全局"
            }
          },
          "worth-mentioning-config/applets": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/worthMentioningSet/worthMentioningConfigAppletsView"
              ], resolve);
            },
            metaInfo: {
              title: "第三方登录设置",
              name: "worthMentioningSet",
              attribution: "全局"
            }
          },
          "worth-mentioning-config/pcwx": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/worthMentioningSet/worthMentioningConfigPcWxView"
              ], resolve);
            },
            metaInfo: {
              title: "第三方登录设置",
              name: "worthMentioningSet",
              attribution: "全局"
            }
          },
          "worth-mentioning-config/ucenter": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/worthMentioningSet/worthMentioningConfigUcenter"
              ], resolve);
            },
            metaInfo: {
              title: "第三方登录设置",
              name: "otherServiceSet",
              attribution: "全局",
            }
          },
          "pay-set": {
            comLoad: function (resolve) {
              require(["../view/site/global/paySet/paySetView"], resolve);
            },
            metaInfo: {
              title: "支付设置",
              name: "paySet",
              attribution: "全局"
            }
          },
          "pay-config/wx": {
            comLoad: function (resolve) {
              require(["../view/site/global/paySet/payConfigWxView"], resolve);
            },
            metaInfo: {
              title: "支付设置",
              name: "paySet",
              attribution: "全局"
            }
          },
          "wx-notice": {
            comLoad: function (resolve) {
              require(["../view/site/global/notice/WxNoticeSetView"], resolve);
            },
            metaInfo: {
              title: "通知设置",
              name: "noticeSet",
              attribution: "全局",
              alias: "微信通知"
            }
          },
          "other-service-set": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/otherService/otherServiceSet"
              ], resolve);
            },
            metaInfo: {
              title: "其他服务设置",
              name: "otherServiceSet",
              attribution: "全局",
            }
          },
          "other-service-set-key": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/otherService/otherServiceKeySet"
              ], resolve);
            },
            metaInfo: {
              title: "其他服务设置",
              name: "otherServiceSet",
              attribution: "全局",
            }
          },
          "system-notice": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/notice/systemNoticeSetView"
              ], resolve);
            },
            metaInfo: {
              title: "通知设置",
              name: "noticeSet",
              attribution: "全局",
              // alias: "系统通知"
            }
          },
          "notice-configure": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/notice/noticeConfigureView"
              ], resolve);
            },
            metaInfo: {
              title: "通知设置",
              name: "noticeSet",
              attribution: "全局"
            }
          },
          "tencent-cloud-set": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/tencentCloudConfig/tencentCloudSetView"
              ], resolve);
            },
            metaInfo: {
              title: "腾讯云设置",
              name: "tencentCloudSet",
              attribution: "全局"
            }
          },
          "tencent-cloud-config/cloud": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/tencentCloudConfig/tencentCloudConfigCloudView"
              ], resolve);
            },
            metaInfo: {
              title: "腾讯云设置",
              name: "tencentCloudSet",
              attribution: "全局"
            }
          },
          "tencent-cloud-config/sms": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/tencentCloudConfig/tencentCloudConfigSmsView"
              ], resolve);
            },
            metaInfo: {
              title: "腾讯云设置",
              name: "tencentCloudSet",
              attribution: "全局"
            }
          },
          "tencent-cloud-config/cos": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/tencentCloudConfig/tencentCloudConfigCosView"
              ], resolve);
            },
            metaInfo: {
              title: "腾讯云设置",
              name: "tencentCloudSet",
              attribution: "全局"
            }
          },
          "tencent-cloud-config/vod": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/tencentCloudConfig/tencentCloudConfigVodView"
              ], resolve);
            },
            metaInfo: {
              title: "腾讯云设置",
              name: "tencentCloudSet",
              attribution: "全局"
            }
          },
          "tencent-cloud-config/code": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/tencentCloudConfig/tencentCloudConfigCodeView"
              ], resolve);
            },
            metaInfo: {
              title: "腾讯云设置",
              name: "tencentCloudSet",
              attribution: "全局"
            }
          },
          "annex-set": {
            comLoad: function (resolve) {
              require(["../view/site/global/annexSetView"], resolve);
            },
            metaInfo: {
              title: "附件设置",
              name: "annexSet",
              attribution: "全局"
            }
          },
          "water-mark-set": {
            comLoad: function (resolve) {
              require(["../view/site/global/waterMarkSetView"], resolve);
            },
            metaInfo: {
              title: "水印设置",
              name: "waterMarkSet",
              attribution: "全局"
            }
          },
          "content-filter-set": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/contentFilteringSet/contentFilteringSetView"
              ], resolve);
            },
            metaInfo: {
              title: "内容过滤设置",
              name: "contentFilteringSet",
              attribution: "全局"
            }
          },
          "add-sensitive-words": {
            comLoad: function (resolve) {
              require([
                "../view/site/global/contentFilteringSet/addSensitiveWordsView"
              ], resolve);
            },
            metaInfo: {
              title: "内容过滤设置",
              name: "contentFilteringSet",
              attribution: "全局"
            }
          },
          /*'user-manage-set':{
            comLoad: function (resolve) {
              require(['../view/site/global/userManageSetView'], resolve)
            },
            metaInfo: {
              title: '后台用户管理',
              ame:'userManage',
              attribution:'全局'
            }
          },
          'role-manage-set':{
            comLoad: function (resolve) {
              require(['../view/site/global/roleManagesSet/roleManageSetView'], resolve)
            },
            metaInfo: {
              title: '后台角色管理',
              name:'roleManage',
              attribution:'全局'
            }
          },
          'role-manage-set/permission':{
            comLoad: function (resolve) {
              require(['../view/site/global/roleManagesSet/roleManagePermissionEditingView'], resolve)
            },
            metaInfo: {
              title: '后台角色管理权限编辑',
              name:'roleManage',
              attribution:'全局'
            }
          },*/

          //内容分类
          "cont-class": {
            comLoad: function (resolve) {
              // require(["../view/site/cont/contClassView"], resolve);
              require(["../view/site/cont/contClassPayView"], resolve);
            },
            metaInfo: {
              title: "内容分类",
              name: "contClass",
              attribution: "内容"
            }
          },
          //顶部分类
          "cont-classdingbu": {
            comLoad: function (resolve) {
              // require(["../view/site/cont/contClassView"], resolve);
              require(["../view/site/cont/contClassdingbuPayView"], resolve);
            },
            metaInfo: {
              title: "顶部分类",
              name: "contClassdingbu",
              attribution: "内容"
            }
          },
          //尾部分类
          "cont-classweibu": {
            comLoad: function (resolve) {
              // require(["../view/site/cont/contClassView"], resolve);
              require(["../view/site/cont/contClassweibuPayView"], resolve);
            },
            metaInfo: {
              title: "尾部分类",
              name: "contClassweibu",
              attribution: "内容"
            }
          },

          //标签分类
          "cont-classbiaoqian": {
            comLoad: function (resolve) {
              // require(["../view/site/cont/contClassView"], resolve);
              require(["../view/site/cont/contClassbiaoqianPayView"], resolve);
            },
            metaInfo: {
              title: "标签分类",
              name: "contClassbiaoqian",
              attribution: "内容"
            }
          },
          "cont-manage": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contManages/contManageView"
              ], resolve);
            },
            metaInfo: {
              title: "内容管理",
              name: "contManage",
              attribution: "内容",
              alias: "最新主题"
            }
          },
          "cont-manage/topic": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contManages/contManageView"
              ], resolve);
            },
            metaInfo: {
              title: "内容管理",
              name: "contManage",
              attribution: "内容",
              alias: "最新主题"
            }
          },
          "latest-reply": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contManages/latestReplyView"
              ], resolve);
            },
            metaInfo: {
              title: "内容管理",
              name: "contManage",
              attribution: "内容",
              alias: "最新回复"
            }
          },
          "cont-manage/search": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contManages/contManageSearchView"
              ], resolve);
            },
            metaInfo: {
              title: "内容管理",
              name: "contManage",
              attribution: "内容",
              alias: "搜索"
            }
          },
          "cont-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/contReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "内容审核",
              name: "contReview",
              attribution: "内容",
              alias: "主题审核"
            }
          },
          "reply-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/replyReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "回复审核",
              name: "contReview",
              attribution: "内容",
              alias: "回复审核"
            }
          },
          //圈子分类
          "cont-classquanzi": {
            comLoad: function (resolve) {
              // require(["../view/site/cont/contClassView"], resolve);
              require(["../view/site/cont/contClassquanziPayView"], resolve);
            },
            metaInfo: {
              title: "圈子分类",
              name: "contClassquanzi",
              attribution: "圈子"
            }
          },
          //圈子
          "cont-quanzi": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contQuan/contQuanView"
              ], resolve);
            },
            metaInfo: {
              title: "圈子列表",
              name: "contQuan",
              attribution: "圈子",
              alias: "最新圈子"
            }
          },
          //圈子审核
          "quan-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/quanReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "圈子审核",
              name: "quanReview",
              attribution: "圈子",
              alias: "待审圈子"
            }
          },
          "quanreply-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/quanreplyReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "圈子审核",
              name: "quanReview",
              attribution: "圈子",
              alias: "回圈待审"
            }
          },
          "cont-quanzi/topic": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contQuan/contQuanView"
              ], resolve);
            },
            metaInfo: {
              title: "圈子列表",
              name: "contQuan",
              attribution: "圈子",
              alias: "最新圈子"
            }
          },
          "quanzi-reply": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contQuan/quanziReplyView"
              ], resolve);
            },
            metaInfo: {
              title: "圈子列表",
              name: "contQuan",
              attribution: "圈子",
              alias: "回复圈子"
            }
          },
          //圈帖
          "cont-quantie": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contQuan/contQuantieView"
              ], resolve);
            },
            metaInfo: {
              title: "圈帖列表",
              name: "contQuantie",
              attribution: "圈子",
              alias: "最新圈帖"
            }
          },
		  "quantie-reply": {
		    comLoad: function (resolve) {
		      require([
		        "../view/site/cont/contQuan/quantieReplyView"
		      ], resolve);
		    },
		    metaInfo: {
		      title: "圈帖列表",
		      name: "contQuantie",
		      attribution: "圈子",
		      alias: "回复圈帖"
		    }
		  },
          //圈帖审核
          "quantie-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/quantieReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "圈帖审核",
              name: "quantieReview",
              attribution: "圈子",
              alias: "待审圈帖"
            }
          },
          "quantiereply-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/quantiereplyReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "圈帖审核",
              name: "quantieReview",
              attribution: "圈子",
              alias: "回圈帖待审"
            }
          },
          ///////////////////////////////////////////////////////////////////////
          //课程分类
          "cont-classke": {
            comLoad: function (resolve) {
              // require(["../view/site/cont/contClassView"], resolve);
              require(["../view/site/cont/contClasskePayView"], resolve);
            },
            metaInfo: {
              title: "课程分类",
              name: "contClasske",
              attribution: "课程"
            }
          },
          //课程
          "cont-ke": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contKe/contKeView"
              ], resolve);
            },
            metaInfo: {
              title: "课题列表",
              name: "contKe",
              attribution: "课程",
              alias: "最新课题"
            }
          },
          //回复课题
          "ke-reply": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contKe/keReplyView"
              ], resolve);
            },
            metaInfo: {
              title: "课题列表",
              name: "contKe",
              attribution: "课程",
              alias: "回复课题"
            }
          },
          //课题审核
          "ke-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/keReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "课题审核",
              name: "keReview",
              attribution: "课程",
              alias: "待审课题"
            }
          },
          "kereply-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/kereplyReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "课题审核",
              name: "keReview",
              attribution: "课程",
              alias: "回题待审"
            }
          },
          //课帖
          "cont-ketie": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contKe/contKetieView"
              ], resolve);
            },
            metaInfo: {
              title: "课帖列表",
              name: "contKetie",
              attribution: "课程",
              alias: "最新课帖"
            }
          },
          //回复课帖
          "ketie-reply": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contKe/ketieReplyView"
              ], resolve);
            },
            metaInfo: {
              title: "课帖列表",
              name: "contKetie",
              attribution: "课程",
              alias: "回复课帖"
            }
          },
          //课帖审核
          "ketie-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/ketieReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "课帖审核",
              name: "ketieReview",
              attribution: "课程",
              alias: "待审课帖"
            }
          },
          "ketiereply-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/ketiereplyReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "课帖审核",
              name: "ketieReview",
              attribution: "课程",
              alias: "回课帖待审"
            }
          },
          ///////////////////////////////////////////////////////////////////////
          //商城分类
          "cont-classshop": {
            comLoad: function (resolve) {
              // require(["../view/site/cont/contClassView"], resolve);
              require(["../view/site/cont/contClassshopPayView"], resolve);
            },
            metaInfo: {
              title: "商城分类",
              name: "contClassshop",
              attribution: "商城"
            }
          },
          //商品分类
          "cont-classgoods": {
            comLoad: function (resolve) {
              // require(["../view/site/cont/contClassView"], resolve);
              require(["../view/site/cont/contClassgoodsPayView"], resolve);
            },
            metaInfo: {
              title: "商品分类",
              name: "contClassgoods",
              attribution: "商城"
            }
          },
          //商城
          "cont-shop": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contShop/contShopView"
              ], resolve);
            },
            metaInfo: {
              title: "商城列表",
              name: "contShop",
              attribution: "商城",
              alias: "最新商城"
            }
          },
          //回复商城
          "shop-reply": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contShop/shopReplyView"
              ], resolve);
            },
            metaInfo: {
              title: "商城列表",
              name: "contShop",
              attribution: "商城",
              alias: "回复商城"
            }
          },
          //商城审核
          "shop-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/shopReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "审核商城",
              name: "shopReview",
              attribution: "商城",
              alias: "待审商城"
            }
          },
          "shopreply-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/shopreplyReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "审核商城",
              name: "shopReview",
              attribution: "商城",
              alias: "回商城待审"
            }
          },
          //商品
          "cont-goodstie": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contShop/contGoodstieView"
              ], resolve);
            },
            metaInfo: {
              title: "商品列表",
              name: "contGoodstie",
              attribution: "商城",
              alias: "最新商品"
            }
          },
          //回复商品
          "goodstie-reply": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contShop/goodstieReplyView"
              ], resolve);
            },
            metaInfo: {
              title: "商品列表",
              name: "contGoodstie",
              attribution: "商城",
              alias: "回复商品"
            }
          },
          //商品审核
          "goodstie-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/goodstieReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "商品审核",
              name: "goodstieReview",
              attribution: "商城",
              alias: "待审商品"
            }
          },
          "goodstiereply-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/goodstiereplyReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "商品审核",
              name: "goodstieReview",
              attribution: "商城",
              alias: "回商品待审"
            }
          },
          //提问
          "cont-ask": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contManages/contAskView",
              ], resolve);
            },
            metaInfo: {
              title: "提问管理",
              name: "contAsk",
              attribution: "内容",
              alias: "最新提问"
            }
          },
          "ask-reply": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contManages/askReplyView"
              ], resolve);
            },
            metaInfo: {
              title: "提问管理",
              name: "contAsk",
              attribution: "内容",
              alias: "提问回复"
            }
          },
          "ask-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/askReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "提问管理",
              name: "contAsk",
              attribution: "内容",
              alias: "提问审核"
            }
          },
          "askreply-review": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/contModeration/askreplyReviewView"
              ], resolve);
            },
            metaInfo: {
              title: "提问管理",
              name: "contAsk",
              attribution: "内容",
              alias: "回复提问审核"
            }
          },
          "cont-classask": {
            comLoad: function (resolve) {
              require(["../view/site/cont/contClassaskPayView"], resolve);
            },
            metaInfo: {
              title: "提问管理",
              name: "contAsk",
              attribution: "内容",
              alias: "提问分类"
            }
          },
          //话题管理
          "topic-management": {
            comLoad: function(resolve) {
              require(["../view/site/cont/topicManagement/topicManagementView"], resolve);
            },
            metaInfo: {
                title: "话题管理",
                name: "topicManagement",
                attribution: "内容"
                }
          },
          // 举报管理
          "report-manage": {
            comLoad: function (resolve) {
              require(["../view/site/cont/reportManage/reportManageView"], resolve);
            },
            metaInfo: {
              title: "举报管理",
              name: "reportManage",
              attribution: "内容",
              alias: "最新举报"
            }
          },
          "report-processed": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/reportManage/reportProcessedView"
              ], resolve);
            },
            metaInfo: {
              title: "举报管理",
              name: "reportManage",
              attribution: "内容",
              alias: "已处理记录"
            }
          },
          "recycle-bin": {
            comLoad: function (resolve) {
              require(["../view/site/cont/recycleBin/recycleBinView"], resolve);
            },
            metaInfo: {
              title: "回收站",
              name: "recycleBin",
              attribution: "内容",
              alias: "主题"
            }
          },
          "recycle-bin-reply": {
            comLoad: function (resolve) {
              require([
                "../view/site/cont/recycleBin/recycleBinReplyView"
              ], resolve);
            },
            metaInfo: {
              title: "回收站",
              name: "recycleBin",
              attribution: "内容",
              alias: "回复"
            }
          },
          //财务分类
          "fund-details": {
            comLoad: function (resolve) {
              require(["../view/site/finance/fundDetailsView"], resolve);
            },
            metaInfo: {
              title: "资金明细",
              name: "fundDetails",
              attribution: "财务"
            }
          },
          "order-record": {
            comLoad: function (resolve) {
              require(["../view/site/finance/orderRecordView"], resolve);
            },
            metaInfo: {
              title: "订单记录",
              name: "orderRecord",
              attribution: "财务"
            }
          },
          "withdrawal-application": {
            comLoad: function (resolve) {
              require([
                "../view/site/finance/withdrawMange/withdrawalApplicationView"
              ], resolve);
            },
            metaInfo: {
              title: "提现管理",
              name: "withdrawMange",
              attribution: "财务",
              alias: "提现申请"
            }
          },
          "withdrawal-setting": {
            comLoad: function (resolve) {
              require([
                "../view/site/finance/withdrawMange/withdrawalSettingView"
              ], resolve);
            },
            metaInfo: {
              title: "提现管理",
              name: "withdrawMange",
              attribution: "财务",
              alias: "提现设置"
            }
          },
          "financial-statistics": {
            comLoad: function (resolve) {
              require(["../view/site/finance/financialStatistics"], resolve);
            },
            metaInfo: {
              title: "财务统计",
              name: "financialStatistics",
              attribution: "财务"
            }
          },
          //用户
          "user-manage": {
            comLoad: function (resolve) {
              require(["../view/site/user/userManage/userManageView"], resolve);
            },
            metaInfo: {
              title: "用户管理",
              name: "userManage",
              attribution: "用户"
            }
          },
          "user-search-list": {
            comLoad: function (resolve) {
              require(["../view/site/user/userManage/userSearchList"], resolve);
            },
            metaInfo: {
              title: "用户管理",
              name: "userManage",
              attribution: "用户"
            }
          },
          "user-details": {
            comLoad: function (resolve) {
              require([
                "../view/site/user/userManage/userDetailsView"
              ], resolve);
            },
            metaInfo: {
              title: "用户管理",
              name: "userManage",
              attribution: "用户"
            }
          },
          wallet: {
            comLoad: function (resolve) {
              require(["../view/site/user/userManage/walletView"], resolve);
            },
            metaInfo: {
              title: "用户管理",
              name: "userManage",
              attribution: "用户"
            }
          },
          "user-rol": {
            comLoad: function (resolve) {
              require(["../view/site/user/userRol/userRolView"], resolve);
            },
            metaInfo: {
              title: "用户角色",
              name: "userRol",
              attribution: "用户"
            }
          },
          "rol-permission": {
            comLoad: function (resolve) {
              // require(["../view/site/user/userRol/rolPermissionView"], resolve);
              require(["../view/site/user/userRol/rolPermissionPayView"], resolve);
            },
            metaInfo: {
              title: "用户角色",
              name: "userRol",
              attribution: "用户"
            }
          },
          "user-review": {
            comLoad: function (resolve) {
              require(["../view/site/user/userReviewView"], resolve);
            },
            metaInfo: {
              title: "用户审核",
              name: "userReview",
              attribution: "用户"
            }
          }
        },
        metaInfo: {
          title: "后台架子"
        }
      },
      "admin/login": {
        comLoad: function (resolve) {
          require(["../view/site/login/loginView"], resolve);
        },
        metaInfo: {
          title: "后台登录"
        }
      }
    }
  },
  /**
   * 后端路由守卫
   * @param  {[type]}   to   [description]
   * @param  {Function} next [description]
   * @return {[type]}        [description]
   */
  beforeEnter: function (to, form, next) {
    let token = webDb.getLItem("Authorization");
    let tokenId = webDb.getLItem("tokenId");
    let groupId = "";

    if (!token && !tokenId) {
      if (to.path == "/admin/login") {
        next();
        return;
      }
      next({path: "/admin/login"});
    } else {
      this.getUserInfo(tokenId)
        .then(res => {
          groupId = res.readdata.groups[0]._data.id;
          webDb.setLItem("username", res.data.attributes.username);
          if (groupId === "1") {
            if (to.path == "/admin/login") {
              next("/admin");
              return;
            }
            next();
          } else {
            alert("权限不足！");
            if (to.path == "/admin/login") {
              next();
              return;
            }
            next({path: "/admin/login"});
          }
        })
        .catch(() => {
          if (to.path == "/admin/login") {
            next();
            return;
          }
          next({path: "/admin/login"});
        });
    }
  },
  getUserInfo(id) {
    return appFetch({
      url: "users",
      method: "get",
      splice: "/" + id,
      data: {
        include: ["groups"]
      }
    })
      .then(res => {
        return res;
      })
      .catch(err => {
      });
  }
};
