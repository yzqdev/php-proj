<template>
  <div class="site-function-set-box">
    <!-- 发布功能 -->
    <Card>
      <CardRow class="publishing-title" description="签到">
        签到设置
      </CardRow>
    </Card>

    <el-row :gutter="20">
      <el-col :span="12">
        <CardRow class="publishing-title" >
          签到奖励
        </CardRow>
        <el-radio-group v-model.number="signin && signin[0].ivalue">
          <el-radio :label="1">金币</el-radio>
          <el-radio :label="2">积分</el-radio>
          <el-radio :label="3">金币+积分</el-radio>
          <el-radio :label="4">成长值</el-radio>
          <el-radio :label="5">金币+成长值</el-radio>
          <el-radio :label="6">积分+成长值</el-radio>
        </el-radio-group>

      </el-col>

    </el-row>



    <el-row :gutter="20">

      <el-col :span="6">
        <CardRow class="publishing-title">
          每天签到次数
        </CardRow>
        <el-input
          placeholder="每天签到次数"
          prefix-icon="el-icon-s-data"
          v-model.number="signin[1] && signin[1].second">
        </el-input>
      </el-col>
    </el-row>
    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          X天内可以补签。0补不能补签。
        </CardRow>
        <el-input
          placeholder="补签天数"
          prefix-icon="el-icon-edit"
          v-model.number="signin[1] && signin[1].repair">
        </el-input>
      </el-col>

    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          补签需要消耗。
        </CardRow>
        <el-radio-group v-model.number="signin[2] && signin[2].ivalue">
          <el-radio :label="1">金币</el-radio>
          <el-radio :label="2">积分</el-radio>
          <el-radio :label="3">成长值</el-radio>
        </el-radio-group>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          补签需要消耗多少值
        </CardRow>
        <el-input
          placeholder="补签需要消耗多少值"
          prefix-icon="el-icon-edit"
          v-model.number="signin[2] && signin[2].repair">
        </el-input>
      </el-col>

    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          第一天签到奖励
        </CardRow>
        <el-input
          placeholder="请输入奖励值"
          prefix-icon="el-icon-edit"
          v-model.number="signinday[0] && signinday[0].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          第二天签到奖励
        </CardRow>
        <el-input
          placeholder="请输入奖励值"
          prefix-icon="el-icon-edit"
          v-model.number="signinday[1] && signinday[1].value">
        </el-input>
      </el-col>
    </el-row>
    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          第三天签到奖励
        </CardRow>
        <el-input
          placeholder="请输入奖励值"
          prefix-icon="el-icon-edit"
          v-model.number="signinday[2] && signinday[2].value">
        </el-input>
      </el-col>
    </el-row>
    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          第四天签到奖励
        </CardRow>
        <el-input
          placeholder="请输入奖励值"
          prefix-icon="el-icon-edit"
          v-model.number="signinday[3] && signinday[3].value">
        </el-input>
      </el-col>
    </el-row>
    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          第五天签到奖励
        </CardRow>
        <el-input
          placeholder="请输入奖励值"
          prefix-icon="el-icon-edit"
          v-model.number="signinday[4] && signinday[4].value">
        </el-input>
      </el-col>
    </el-row>
    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          第六天签到奖励
        </CardRow>
        <el-input
          placeholder="请输入奖励值"
          prefix-icon="el-icon-edit"
          v-model.number="signinday[5] && signinday[5].value">
        </el-input>
      </el-col>
    </el-row>
    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          第七天签到奖励
        </CardRow>
        <el-input
          placeholder="请输入奖励值"
          prefix-icon="el-icon-edit"
          v-model.number="signinday[6] && signinday[6].value">
        </el-input>
      </el-col>
    </el-row>
    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          第N天签到奖励
        </CardRow>
        <el-input
          placeholder="请输入奖励值"
          prefix-icon="el-icon-edit"
          v-model.number="signinday[7] && signinday[7].value">
        </el-input>
      </el-col>
    </el-row>






    <el-button type="primary" size="medium" @click="handlePublishingSubmit">提交</el-button>

  </div>
</template>

<script>
  import "../../../../scss/site/module/globalStyle.scss";
  import siteSigninSetCon from "../../../../controllers/site/global/siteSet/siteSigninSetCon";
  export default {
    name: "site-signin-set-view",
    ...siteSigninSetCon
  };
</script>
