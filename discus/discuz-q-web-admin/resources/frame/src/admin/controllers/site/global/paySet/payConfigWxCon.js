
import Card from '../../../../view/site/common/card/card';
import CardRow from '../../../../view/site/common/card/cardRow';

export default {
  data:function () {
    return {
      appId:'',
      mchId:'',
      apiKey:'',
      apiKeyV3:'',
      serialNo:'',
      appSecret:'',
      type:'',
      iOSPay: false,
      value: false,
      value2: false,
    }
  },

  created(){
    var type = this.$route.query.type;
    this.type = type;
    this.loadStatus();
  },
  methods:{
    loadStatus(){
      //初始化
      this.appFetch({
        url:'forum',
        method:'get',
        data:{}
      }).then(data=>{
        if (data.errors){
          this.$message.error(data.errors[0].code);
        }else {
          this.appId = data.readdata._data.paycenter.app_id;
          this.mchId = data.readdata._data.paycenter.mch_id;
          this.apiKey = data.readdata._data.paycenter.api_key;
          this.apiKeyV3 = data.readdata._data.paycenter.api_keyv3;
          this.serialNo = data.readdata._data.paycenter.serial_no;
          this.appSecret = data.readdata._data.paycenter.app_secret;
          this.iOSPay = data.readdata._data.paycenter.wxpay_ios;
          this.value = data.readdata._data.paycenter.wxpay_mchpay_close;
          this.value2 = data.readdata._data.paycenter.wxpay_mchpay2_close;
        }
      }).catch(error=>{
      })
    },
    submitConfiguration(){

      if (this.value && this.value2){
        this.$message.error('提现产品只能选其一');return;
      }

      this.appFetch({
        url:'settings',
        method:'post',
        data:{
          "data":[
            {
               "attributes":{
                "key":"app_id",
                "value":this.appId,
                "tag": this.type
               }
            },
            {
               "attributes":{
                "key":"mch_id",
                "value":this.mchId,
                "tag": this.type
               }
            },
            {
               "attributes":{
                "key":"api_key",
                "value":this.apiKey,
                "tag": this.type
               }
            },
            {
               "attributes":{
                "key":"app_secret",
                "value":this.appSecret,
                "tag": this.type
               }
            },
            {
               "attributes":{
                "key":"wxpay_ios",
                "value":this.iOSPay,
                "tag": this.type
               }
            },
            {
              "attributes":{
               "key":"wxpay_mchpay_close",
               "value":this.value,
               "tag": this.type
              }
           },
            {
              "attributes":{
                "key":"wxpay_mchpay2_close",
                "value":this.value2,
                "tag": this.type
              }
            },
            {
              "attributes":{
                "key":"api_keyv3",
                "value":this.apiKeyV3,
                "tag": this.type
              }
            },
            {
              "attributes":{
                "key":"serial_no",
                "value":this.serialNo,
                "tag": this.type
              }
            },
           ]
        }
      }).then(data=>{
        // this.$router.push({
        //   path:'/admin/pay-set'
        // });
        if (data.errors){
          this.$message.error(data.errors[0].code);
        }else {
          this.$message({
            message: '提交成功',
            type: 'success'
          });
        }
      })
    }
  },
  components:{
    Card,
    CardRow
  }
}
