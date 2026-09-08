import Card from "../../../view/site/common/card/card";
import CardRow from "../../../view/site/common/card/cardRow";

export default {
  data: function() {
    return {
      // 关闭站点

      /*closeList: [],
      closeSelectList: [],

      // fileList:[],
      loading: true,
      fullscreenLoading: false,
      siteTheme: 1,

      siteMode: "1", //站点模式选择
      sitePrice: "",
      siteExpire: "",

      siteClose: "1", //关闭站点选择
      // siteLogoFile: {},
      siteLogoFile: [],

      siteCloseMsg: "",
      dialogImageUrl: "",
      dialogVisible: false,
      fileList: [],
      deleBtn: false,
      disabled: true, // 付费模式置灰
      disabledask: true, // 提问模式置灰

      purchaseNum: 0,*/
      imgWeiHangshu: 5,
      weiapp: [],
      numberimgapp: [
        {
          imageUrl: "",
          imgWidht: 0,
          imgHeight: 0,
          text: "图标1",
          textrule: "尺寸：80px*88px"
        },
        {
          imageUrl: "",
          imgWidht: 0,
          imgHeight: 0,
          text: "图标2",
          textrule: "尺寸：80px*88px"
        },
        {
          imageUrl: "",
          imgWidht: 0,
          imgHeight: 0,
          text: "图标3",
          textrule: "尺寸：80px*88px"
        },
        {
          imageUrl: "",
          imgWidht: 0,
          imgHeight: 0,
          text: "图标4",
          textrule: "尺寸：80px*88px"
        },
        {
          imageUrl: "",
          imgWidht: 0,
          imgHeight: 0,
          text: "图标5",
          textrule: "尺寸：80px*88px"
        },
        {
          imageUrl: "",
          imgWidht: 0,
          imgHeight: 0,
          text: "图标6",
          textrule: "尺寸：80px*88px"
        },
        {
          imageUrl: "",
          imgWidht: 0,
          imgHeight: 0,
          text: "图标7",
          textrule: "尺寸：80px*88px"
        },
        {
          imageUrl: "",
          imgWidht: 0,
          imgHeight: 0,
          text: "图标8",
          textrule: "尺寸：80px*88px"
        },
        {
          imageUrl: "",
          imgWidht: 0,
          imgHeight: 0,
          text: "图标9",
          textrule: "尺寸：80px*88px"
        },
        {
          imageUrl: "",
          imgWidht: 0,
          imgHeight: 0,
          text: "图标10",
          textrule: "尺寸：80px*88px"
        }
      ]
    };
  },

  created: function() {
    //初始化请求设置
    this.loadStatus();
  },
  computed: {
    // uploadDisabled:function() {
    //     return this.fileList.length >0
    // },
  },

  methods: {
    loadStatus() {
      //初始化设置
      this.appFetch({
        url: "forum",
        method: "get",
        data: {}
      })
        .then(data => {
          if (data.errors) {
            this.$message.error(data.errors[0].code);
          } else {
            console.log(data)


            // logo size
            // 行数
            this.imgweihangshus = data.readdata._data.set_site.img_wei_hangshu;
            this.weiapp = data.readdata._data.set_site.site_weiapp;
            this.siteTheme = data.readdata._data.set_site.site_skin;
            this.numberimgapp[0].textrule = this.siteTheme === 1
              ? "尺寸：80px*80px"
              : "尺寸：80px*80px";

            this.numberimgapp[0].imageUrl = data.readdata._data.set_site.site_wei_img0;
            this.numberimgapp[1].imageUrl =
              data.readdata._data.set_site.site_wei_img1;
            this.numberimgapp[2].imageUrl =
              data.readdata._data.set_site.site_wei_img2;
            // icon
            this.numberimgapp[3].imageUrl =
              data.readdata._data.set_site.site_wei_img3;
            this.numberimgapp[4].imageUrl =
              data.readdata._data.set_site.site_wei_img4;
            this.numberimgapp[5].imageUrl =
              data.readdata._data.set_site.site_wei_img5;
            this.numberimgapp[6].imageUrl =
              data.readdata._data.set_site.site_wei_img6;
            this.numberimgapp[7].imageUrl =
              data.readdata._data.set_site.site_wei_img7;
            this.numberimgapp[8].imageUrl =
              data.readdata._data.set_site.site_wei_img8;
            this.numberimgapp[9].imageUrl =
              data.readdata._data.set_site.site_wei_img9;

            /*if (this.siteWeixinapp == 1) {
              this.weixinapp = 1;
            } else {
              this.weixinapp = 2;
            }
            if (this.siteWeixinkefu == 1) {
              this.weixinkefu = 1;
            } else {
              this.weixinkefu = 2;
            }
            if (this.siteAsk == 1) {
              this.asks = 1;
            } else {
              this.asks = 2;
            }*/
            if (this.imgweihangshus == 5) {
              this.imgWeiHangshu = 5;
            } else if (this.imgweihangshus == 4) {
              this.imgWeiHangshu = 4;
            } else {
              this.imgWeiHangshu = 1;
            }

            // this.siteLogoFile = data.readdata._data.siteLogoFile;


            // if (data.readdata._data.logo) {
            //   this.fileList.push({url: data.readdata._data.logo});
            // }
            this.getScaleImgSize(this.numberimgapp[0].imageUrl, {
              width: 140,
              height: 140
            }).then(res => {
              this.numberimgapp[0].imgWidht = res.width;
              this.numberimgapp[0].imgHeight = res.height;
            });
            this.getScaleImgSize(this.numberimgapp[1].imageUrl, {
              width: 140,
              height: 140
            }).then(res => {
              this.numberimgapp[1].imgWidht = res.width;
              this.numberimgapp[1].imgHeight = res.height;
            });
            this.getScaleImgSize(this.numberimgapp[2].imageUrl, {
              width: 140,
              height: 140
            }).then(res => {
              this.numberimgapp[2].imgWidht = res.width;
              this.numberimgapp[2].imgHeight = res.height;
            });
            this.getScaleImgSize(this.numberimgapp[3].imageUrl, {
              width: 140,
              height: 140
            }).then(res => {
              this.numberimgapp[3].imgWidht = res.width;
              this.numberimgapp[3].imgHeight = res.height;
            });
            this.getScaleImgSize(this.numberimgapp[4].imageUrl, {
              width: 140,
              height: 140
            }).then(res => {
              this.numberimgapp[4].imgWidht = res.width;
              this.numberimgapp[4].imgHeight = res.height;
            });
            this.getScaleImgSize(this.numberimgapp[5].imageUrl, {
              width: 140,
              height: 140
            }).then(res => {
              this.numberimgapp[5].imgWidht = res.width;
              this.numberimgapp[5].imgHeight = res.height;
            });
            this.getScaleImgSize(this.numberimgapp[6].imageUrl, {
              width: 140,
              height: 140
            }).then(res => {
              this.numberimgapp[6].imgWidht = res.width;
              this.numberimgapp[6].imgHeight = res.height;
            });
            this.getScaleImgSize(this.numberimgapp[7].imageUrl, {
              width: 140,
              height: 140
            }).then(res => {
              this.numberimgapp[7].imgWidht = res.width;
              this.numberimgapp[7].imgHeight = res.height;
            });
            this.getScaleImgSize(this.numberimgapp[8].imageUrl, {
              width: 140,
              height: 140
            }).then(res => {
              this.numberimgapp[8].imgWidht = res.width;
              this.numberimgapp[8].imgHeight = res.height;
            });
            this.getScaleImgSize(this.numberimgapp[8].imageUrl, {
              width: 140,
              height: 140
            }).then(res => {
              this.numberimgapp[9].imgWidht = res.width;
              this.numberimgapp[9].imgHeight = res.height;
            });
          }
        })
        .catch(error => {});
    },
    //删除已上传logo
    deleteImage(file, index, fileList) {
      let type = "";
      switch (index) {
        case 0:
          type = "imgweia";
          break;
        case 1:
          type = "imgweib";
          break;
        case 2:
          type = "imgweic";
          break;
        case 3:
          type = "imgweid";
          break;
        case 4:
          type = "imgweie";
          break;
        case 5:
          type = "imgweif";
          break;
        case 6:
          type = "imgweig";
          break;
        case 7:
          type = "imgweih";
          break;
        case 8:
          type = "imgweii";
          break;
        case 9:
          type = "imgweij";
          break;
        default:
          this.$message.error("未知类型");
      }
      this.numberimgapp[index].imageUrl = "";
      this.appFetch({
        url: "logo",
        method: "delete",
        data: {
          type: type
        }
      })
        .then(data => {
          if (data.errors) {
            this.$message.error(data.errors[0].code);
          } else {
            this.$message("删除成功");
          }
        })
        .catch(error => {});
    },
    handlePictureCardPreview(file) {
      this.dialogImageUrl = file.url;
      this.dialogVisible = true;
    },

    radioChangeClose(closeVal) {
      if (closeVal == "1") {
        this.siteClose = true;
      } else {
        this.siteClose = false;
      }
    },



    handleAvatarSuccess(res, file) {
      // this.imageUrl = URL.createObjectURL(file.raw);
    },
    handleFile() {},
    getScaleImgSize(url, obj) {
      if (url === "") {
        return;
      }
      //处理等比例上传图片，
      return new Promise((resolve, reject) => {
        this.getImageSize(url)
          .then(res => {
            const scale = res.height / res.width;
            if (scale > obj.height / obj.width) {
              resolve({
                width: obj.height / scale,
                height: obj.height
              });
            } else {
              resolve({
                width: obj.width,
                height: obj.width * scale
              });
            }
          })
          .catch(err => {
            console.log(err);
            // reject(err);
          });
      });
    },
    getImageSize(url) {
      const img = document.createElement("img");
      return new Promise((resolve, reject) => {
        img.onload = ev => {
          resolve({ width: img.naturalWidth, height: img.naturalHeight });
        };
        img.src = url;
        img.onerror = reject;
        console.log(url);
      });
    },

    //上传时，判断文件的类型及大小是否符合规则
    beforeAvatarUpload(file) {
      const isJPG =
        file.type == "image/jpeg" ||
        file.type == "image/png" ||
        file.type == "image/gif" ||
        file.type == "image/ico"  ||
        file.type == "image/vnd.microsoft.icon" ||
        file.type == "image/x-icon";
      const isLt2M = file.size / 1024 / 1024 < 2;
      if (!isJPG) {
        this.$message.warning("上传头像图片只能是 JPG/PNG/GIF/ICO 格式!");
        return isJPG;
      }
      if (!isLt2M) {
        this.$message.warning("上传头像图片大小不能超过 2MB!");
        return isLt2M;
      }
      return isJPG && isLt2M;
    },
    // 上传图片
    uploaderLogo(e, index) {
      let type = "";
      switch (index) {
        case 0:
          type = "imgweia";
          break;
        case 1:
          type = "imgweib";
          break;
        case 2:
          type = "imgweic";
          break;
        case 3:
          type = "imgweid";
          break;
        case 4:
          type = "imgweie";
          break;
        case 5:
          type = "imgweif";
          break;
        case 6:
          type = "imgweig";
          break;
        case 7:
          type = "imgweih";
          break;
        case 8:
          type = "imgweii";
          break;
        case 9:
          type = "imgweij";
          break;
        default:
          this.$message.error("未知类型");
      }
      let logoFormData = new FormData();
      logoFormData.append("logo", e.file);
      logoFormData.append("type", type);
      this.appFetch({
        url: "logo",
        method: "post",
        data: logoFormData
      })
        .then(data => {
          if (data.errors) {
            this.$message.error(data.errors[0].code);
          } else {
            this.numberimgapp[index].imageUrl = data.readdata._data.default.logo;
            this.getScaleImgSize(this.numberimgapp[index].imageUrl, {
              width: 140,
              height: 140
            }).then(res => {
              this.numberimgapp[index].imgWidht = res.width;
              this.numberimgapp[index].imgHeight = res.height;
            });
            this.numberimgapptwo[index].imageUrl = data.readdata._data.default.logo;
            this.getScaleImgSize(this.numberimgapptwo[index].imageUrl, {
              width: 140,
              height: 140
            }).then(res => {
              this.numberimgapptwo[index].imgWidht = res.width;
              this.numberimgapptwo[index].imgHeight = res.height;
            });
            this.$message({ message: "上传成功", type: "success" });
          }
        })
        .catch(error => {});
    },
    siteSetPost() {

      this.appFetch({
        url: "settings",
        method: "post",
        data: {
          data: [

            // {
            //   attributes: {
            //     key: "site_close",
            //     value: this.siteClose,
            //     tag: "default"
            //   }
            // },
            {
              attributes: {
                key: "img_wei_hangshu",
                value: this.imgWeiHangshu,
                tag: "default"
              }
            },
            {
              attributes: {
                key: "site_weiapp",
                value: this.weiapp,
                tag: "default"
              }
            }
          ]
        }
      })
        .then(data => {
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
              message: "提交成功",
              type: "success"
            });
          }
        })
        .catch(error => {});
    }
  },
  onblurFun() {
    if (this.siteAuthorScale == null || this.siteAuthorScale == "") {
      this.siteAuthorScale = 0;
    }
    if (this.siteMasterScale == null || this.siteMasterScale == "") {
      this.siteMasterScale = 0;
    }
  },
  components: {
    Card,
    CardRow
  }
};
