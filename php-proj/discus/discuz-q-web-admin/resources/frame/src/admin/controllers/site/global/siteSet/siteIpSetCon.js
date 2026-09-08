import Card from "../../../../view/site/common/card/card";
import CardRow from "../../../../view/site/common/card/cardRow";

export default {
  data(){
    return {
      appCode:''
    }
  },

  created() {
    //初始化请求设置
    this.loadIpStatus();
  },

  methods:{
    // 加载功能权限
    loadIpStatus() {
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
            this.appCode = data.readdata._data.set_site.site_appcode;
          }
        })
        .catch(error => {});
    },
    // 提交功能状态更改
    siteIpPost(){
      this.appFetch({
        url: "settings",
        method: "post",
        data: {
          data: [
            {
              attributes: {
                key: "site_appcode",
                value: this.appCode,
                tag: "default"
              }
            },
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
    },
  },
  components: {
    Card,
    CardRow
  }
};
