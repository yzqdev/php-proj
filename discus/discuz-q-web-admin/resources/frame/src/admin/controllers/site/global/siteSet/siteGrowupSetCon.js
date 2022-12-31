import Card from "../../../../view/site/common/card/card";
import CardRow from "../../../../view/site/common/card/cardRow";

export default {
  data(){
    return {
      growups:[], // 成长身份
      growup:[] // 成长值
    }
  },
  methods:{
    // 加载功能权限
    loadGrowupStatus() {
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
            // 成长值
            this.growup = data.readdata._data.set_site.site_growup;
            // 成长身份
            this.growups = data.readdata._data.set_site.site_growups;
          }
        })
        .catch(error => {});
    },
    // 提交功能状态更改
    handlePublishingSubmit(){
      this.appFetch({
        url: "settings",
        method: "post",
        data: {
          data: [
            {
              attributes: {
                key: "site_growup",
                value: this.growup,
                tag: "default"
              }
            },
            {
              attributes: {
                key: "site_growups",
                value: this.growups,
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
    },
  },
  created(){
    this.loadGrowupStatus()
  },
  components:{
    Card,
    CardRow
  }
}
