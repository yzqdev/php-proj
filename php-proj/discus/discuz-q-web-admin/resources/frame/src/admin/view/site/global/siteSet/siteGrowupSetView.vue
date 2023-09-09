<template>
  <div class="site-function-set-box">
    <!-- 发布功能 -->
    <Card>
      <CardRow class="publishing-title" description="成长值">
        成长值设置
      </CardRow>
    </Card>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          成长值身份一
        </CardRow>
        <el-input
          placeholder="请输入成长值"
          prefix-icon="el-icon-edit"
          v-model="growups[0] && growups[0].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          需要成长值
        </CardRow>
        <el-input
          placeholder="需要成长值"
          prefix-icon="el-icon-s-data"
          v-model.number="growups[0] && growups[0].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          成长值身份二
        </CardRow>
        <el-input
          placeholder="请输入成长值"
          prefix-icon="el-icon-edit"
          v-model="growups[1] && growups[1].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          需要成长值
        </CardRow>
        <el-input
          placeholder="需要成长值"
          prefix-icon="el-icon-s-data"
          v-model.number="growups[1] && growups[1].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          成长值身份三
        </CardRow>
        <el-input
          placeholder="请输入成长值"
          prefix-icon="el-icon-edit"
          v-model="growups[2] && growups[2].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          需要成长值
        </CardRow>
        <el-input
          placeholder="需要成长值"
          prefix-icon="el-icon-s-data"
          v-model.number="growups[2] && growups[2].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          成长值身份四
        </CardRow>
        <el-input
          placeholder="请输入成长值"
          prefix-icon="el-icon-edit"
          v-model="growups[3] && growups[3].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          需要成长值
        </CardRow>
        <el-input
          placeholder="需要成长值"
          prefix-icon="el-icon-s-data"
          v-model.number="growups[3] && growups[3].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          成长值身份五
        </CardRow>
        <el-input
          placeholder="请输入成长值"
          prefix-icon="el-icon-edit"
          v-model="growups[4] && growups[4].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          需要成长值
        </CardRow>
        <el-input
          placeholder="需要成长值"
          prefix-icon="el-icon-s-data"
          v-model.number="growups[4] && growups[4].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          成长值身份六
        </CardRow>
        <el-input
          placeholder="请输入成长值"
          prefix-icon="el-icon-edit"
          v-model="growups[5] && growups[5].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          需要成长值
        </CardRow>
        <el-input
          placeholder="需要成长值"
          prefix-icon="el-icon-s-data"
          v-model.number="growups[5] && growups[5].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          成长值身份七
        </CardRow>
        <el-input
          placeholder="请输入成长值"
          prefix-icon="el-icon-edit"
          v-model="growups[6] && growups[6].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          需要成长值
        </CardRow>
        <el-input
          placeholder="需要成长值"
          prefix-icon="el-icon-s-data"
          v-model.number="growups[6] && growups[6].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          成长值身份八
        </CardRow>
        <el-input
          placeholder="请输入成长值"
          prefix-icon="el-icon-edit"
          v-model="growups[7] && growups[7].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          需要成长值
        </CardRow>
        <el-input
          placeholder="需要成长值"
          prefix-icon="el-icon-s-data"
          v-model.number="growups[7] && growups[7].value">
        </el-input>
      </el-col>
    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          登录成长值设置
        </CardRow>
        <el-input
          placeholder="请输入成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[0] && growup[0].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="每天奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[0] && growup[0].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          注册成长值
        </CardRow>
        <el-input
          placeholder="请输入成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[1] && growup[1].ivalue">
        </el-input>
      </el-col>

    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          评论成长值
        </CardRow>
        <el-input
          placeholder="请输入成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[2] && growup[2].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[2] && growup[2].value">
        </el-input>
      </el-col>
    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          点赞成长值
        </CardRow>
        <el-input
          placeholder="点赞奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[3] && growup[3].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[3] && growup[3].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          取消点赞扣除成长值
        </CardRow>
        <el-input
          placeholder="取消点赞扣除成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[3] && growup[3].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[3] && growup[3].unvalueday">
        </el-input>
      </el-col>
    </el-row>



    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          收藏成长值
        </CardRow>
        <el-input
          placeholder="收藏奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[4] && growup[4].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[4] && growup[4].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          取消收藏扣除成长值
        </CardRow>
        <el-input
          placeholder="取消收藏扣除成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[4] && growup[4].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[4] && growup[4].unvalueday">
        </el-input>
      </el-col>

    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          关注成长值
        </CardRow>
        <el-input
          placeholder="关注奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[5] && growup[5].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[5] && growup[5].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          取消关注扣除成长值
        </CardRow>
        <el-input
          placeholder="取消关注扣除成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[5] && growup[5].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[5] && growup[5].unvalueday">
        </el-input>
      </el-col>

    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布帖子成长值
        </CardRow>
        <el-input
          placeholder="发布帖子奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[6] && growup[6].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[6] && growup[6].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除帖子扣除成长值
        </CardRow>
        <el-input
          placeholder="删除帖子扣除成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[6] && growup[6].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[6] && growup[6].unvalue">
        </el-input>
      </el-col>

    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布文章成长值
        </CardRow>
        <el-input
          placeholder="发布文章奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[7] && growup[7].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[7] && growup[7].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除文章扣除成长值
        </CardRow>
        <el-input
          placeholder="删除文章扣除成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[7] && growup[7].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[7] && growup[7].unvalueday">
        </el-input>
      </el-col>

    </el-row>



    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布图片奖励成长值
        </CardRow>
        <el-input
          placeholder="发布图片奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[8] && growup[8].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[8] && growup[8].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除图片扣除成长值
        </CardRow>
        <el-input
          placeholder="删除图片扣除成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[8] && growup[8].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[8] && growup[8].unvalueday">
        </el-input>
      </el-col>

    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布视频奖励成长值
        </CardRow>
        <el-input
          placeholder="发布视频奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[9] && growup[9].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[9] && growup[9].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除视频扣除成长值
        </CardRow>
        <el-input
          placeholder="删除视频扣除成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[9] && growup[9].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[9] && growup[9].unvalueday">
        </el-input>
      </el-col>

    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布问答奖励成长值
        </CardRow>
        <el-input
          placeholder="发布问答奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[10] && growup[10].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[10] && growup[10].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除问答扣除成长值
        </CardRow>
        <el-input
          placeholder="删除问答扣除成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[10] && growup[10].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[10] && growup[10].unvalueday">
        </el-input>
      </el-col>

    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          分享奖励成长值
        </CardRow>
        <el-input
          placeholder="分享奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[11] && growup[11].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[11] && growup[11].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">

      <el-col :span="6">
        <CardRow class="publishing-title">
          阅读奖励成长值
        </CardRow>
        <el-input
          placeholder="阅读奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[12] && growup[12].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[12] && growup[12].value">
        </el-input>
      </el-col>
    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          精华奖励成长值
        </CardRow>
        <el-input
          placeholder="精华奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[13] && growup[13].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[13] && growup[13].value">
        </el-input>
      </el-col>
    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          打赏奖励成长值
        </CardRow>
        <el-input
          placeholder="打赏奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[14] && growup[14].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[14] && growup[14].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布圈帖奖励成长值
        </CardRow>
        <el-input
          placeholder="发布圈帖奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[15] && growup[16].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[15] && growup[15].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除圈帖扣除成长值
        </CardRow>
        <el-input
          placeholder="删除圈帖扣除成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[15] && growup[15].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[15] && growup[15].unvalueday">
        </el-input>
      </el-col>

    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布课帖奖励成长值
        </CardRow>
        <el-input
          placeholder="发布课帖奖励成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[16] && growup[16].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[16] && growup[16].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除课帖扣除成长值
        </CardRow>
        <el-input
          placeholder="删除课帖扣除成长值"
          prefix-icon="el-icon-edit"
          v-model.number="growup[16] && growup[16].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="growup[16] && growup[16].unvalueday">
        </el-input>
      </el-col>

    </el-row>



    <el-button type="primary" size="medium" @click="handlePublishingSubmit">提交</el-button>

  </div>
</template>

<script>
  import "../../../../scss/site/module/globalStyle.scss";
  import siteGrowupSetCon from "../../../../controllers/site/global/siteSet/siteGrowupSetCon";
  export default {
    name: "site-growup-set-view",
    ...siteGrowupSetCon
  };
</script>
