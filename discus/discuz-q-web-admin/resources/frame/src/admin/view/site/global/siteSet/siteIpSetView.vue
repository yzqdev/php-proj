<template>
  <div class="site-set-box">
    <Card>
      <a href="https://market.aliyun.com/products/57002003/cmapi021970.html" target="_blank">点击申请接口</a>
    </Card>

    <Card header="appcode：">
      <CardRow description="API的appcode">
        <el-input placeholder="appcode" v-model="appCode"></el-input>
      </CardRow>
    </Card>

    <Card class="footer-btn">
      <el-button type="primary" size="medium" @click="siteIpPost">提交</el-button>
    </Card>

  </div>
</template>

<script>
import "../../../../scss/site/module/globalStyle.scss";
import siteIPCon from "../../../../controllers/site/global/siteSet/siteIpSetCon";

export default {
  name: "site-ip-set-view",
  ...siteIPCon
};

</script>

<style scoped>

</style>
