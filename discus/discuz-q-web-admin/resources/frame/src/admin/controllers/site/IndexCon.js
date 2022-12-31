/**
 * 后台Index
 */
import webDb from "../../../helpers/webDbHelper";
import appConfig from "../../../../../frame/config/appConfig";
import Card from '@/admin/view/site/common/card/card';
import CardRow from '@/admin/view/site/common/card/cardRow';

export default {
  data: function() {
    return {
      indexTitle: "管理中心首页", //页面内容标题  /顶部导航下面
      sideTitle: "首页", //左侧菜单标题

      /*菜单列表里的name必须和路由里面的name一致，*/
      navList: [
        {
          id: 0,
          title: "首页",
          name: "home",
          submenu: [
            {
              id: 0,
              title: "管理中心首页",
              name: "controlCenter",
              icon: "iconshouye"
            },
            {
              id: 1,
              title: "数据看板",
              name: "dataPanel",
              icon: "iconcaiwutongji"
            }
          ]
        },
        {
          id: 1,
          title: "全局",
          name: "global",
          submenu: [
            {
              id: 0,
              title: "站点设置",
              name: "siteSet",
              icon: "iconzhandianshezhi",
              submenu: [
                {
                  id: 0,
                  title: "站点信息",
                  name: "siteSet",
                  icon: "iconzhandianshezhi"
                },
                {
                  id: 1,
                  title: "主题设置",
                  name: "siteSet",
                  icon: "iconzhandianshezhi"
                },
                {
                  id: 2,
                  title: "功能设置",
                  name: "siteSet",
                  icon: "iconzhandianshezhi"
                },
                {
                  id: 3,
                  title: "首页数据设置",
                  name: "siteSet",
                  icon: "iconzhandianshezhi"
                },
                {
                  id: 4,
                  title: "积分设置",
                  name: "siteSet",
                  icon: "iconzhandianshezhi"
                },
                {
                  id: 5,
                  title: "成长值设置",
                  name: "siteSet",
                  icon: "iconzhandianshezhi"
                },
                {
                  id: 6,
                  title: "签到设置",
                  name: "siteSet",
                  icon: "iconzhandianshezhi"
                },
                {
                  id: 7,
                  title: "H5图标设置",
                  name: "siteSet",
                  icon: "iconzhandianshezhi"
                },
                {
                  id: 8,
                  title: "小程序图标设置",
                  name: "siteSet",
                  icon: "iconzhandianshezhi"
                }
              ]
            },
            {
              id: 1,
              title: "注册与登录设置",
              name: "signUpSet",
              icon: "iconzhuceshezhi"
            },
            {
              id: 2,
              title: "第三方登录设置",
              name: "worthMentioningSet",
              icon: "icondisanfangdenglushezhi"
            },
            {
              id: 3,
              title: "支付设置",
              name: "paySet",
              icon: "iconzhifushezhi"
            },
            {
              id: 4,
              title: "附件设置",
              name: "annexSet",
              icon: "iconfujianshezhi"
            },
            {
              id: 5,
              title: "水印设置",
              name: "waterMarkSet",
              icon: "iconwatermark"
            },
            {
              id: 6,
              title: "内容过滤设置",
              name: "contentFilteringSet",
              icon: "iconneirongguolvshezhi"
            },
            {
              id: 7,
              title: "腾讯云设置",
              name: "tencentCloudSet",
              icon: "icontengxunyun"
            },
            {
              id: 8,
              title: "通知设置",
              name: "noticeSet",
              icon: "icontongzhi"
            },
            {
              id: 9,
              title: "其他服务设置",
              name: "otherServiceSet",
              icon: "iconqitafuwushezhi"
            },
            {
              id: 10,
              title: "IP属地地址",
              name: "siteIpSet",
              icon: "iconqitafuwushezhi"
            },
            // {
            //   id:7,
            //   title:'后台用户管理',
            //   name:'adminUserManage',
            //   icon:'iconyonghuguanli'
            // },
            // {
            //   id:8,
            //   title:'后台角色管理',
            //   name:'adminRoleManage',
            //   icon:'iconjiaoseguanli'
            // }
            // {
            //   id: 9,
            //   title: "操作日志",
            //   name: "operationLog",
            //   icon: "iconqitafuwushezhi"
            // },
          ]
        },
        {
          id: 2,
          title: "用户",
          name: "user",
          submenu: [
            {
              id: 20,
              title: "用户管理",
              name: "userManage",
              icon: "iconyonghuguanli"
            },
            {
              id: 21,
              title: "用户角色",
              name: "userRol",
              icon: "iconjiaoseguanli"
            },
            {
              id: 22,
              title: "用户审核",
              name: "userReview",
              icon: "iconyonghushenhe"
            }
          ]
        },
        {
          id: 3,
          title: "内容",
          name: "cont",
          submenu: [
            {
              id: 0,
              title: "内容分类",
              name: "contClass",
              icon: "iconneirongfenlei",
            },
            {
              id: 1,
              title: "内容管理",
              name: "contManage",
              icon: "iconneirongguanli",
              submenu: [
                {
                  id: 11,
                  title: "最新主题",
                  name: "contManage",
                  icon: "iconneirongguanli"
                },
                {
                  id: 13,
                  title: "最新回复",
                  name: "contManage",
                  icon: "iconneirongguanli"
                }
              ]
            },
            {
              id: 2,
              title: "内容审核",
              name: "contReview",
              icon: "iconneirongshenhe",
              submenu: [
                {
                  id: 21,
                  title: "主题审核",
                  name: "contReview",
                  icon: "iconneirongshenhe"
                },
                {
                  id: 22,
                  title: "回复审核",
                  name: "contReview",
                  icon: "iconneirongshenhe"
                }
              ]
            },
            {
              id: 3,
              title: "话题管理",
              name: "topicManagement",
              icon: "icontopicmanagement",
            },
            {
              id: 4,
              title: "举报管理",
              name: "reportManage",
              icon: "iconjubaoguanli",
              submenu: [
                {
                  id: 41,
                  title: "最新举报",
                  name: "reportManage",
                  icon: "iconjubaoguanli"
                },
                {
                  id: 42,
                  title: "已处理记录",
                  name: "reportManage",
                  icon: "iconjubaoguanli"
                }
              ]
            },
            {
              id: 5,
              title: "回收站",
              name: "recycleBin",
              icon: "iconhuishouzhan",
              submenu: [
                {
                  id: 51,
                  title: "主题",
                  name: "recycleBin",
                  icon: "iconhuishouzhan"
                },
                {
                  id: 52,
                  title: "回复",
                  name: "recycleBin",
                  icon: "iconhuishouzhan"
                }
              ]
            },
            {
              id: 6,
              title: "顶部分类",
              name: "contClassdingbu",
              icon: "iconneirongfenlei",
            },
            {
              id: 7,
              title: "尾部分类",
              name: "contClassweibu",
              icon: "iconneirongfenlei",
            },
            {
              id: 8,
              title: "标签分类",
              name: "contClassbiaoqian",
              icon: "iconneirongfenlei",
            },
            {
              id: 9,
              title: "提问管理",
              name: "contAsk",
              icon: "iconneirongshenhe",
              submenu: [
                {
                  id: 1,
                  title: "最新提问",
                  name: "contAsk",
                  icon: "iconneirongguanli"
                },
                {
                  id: 2,
                  title: "提问回复",
                  name: "contAsk",
                  icon: "iconneirongguanli"
                },
                {
                  id: 3,
                  title: "提问审核",
                  name: "contAsk",
                  icon: "iconneirongshenhe"
                },
                {
                  id: 4,
                  title: "回复提问审核",
                  name: "contAsk",
                  icon: "iconneirongshenhe"
                },
                {
                  id: 5,
                  title: "提问分类",
                  name: "contAsk",
                  icon: "iconneirongshenhe"
                }
              ]
            },
          ]
        },
        {
          id: 4,
          title: "财务",
          name: "finance",
          submenu: [
            {
              id: 40,
              title: "资金明细",
              name: "fundDetails",
              icon: "iconzijinmingxi"
            },
            {
              id: 41,
              title: "订单记录",
              name: "orderRecord",
              icon: "icondingdanjilu"
            },
            {
              id: 42,
              title: "提现管理",
              name: "withdrawMange",
              icon: "icontixianguanli",
              submenu: [
                {
                  id: 421,
                  title: "提现申请",
                  name: "withdrawMange",
                  icon: "icontixianguanli"
                },
                {
                  id: 422,
                  title: "提现设置",
                  name: "withdrawMange",
                  icon: "icontixianguanli"
                }
              ]
            },
            {
              id: 43,
              title: "财务统计",
              name: "financialStatistics",
              icon: "iconcaiwutongji"
            }
          ]
        },
        {
          id: 5,
          title: "圈子",
          name: "quanzi",
          submenu: [
            {
              id: 0,
              title: "圈子分类",
              name: "contClassquanzi",
              icon: "iconzijinmingxi"
            },
            {
              id: 1,
              title: "圈子列表",
              name: "contQuan",
              icon: "icondingdanjilu",
              submenu: [
                {
                  id: 11,
                  title: "最新圈子",
                  name: "contQuan",
                  icon: "iconneirongguanli"
                },
                {
                  id: 13,
                  title: "回复圈子",
                  name: "contQuan",
                  icon: "iconneirongguanli"
                }
              ]
            },
            {
              id: 2,
              title: "圈子审核",
              name: "quanReview",
              icon: "iconneirongshenhe",
              submenu: [
                {
                  id: 21,
                  title: "待审圈子",
                  name: "quanReview",
                  icon: "iconneirongshenhe"
                },
                {
                  id: 22,
                  title: "回圈待审",
                  name: "quanReview",
                  icon: "iconneirongshenhe"
                }
              ]
            },
            {
              id: 3,
              title: "圈帖列表",
              name: "contQuantie",
              icon: "icondingdanjilu",
              submenu: [
                {
                  id: 11,
                  title: "最新圈帖",
                  name: "contQuantie",
                  icon: "iconneirongguanli"
                },
                {
                  id: 13,
                  title: "回复圈帖",
                  name: "contQuantie",
                  icon: "iconneirongguanli"
                }
              ]
            },
            {
              id: 4,
              title: "圈帖审核",
              name: "quantieReview",
              icon: "iconneirongshenhe",
              submenu: [
                {
                  id: 21,
                  title: "待审圈帖",
                  name: "quantieReview",
                  icon: "iconneirongshenhe"
                },
                {
                  id: 22,
                  title: "回圈帖待审",
                  name: "quantieReview",
                  icon: "iconneirongshenhe"
                }
              ]
            },
          ]
        },
        {
          id: 6,
          title: "课程",
          name: "ke",
          submenu: [
            {
              id: 0,
              title: "课题分类",
              name: "contClasske",
              icon: "iconzijinmingxi"
            },
            {
              id: 1,
              title: "课题列表",
              name: "contKe",
              icon: "icondingdanjilu",
              submenu: [
                {
                  id: 11,
                  title: "最新课题",
                  name: "contKe",
                  icon: "iconneirongguanli"
                },
                {
                  id: 13,
                  title: "回复课题",
                  name: "contKe",
                  icon: "iconneirongguanli"
                }
              ]
            },
            {
              id: 2,
              title: "课题审核",
              name: "keReview",
              icon: "iconneirongshenhe",
              submenu: [
                {
                  id: 21,
                  title: "待审课题",
                  name: "keReview",
                  icon: "iconneirongshenhe"
                },
                {
                  id: 22,
                  title: "回题待审",
                  name: "keReview",
                  icon: "iconneirongshenhe"
                }
              ]
            },
            {
              id: 3,
              title: "课帖列表",
              name: "contKetie",
              icon: "icondingdanjilu",
              submenu: [
                {
                  id: 11,
                  title: "最新课帖",
                  name: "contKetie",
                  icon: "iconneirongguanli"
                },
                {
                  id: 13,
                  title: "回复课帖",
                  name: "contKetie",
                  icon: "iconneirongguanli"
                }
              ]
            },
            {
              id: 4,
              title: "课帖审核",
              name: "ketieReview",
              icon: "iconneirongshenhe",
              submenu: [
                {
                  id: 21,
                  title: "待审课帖",
                  name: "ketieReview",
                  icon: "iconneirongshenhe"
                },
                {
                  id: 22,
                  title: "回课帖待审",
                  name: "ketieReview",
                  icon: "iconneirongshenhe"
                }
              ]
            },
          ]
        },
        {
          id: 7,
          title: "商城",
          name: "shop",
          submenu: [
            {
              id: 0,
              title: "商城分类",
              name: "contClassshop",
              icon: "iconzijinmingxi"
            },
            {
              id: 0,
              title: "商品分类",
              name: "contClassgoods",
              icon: "iconzijinmingxi"
            },
            {
              id: 1,
              title: "商城列表",
              name: "contShop",
              icon: "icondingdanjilu",
              submenu: [
                {
                  id: 11,
                  title: "最新商城",
                  name: "contShop",
                  icon: "iconneirongguanli"
                },
                {
                  id: 13,
                  title: "回复商城",
                  name: "contShop",
                  icon: "iconneirongguanli"
                }
              ]
            },
            {
              id: 2,
              title: "审核商城",
              name: "shopReview",
              icon: "iconneirongshenhe",
              submenu: [
                {
                  id: 21,
                  title: "待审商城",
                  name: "shopReview",
                  icon: "iconneirongshenhe"
                },
                {
                  id: 22,
                  title: "回商城待审",
                  name: "shopReview",
                  icon: "iconneirongshenhe"
                }
              ]
            },
            {
              id: 3,
              title: "商品列表",
              name: "contGoodstie",
              icon: "icondingdanjilu",
              submenu: [
                {
                  id: 11,
                  title: "最新商品",
                  name: "contGoodstie",
                  icon: "iconneirongguanli"
                },
                {
                  id: 13,
                  title: "回复商品",
                  name: "contGoodstie",
                  icon: "iconneirongguanli"
                }
              ]
            },
            {
              id: 4,
              title: "商品审核",
              name: "goodstieReview",
              icon: "iconneirongshenhe",
              submenu: [
                {
                  id: 21,
                  title: "待审商品",
                  name: "goodstieReview",
                  icon: "iconneirongshenhe"
                },
                {
                  id: 22,
                  title: "回商品待审",
                  name: "goodstieReview",
                  icon: "iconneirongshenhe"
                }
              ]
            },
          ]
        }
      ], //导航菜单列表
      navSelect: "", //导航选中

      sideList: [], //侧边菜单
      sideSelect: "", //侧边选中

      sideSubmenu: [], //侧边栏子菜单
      sideSubmenuSelect: "", //侧边栏子菜单选中

      userName: "", //用户名

      dialogVisible: false, // 云API配置弹框
      secretId:'',
      secretKey:'',
      appId:'',
    };
  },
  methods: {
    // 清空缓存
    clearCache() {
      this.appFetch({
        url: "clearCache",
        method: "delete",
        data: {}
      }).then(data => {
        if (data.errors) {
          if (data.errors[0].detail) {
            this.$message.error(
              data.errors[0].code + "\n" + data.errors[0].detail[0]
            );
          } else {
            this.$message.error(data.errors[0].code);
          }
        } else {
          this.$message({
            message: "缓存清空完毕",
            type: "success"
          });
        }
      })
      .catch(error => {});
    },
    /*
     *  导航菜单点击事件
     * */
    menuClick(item) {
      this.sideTitle = item.title;

      this.navSelect = item.name;

      switch (item.name) {
        case "home":
          this.sideList = this.navList[0].submenu;
          this.sideSelect = this.navList[0].submenu[0].name;
          this.indexTitle = this.navList[0].submenu[0].title;
          this.sideSubmenu =
            this.navList[0].submenu[0].submenu === undefined || null
              ? []
              : this.navList[0].submenu[0].submenu;
          this.$router.push({ path: "/admin/home" });
          break;
        case "global":
          this.sideList = this.navList[1].submenu;
          this.sideSelect = this.navList[1].submenu[0].name;
          this.indexTitle = this.navList[1].submenu[0].title;
          this.sideSubmenu =
            this.navList[1].submenu[0].submenu === undefined || null
              ? []
              : this.navList[1].submenu[0].submenu;
          this.sideSubmenuSelect = this.navList[1].submenu[0].submenu[0].title;
          this.$router.push({ path: "/admin/site-set" });
          break;
        case "user":
          this.sideList = this.navList[2].submenu;
          this.sideSelect = this.navList[2].submenu[0].name;
          this.indexTitle = this.navList[2].submenu[0].title;
          this.sideSubmenu =
            this.navList[2].submenu[0].submenu === undefined || null
              ? []
              : this.navList[0].submenu[0].submenu;
          this.$router.push({ path: "/admin/user-manage" });
          break;
        case "cont":
          this.sideList = this.navList[3].submenu;
          this.sideSelect = this.navList[3].submenu[0].name;
          this.indexTitle = this.navList[3].submenu[0].title;
          this.sideSubmenu =
            this.navList[0].submenu[0].submenu === undefined || null
              ? []
              : this.navList[0].submenu[0].submenu;
          this.$router.push({ path: "/admin/cont-class" });
          break;
        case "finance":
          this.sideList = this.navList[4].submenu;
          this.sideSelect = this.navList[4].submenu[0].name;
          this.indexTitle = this.navList[4].submenu[0].title;
          this.sideSubmenu =
            this.navList[0].submenu[0].submenu === undefined || null
              ? []
              : this.navList[0].submenu[0].submenu;
          this.$router.push({ path: "/admin/fund-details" });
          break;
        case "quanzi":
          this.sideList = this.navList[5].submenu;
          this.sideSelect = this.navList[5].submenu[0].name;
          this.indexTitle = this.navList[5].submenu[0].title;
          this.sideSubmenu =
            this.navList[0].submenu[0].submenu === undefined || null
              ? []
              : this.navList[0].submenu[0].submenu;
          this.$router.push({ path: "/admin/cont-classquanzi" });
          break;
        case "ke":
          this.sideList = this.navList[6].submenu;
          this.sideSelect = this.navList[6].submenu[0].name;
          this.indexTitle = this.navList[6].submenu[0].title;
          this.sideSubmenu =
            this.navList[0].submenu[0].submenu === undefined || null
              ? []
              : this.navList[0].submenu[0].submenu;
          this.$router.push({ path: "/admin/cont-classke" });
          break;
        case "shop":
          this.sideList = this.navList[7].submenu;
          this.sideSelect = this.navList[7].submenu[0].name;
          this.indexTitle = this.navList[7].submenu[0].title;
          this.sideSubmenu =
            this.navList[0].submenu[0].submenu === undefined || null
              ? []
              : this.navList[0].submenu[0].submenu;
          this.$router.push({ path: "/admin/cont-classshop" });
          break;
        default:
          this.sideList = [];
      }

      this.checkQcloud();
    },

    /*
     *  左侧菜单点击事件
     * */
    sideClick(item) {
      this.sideSelect = item.name;
      this.indexTitle = item.title;

      this.sideSubmenu = [];

      switch (item.name) {
        case "controlCenter":
          this.$router.push({ path: "/admin/home" });
          break;
        case "dataPanel":
          this.$router.push({ path: "/admin/data-panel" });
          break;

        case "siteSet":
          this.sideSubmenu = this.navList[1].submenu[0].submenu;
          this.sideSubmenuSelect = this.navList[1].submenu[0].submenu[0].title;
          this.$router.push({
            path: "/admin/site-set",
            query: { name: "站点设置" }
          });
          break;
        case "signUpSet":
          this.$router.push({ path: "/admin/sign-up-set" });
          break;
        case "worthMentioningSet":
          this.$router.push({ path: "/admin/worth-mentioning-set" });
          break;
        case "paySet":
          this.$router.push({ path: "/admin/pay-set" });
          break;
        case "annexSet":
          this.$router.push({ path: "/admin/annex-set" });
          break;
        case "waterMarkSet":
          this.$router.push({ path: "/admin/water-mark-set" });
          break;
        case "contentFilteringSet":
          this.$router.push({ path: "/admin/content-filter-set" });
          break;
        case "tencentCloudSet":
          this.$router.push({ path: "/admin/tencent-cloud-set" });
          break;
        case "noticeSet":
          this.$router.push({ path: "/admin/system-notice" });
          break;
        // case "operationLog":
        //   this.$router.push({ path: "/admin/operation-log" });
        //   break;
        case "adminUserManage":
          this.$router.push({ path: "/admin/user-manage-set" });
          break;
        case "adminRoleManage":
          this.$router.push({ path: "/admin/role-manage-set" });
          break;

        case "userManage":
          this.$router.push({ path: "/admin/user-manage" });
          break;
        case "userRol":
          this.$router.push({ path: "/admin/user-rol" });
          break;
		case "userSearchList":
		  this.$router.push({ path: "/admin/user-search-list" });
		  break;
        case "userReview":
          this.$router.push({ path: "/admin/user-review" });
          break;

        case "contClass":
          this.$router.push({ path: "/admin/cont-class" });
          break;
        case "contClassdingbu":
          this.$router.push({ path: "/admin/cont-classdingbu" });
          break;
        case "contClassweibu":
          this.$router.push({ path: "/admin/cont-classweibu" });
          break;
        case "contClassbiaoqian":
          this.$router.push({ path: "/admin/cont-classbiaoqian" });
          break;
        case "contClassquanzi":
          this.$router.push({ path: "/admin/cont-classquanzi" });
          break;
        case "contClasske":
          this.$router.push({ path: "/admin/cont-classke" });
          break;
        case "contClassshop":
          this.$router.push({ path: "/admin/cont-classshop" });
          break;
		case "contClassgoods":
		  this.$router.push({ path: "/admin/cont-classgoods" });
		  break;

        case "contManage":
          this.sideSubmenu = this.navList[3].submenu[1].submenu;
          this.sideSubmenuSelect = this.navList[3].submenu[1].submenu[0].title;
          this.$router.push({ path: "/admin/cont-manage" });
          break;
        case "contReview":
          this.sideSubmenu = this.navList[3].submenu[2].submenu;
          this.sideSubmenuSelect = this.navList[3].submenu[2].submenu[0].title;
          this.$router.push({ path: "/admin/cont-review" });
          break;
        case "recycleBin":
          this.sideSubmenu = this.navList[3].submenu[4].submenu;
          this.sideSubmenuSelect = this.navList[3].submenu[4].submenu[0].title;
          this.$router.push({ path: "/admin/recycle-bin" });
          break;

        case "topicManagement":
          this.$router.push({ path: "/admin/topic-management" });
          break;
        case "reportManage": //举报管理
          this.sideSubmenu = this.navList[3].submenu[4].submenu;
          this.sideSubmenuSelect = this.navList[3].submenu[4].submenu[0].title;
          this.$router.push({ path: "/admin/report-manage" });
          break;
        case "contAsk": //提问
          this.sideSubmenu = this.navList[3].submenu[9].submenu;
          this.sideSubmenuSelect = this.navList[3].submenu[9].submenu[0].title;
          this.$router.push({ path: "/admin/cont-ask" });
          break;
        case "askReply": //提问
          this.sideSubmenu = this.navList[3].submenu[9].submenu;
          this.sideSubmenuSelect = this.navList[3].submenu[9].submenu[1].title;
          this.$router.push({ path: "/admin/ask-reply" });
          break;
        case "askReview": //提问
          this.sideSubmenu = this.navList[3].submenu[9].submenu;
          this.sideSubmenuSelect = this.navList[3].submenu[9].submenu[2].title;
          this.$router.push({ path: "/admin/ask-review" });
          break;
        case "askreplyReview": //提问
          this.sideSubmenu = this.navList[3].submenu[9].submenu;
          this.sideSubmenuSelect = this.navList[3].submenu[9].submenu[3].title;
          this.$router.push({ path: "/admin/askreply-review" });
          break;
        case "contClassask": //提问
          this.sideSubmenu = this.navList[3].submenu[9].submenu;
          this.sideSubmenuSelect = this.navList[3].submenu[9].submenu[4].title;
          this.$router.push({ path: "/admin/cont-classask" });
          break;
        case "fundDetails":
          this.$router.push({ path: "/admin/fund-details" });
          break;
        case "orderRecord":
          this.$router.push({ path: "/admin/order-record" });
          break;
        case "withdrawMange":
          this.sideSubmenu = this.navList[4].submenu[2].submenu;
          this.sideSubmenuSelect = this.navList[4].submenu[2].submenu[0].title;
          this.$router.push({ path: "/admin/withdrawal-application" });
          break;
        case "contQuan":
          this.sideSubmenu = this.navList[5].submenu[1].submenu;
          this.sideSubmenuSelect = this.navList[5].submenu[1].submenu[0].title;
          this.$router.push({ path: "/admin/cont-quanzi" });
          break;
        case "quanReview":
          this.sideSubmenu = this.navList[5].submenu[2].submenu;
          this.sideSubmenuSelect = this.navList[5].submenu[2].submenu[0].title;
          this.$router.push({ path: "/admin/quan-review" });
          break;
        case "contQuantie":
        this.sideSubmenu = this.navList[5].submenu[3].submenu;
        this.sideSubmenuSelect = this.navList[5].submenu[3].submenu[0].title;
        this.$router.push({ path: "/admin/cont-quantie" });
        break;
        case "quantieReview":
        this.sideSubmenu = this.navList[5].submenu[4].submenu;
        this.sideSubmenuSelect = this.navList[5].submenu[4].submenu[0].title;
        this.$router.push({ path: "/admin/quantie-review" });
        break;
        case "contKe":
          this.sideSubmenu = this.navList[6].submenu[1].submenu;
          this.sideSubmenuSelect = this.navList[6].submenu[1].submenu[0].title;
          this.$router.push({ path: "/admin/cont-ke" });
          break;
        case "keReview":
          this.sideSubmenu = this.navList[6].submenu[2].submenu;
          this.sideSubmenuSelect = this.navList[6].submenu[2].submenu[0].title;
          this.$router.push({ path: "/admin/ke-review" });
          break;
        case "contKetie":
          this.sideSubmenu = this.navList[6].submenu[3].submenu;
          this.sideSubmenuSelect = this.navList[6].submenu[3].submenu[0].title;
          this.$router.push({ path: "/admin/cont-ketie" });
          break;
        case "ketieReview":
          this.sideSubmenu = this.navList[6].submenu[4].submenu;
          this.sideSubmenuSelect = this.navList[6].submenu[4].submenu[0].title;
          this.$router.push({ path: "/admin/ketie-review" });
          break;
        /*case "contShop":
          this.sideSubmenu = this.navList[7].submenu[1].submenu;
          this.sideSubmenuSelect = this.navList[7].submenu[1].submenu[0].title;
          this.$router.push({ path: "/admin/cont-shop" });
          break;*/
        case "contGoods":
          this.sideSubmenu = this.navList[7].submenu[1].submenu;
          this.sideSubmenuSelect = this.navList[7].submenu[1].submenu[0].title;
          this.$router.push({ path: "/admin/cont-classgoods" });
          break;
		case "contShop":
		  this.sideSubmenu = this.navList[7].submenu[2].submenu;
		  this.sideSubmenuSelect = this.navList[7].submenu[2].submenu[0].title;
		  this.$router.push({ path: "/admin/cont-shop" });
		  break;
        case "shopReview":
          this.sideSubmenu = this.navList[7].submenu[3].submenu;
          this.sideSubmenuSelect = this.navList[7].submenu[3].submenu[0].title;
          this.$router.push({ path: "/admin/shop-review" });
          break;
        case "contGoodstie":
          this.sideSubmenu = this.navList[7].submenu[4].submenu;
          this.sideSubmenuSelect = this.navList[7].submenu[4].submenu[0].title;
          this.$router.push({ path: "/admin/cont-goodstie" });
          break;
        case "goodstieReview":
          this.sideSubmenu = this.navList[7].submenu[5].submenu;
          this.sideSubmenuSelect = this.navList[7].submenu[5].submenu[0].title;
          this.$router.push({ path: "/admin/goodstie-review" });
          break;

        case "financialStatistics":
          this.$router.push({ path: "/admin/financial-statistics" });
          break;
        case "otherServiceSet":
          this.$router.push({ path: "/admin/other-service-set" });
          break;
        case "siteIpSet":
          this.$router.push({ path: "/admin/site-ip-set" });
          break;
      }
      this.checkQcloud();
    },

    /*
     *  跳转到站点首页
     * */
    jumpIndex() {
      let Url = "";
      Url = appConfig.baseUrl;

      this.$router.push({
        path: "/admin"
      });
    },

    /*
     *  左侧菜单的子菜单(位置对应：横向导航下面)点击事件
     *  配置点击子菜单后跳转页面
     * */
    sideSubmenuClick(title) {
      switch (title) {
        case "站点信息":
          this.sideSubmenuSelect = title;
          this.$router.push({
            path: "/admin/site-set",
            query: { name: "站点设置" }
          });
          break;
        case "主题设置":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/site-theme" });
          break;
        case "功能设置":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/site-function-set" });
          break;
        case "首页数据设置":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/site-sort-set" });
          break;
        case "IP属地设置":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/site-ip-set" });
          break;
        case "积分设置":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/site-integral-set" });
          break;
        case "成长值设置":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/site-growup-set" });
          break;
        case "签到设置":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/site-signin-set" });
          break;
        case "H5图标设置":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/site-tubiao" });
          break;
        case "小程序图标设置":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/site-weitubiao" });
          break;
        case "最新主题":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/cont-manage" });
          break;
        case "最新回复":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/latest-reply" });
          break;
        case "最新圈子":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/cont-quanzi" });
          break;
        case "回复圈子":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/quanzi-reply" });
          break;
        case "待审圈子":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/quan-review" });
          break;
        case "回圈待审":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/quanreply-review" });
          break;
        //圈帖
        case "最新圈帖":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/cont-quantie" });
          break;
        case "回复圈帖":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/quantie-reply" });
          break;
        case "待审圈帖":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/quantie-review" });
          break;
        case "回圈帖待审":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/quantiereply-review" });
          break;
          //课题
        case "最新课题":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/cont-ke" });
          break;
        case "回复课题":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/ke-reply" });
          break;
        case "待审课题":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/ke-review" });
          break;
        case "回题待审":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/kereply-review" });
          break;
        //课帖
        case "最新课帖":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/cont-ketie" });
          break;
        case "回复课帖":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/ketie-reply" });
          break;
        case "待审课帖":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/ketie-review" });
          break;
        case "回课帖待审":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/ketiereply-review" });
          break;
        //商城
        case "最新商城":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/cont-shop" });
          break;
        case "回复商城":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/shop-reply" });
          break;
        case "待审商城":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/shop-review" });
          break;
        case "回商城待审":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/shopreply-review" });
          break;
        //商城
        case "商品分类":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/cont-goods" });
          break;
        case "最新商品":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/cont-goodstie" });
          break;
        case "回复商品":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/goodstie-reply" });
          break;
        case "待审商品":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/goodstie-review" });
          break;
        case "回商品待审":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/goodstiereply-review" });
          break;
        // case '搜索':
        //   this.sideSubmenuSelect = title;
        //   this.$router.push({path:'/admin/cont-manage/search'});
        //   break;
        case "主题审核":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/cont-review" });
          break;
        case "回复审核":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/reply-review" });
          break;
        case "最新举报": // 举报管理-最新举报
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/report-manage" });
          break;
        case "已处理记录": // 举报管理-已处理记录
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/report-processed" });
          break;
        case "主题":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/recycle-bin" });
          break;
        case "回复":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/recycle-bin-reply" });
          break;
        case "提现申请":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/withdrawal-application" });
          break;
        case "提现设置":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/withdrawal-setting" });
          break;
        //提问
        case "最新提问":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/cont-ask" });
          break;
        case "提问回复":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/ask-reply" });
          break;
        case "提问审核":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/ask-review" });
          break;
        case "回复提问审核":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/askreply-review" });
          break;
        case "提问分类":
          this.sideSubmenuSelect = title;
          this.$router.push({ path: "/admin/cont-classask" });
          break;
        default:
          this.$message.error("没有当前页面，跳转404页面");
          // this.$router.push({path:'/admin/home'});
          console.log("没有当前页面，跳转404页面");
      }
    },

    /*
     *  配置页面刷新后菜单保持选中
     * */
    setDataStatus() {
      //设置页面刷新前状态，通过路由获取
      let attribution = this.$router.history.current.meta.attribution; //导航名字
      let name = this.$router.history.current.meta.name; //子菜单唯一标识符
      let title = this.$router.history.current.meta.title; //子菜单名字

      switch (attribution) {
        case "首页":
          this.navSelect = this.navList[0].name;
          this.indexTitle = title;
          this.sideTitle = attribution;
          this.sideSelect = name;
          this.sideList = this.navList[0].submenu;
          break;
        case "全局":
          this.navSelect = this.navList[1].name;
          this.indexTitle = title;
          this.sideTitle = attribution;
          this.sideSelect = name;
          this.sideList = this.navList[1].submenu;
          break;
        case "用户":
          this.navSelect = this.navList[2].name;
          this.indexTitle = title;
          this.sideTitle = attribution;
          this.sideSelect = name;
          this.sideList = this.navList[2].submenu;
          break;
        case "内容":
          this.navSelect = this.navList[3].name;
          this.indexTitle = title;
          this.sideTitle = attribution;
          this.sideSelect = name;
          this.sideList = this.navList[3].submenu;
          break;
        case "财务":
          this.navSelect = this.navList[4].name;
          this.indexTitle = title;
          this.sideTitle = attribution;
          this.sideSelect = name;
          this.sideList = this.navList[4].submenu;
          break;
        case "圈子":
          this.navSelect = this.navList[5].name;
          this.indexTitle = title;
          this.sideTitle = attribution;
          this.sideSelect = name;
          this.sideList = this.navList[5].submenu;
          break;
        case "课程":
          this.navSelect = this.navList[6].name;
          this.indexTitle = title;
          this.sideTitle = attribution;
          this.sideSelect = name;
          this.sideList = this.navList[6].submenu;
          break;
        case "商城":
          this.navSelect = this.navList[7].name;
          this.indexTitle = title;
          this.sideTitle = attribution;
          this.sideSelect = name;
          this.sideList = this.navList[7].submenu;
          break;
        default:
          console.log("获取菜单出错");
          this.$message.error("获取菜单出错");
      }

      /*
       * 获取子菜单别名，对应位置导航下子菜单，刷新后设置子菜单选中状态
       * */
      let sideSubmenu = this.$router.history.current.meta.alias;

      if (sideSubmenu) {
        switch (sideSubmenu) {
          case "站点信息":
            this.sideSubmenu = this.navList[1].submenu[0].submenu;
            this.sideSubmenuSelect = this.navList[1].submenu[0].submenu[0].title;
            break;
          case "主题设置":
            this.sideSubmenu = this.navList[1].submenu[0].submenu;
            this.sideSubmenuSelect = this.navList[1].submenu[0].submenu[1].title;
            break;
          case "功能设置":
            this.sideSubmenu = this.navList[1].submenu[0].submenu;
            this.sideSubmenuSelect = this.navList[1].submenu[0].submenu[2].title;
            break;
          case "首页数据设置":
            this.sideSubmenu = this.navList[1].submenu[0].submenu;
            this.sideSubmenuSelect = this.navList[1].submenu[0].submenu[3].title;
            break;
          case "积分设置":
            this.sideSubmenu = this.navList[1].submenu[0].submenu;
            this.sideSubmenuSelect = this.navList[1].submenu[0].submenu[4].title;
            break;
          case "成长值设置":
            this.sideSubmenu = this.navList[1].submenu[0].submenu;
            this.sideSubmenuSelect = this.navList[1].submenu[0].submenu[5].title;
            break;
          case "签到设置":
            this.sideSubmenu = this.navList[1].submenu[0].submenu;
            this.sideSubmenuSelect = this.navList[1].submenu[0].submenu[6].title;
            break;
          case "H5图标设置":
            this.sideSubmenu = this.navList[1].submenu[0].submenu;
            this.sideSubmenuSelect = this.navList[1].submenu[0].submenu[7].title;
            break;
          case "小程序图标设置":
            this.sideSubmenu = this.navList[1].submenu[0].submenu;
            this.sideSubmenuSelect = this.navList[1].submenu[0].submenu[8].title;
            break;
          case "最新主题":
            this.sideSubmenu = this.navList[3].submenu[1].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[1].submenu[0].title;
            break;
          case "最新回复":
            this.sideSubmenu = this.navList[3].submenu[1].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[1].submenu[1].title;
            break;


          // case "搜索":
          //   this.sideSubmenu = this.navList[3].submenu[1].submenu;
          //   this.sideSubmenuSelect = this.navList[3].submenu[1].submenu[1].title;
          //   break;
          case "主题审核":
            this.sideSubmenu = this.navList[3].submenu[2].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[2].submenu[0].title;
            break;
          case "回复审核":
            this.sideSubmenu = this.navList[3].submenu[2].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[2].submenu[1].title;
            break;
          case "最新举报": // 举报管理-最新举报
            this.sideSubmenu = this.navList[3].submenu[4].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[4].submenu[0].title;
            break;
          case "已处理记录": // 举报管理-已处理记录
            this.sideSubmenu = this.navList[3].submenu[4].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[4].submenu[1].title;
            break;
          case "主题":
            this.sideSubmenu = this.navList[3].submenu[5].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[5].submenu[0].title;
            break;
          case "回复":
            this.sideSubmenu = this.navList[3].submenu[5].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[5].submenu[1].title;
            break;
          case "最新提问":
            this.sideSubmenu = this.navList[3].submenu[9].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[9].submenu[0].title;
            break;
          case "提问回复":
            this.sideSubmenu = this.navList[3].submenu[9].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[9].submenu[1].title;
            break;
          case "提问审核":
            this.sideSubmenu = this.navList[3].submenu[9].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[9].submenu[2].title;
            break;
          case "回复提问审核":
            this.sideSubmenu = this.navList[3].submenu[9].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[9].submenu[3].title;
            break;
          case "提问分类":
            this.sideSubmenu = this.navList[3].submenu[9].submenu;
            this.sideSubmenuSelect = this.navList[3].submenu[9].submenu[4].title;
            break;
          case "提现申请":
            this.sideSubmenu = this.navList[4].submenu[2].submenu;
            this.sideSubmenuSelect = this.navList[4].submenu[2].submenu[0].title;
            break;
          case "提现设置":
            this.sideSubmenu = this.navList[4].submenu[2].submenu;
            this.sideSubmenuSelect = this.navList[4].submenu[2].submenu[1].title;
            break;
          default:
          case "最新圈子":
            this.sideSubmenu = this.navList[5].submenu[1].submenu;
            this.sideSubmenuSelect = this.navList[5].submenu[1].submenu[0].title;
            break;
          case "回复圈子":
            this.sideSubmenu = this.navList[5].submenu[1].submenu;
            this.sideSubmenuSelect = this.navList[5].submenu[1].submenu[1].title;
            break;
          case "待审圈子":
            this.sideSubmenu = this.navList[5].submenu[2].submenu;
            this.sideSubmenuSelect = this.navList[5].submenu[2].submenu[0].title;
            break;
          case "回圈待审":
            this.sideSubmenu = this.navList[5].submenu[2].submenu;
            this.sideSubmenuSelect = this.navList[5].submenu[2].submenu[1].title;
            break;
          case "最新圈帖":
            this.sideSubmenu = this.navList[5].submenu[3].submenu;
            this.sideSubmenuSelect = this.navList[5].submenu[3].submenu[0].title;
            break;
          case "回复圈帖":
            this.sideSubmenu = this.navList[5].submenu[3].submenu;
            this.sideSubmenuSelect = this.navList[5].submenu[3].submenu[1].title;
            break;
          case "待审圈帖":
            this.sideSubmenu = this.navList[5].submenu[4].submenu;
            this.sideSubmenuSelect = this.navList[5].submenu[4].submenu[0].title;
            break;
          case "回圈帖待审":
            this.sideSubmenu = this.navList[5].submenu[4].submenu;
            this.sideSubmenuSelect = this.navList[5].submenu[4].submenu[1].title;
            break;
          //课题
          case "最新课题":
            this.sideSubmenu = this.navList[6].submenu[1].submenu;
            this.sideSubmenuSelect = this.navList[6].submenu[1].submenu[0].title;
            break;
          case "回复课题":
            this.sideSubmenu = this.navList[6].submenu[1].submenu;
            this.sideSubmenuSelect = this.navList[6].submenu[1].submenu[1].title;
            break;
          case "待审课题":
            this.sideSubmenu = this.navList[6].submenu[2].submenu;
            this.sideSubmenuSelect = this.navList[6].submenu[2].submenu[0].title;
            break;
          case "回题待审":
            this.sideSubmenu = this.navList[6].submenu[2].submenu;
            this.sideSubmenuSelect = this.navList[6].submenu[2].submenu[1].title;
            break;
          //课帖
          case "最新课帖":
            this.sideSubmenu = this.navList[6].submenu[3].submenu;
            this.sideSubmenuSelect = this.navList[6].submenu[3].submenu[0].title;
            break;
          case "回复课帖":
            this.sideSubmenu = this.navList[6].submenu[3].submenu;
            this.sideSubmenuSelect = this.navList[6].submenu[3].submenu[1].title;
            break;
          case "待审课帖":
            this.sideSubmenu = this.navList[6].submenu[4].submenu;
            this.sideSubmenuSelect = this.navList[6].submenu[4].submenu[0].title;
            break;
          case "回课帖待审":
            this.sideSubmenu = this.navList[6].submenu[4].submenu;
            this.sideSubmenuSelect = this.navList[6].submenu[4].submenu[1].title;
            break;
          //商城
          case "商品分类":
            this.sideSubmenu = this.navList[7].submenu[1].submenu;
            this.sideSubmenuSelect = this.navList[7].submenu[1].submenu[0].title;
            break;
          case "最新商城":
            this.sideSubmenu = this.navList[7].submenu[2].submenu;
            this.sideSubmenuSelect = this.navList[7].submenu[2].submenu[0].title;
            break;
          case "回复商城":
            this.sideSubmenu = this.navList[7].submenu[2].submenu;
            this.sideSubmenuSelect = this.navList[7].submenu[2].submenu[1].title;
            break;
          case "待审商城":
            this.sideSubmenu = this.navList[7].submenu[3].submenu;
            this.sideSubmenuSelect = this.navList[7].submenu[3].submenu[0].title;
            break;
          case "回商城待审":
            this.sideSubmenu = this.navList[7].submenu[3].submenu;
            this.sideSubmenuSelect = this.navList[7].submenu[3].submenu[1].title;
            break;
          //商品
          case "最新商品":
            this.sideSubmenu = this.navList[7].submenu[4].submenu;
            this.sideSubmenuSelect = this.navList[7].submenu[4].submenu[0].title;
            break;
          case "回复商品":
            this.sideSubmenu = this.navList[7].submenu[4].submenu;
            this.sideSubmenuSelect = this.navList[7].submenu[4].submenu[1].title;
            break;
          case "待审商品":
            this.sideSubmenu = this.navList[7].submenu[5].submenu;
            this.sideSubmenuSelect = this.navList[7].submenu[5].submenu[0].title;
            break;
          case "回商品待审":
            this.sideSubmenu = this.navList[7].submenu[5].submenu;
            this.sideSubmenuSelect = this.navList[7].submenu[5].submenu[1].title;
            break;

            // this.$router.push({path:'/admin/home'});
            this.sideSubmenu = [];
            console.log("当下没有侧边栏子菜单");
            this.$message.error("当下没有侧边栏子菜单");
        }
      }
    },

    quitClick() {
      localStorage.clear();
      this.$router.push({ path: "/admin/login" });
    },

    // 判断腾讯云云api是否配置
    checkQcloud() {
      this.appFetch({
        url: "checkQcloud",
        method: "get",
        data: {}
      }).then(data => {
        if (!data.readdata._data.isBuildQcloud) {
          this.dialogVisible = true;
          this.tencentCloudList()//初始化云API配置
        }
      })
      .catch(error => {});
    },

    tencentCloudList(){
      this.appFetch({
        url:'forum',
        method:'get',
        data:{

        }
      }).then(res=>{
        if (res.errors){
          this.$message.error(res.errors[0].code);
        }else {
          this.appId = res.readdata._data.qcloud.qcloud_app_id
          this.secretId = res.readdata._data.qcloud.qcloud_secret_id
          this.secretKey = res.readdata._data.qcloud.qcloud_secret_key
        }
      })
    },
    async  Submission(){
      try{
        await this.appFetch({
        url:'settings',
        method:'post',
        data:{
          "data":[
            {
              "attributes":{
                "key":'qcloud_app_id',
                "value":this.appId,
                "tag": "qcloud"
              }
            },
            {
              "attributes":{
                "key":'qcloud_secret_id',
                "value":this.secretId,
                "tag": "qcloud",
              }
              },
              {
                "attributes":{
                  "key":'qcloud_secret_key',
                  "value":this.secretKey,
                  "tag": "qcloud",
                }
              }

          ]
        }
      }).then(res=>{
        if(res.errors){
          throw new Error(res.errors[0].code);
        }
          this.$message({ message: '提交成功', type: 'success' });
      })
      }
        catch(err){
          this.$message({
            showClose: true,
            message: err
          });
        }
    }
  },
  created() {
    this.setDataStatus();
    this.userName = webDb.getLItem("username");
    this.checkQcloud();
  },
  watch: {
    $route() {
      this.setDataStatus();
    }
  },
  components:{
    Card,
    CardRow
  }
};
