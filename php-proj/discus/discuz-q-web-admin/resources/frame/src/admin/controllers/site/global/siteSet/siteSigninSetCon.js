import Card from "../../../../view/site/common/card/card";
import CardRow from "../../../../view/site/common/card/cardRow";

export default {
  data(){
    return {
      signin:[], // 签到
      signinday:[] // 签到天数奖励值
    }
  },
  methods:{
    // 加载功能权限
    loadSigninStatus() {
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
            // 签到
            this.signin = data.readdata._data.set_site.site_signin;
            // 签到值
            this.signinday = data.readdata._data.set_site.site_signinday;
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
                key: "site_signinday",
                value: this.signinday,
                tag: "default"
              }
            },
            {
              attributes: {
                key: "site_signin",
                value: this.signin,
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
    this.loadSigninStatus()
  },
  components:{
    Card,
    CardRow
  }
}
