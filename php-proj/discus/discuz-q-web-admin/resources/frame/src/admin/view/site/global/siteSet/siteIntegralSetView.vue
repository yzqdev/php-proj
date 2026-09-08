<template>
  <div class="site-function-set-box">
    <!-- 发布功能 -->
    <Card>
      <CardRow class="publishing-title" description="积分">
        积分设置
      </CardRow>
    </Card>



    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          登录积分设置
        </CardRow>
        <el-input
          placeholder="请输入积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[0] && integral[0].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="每天奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[0] && integral[0].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          注册积分
        </CardRow>
        <el-input
          placeholder="请输入积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[1] && integral[1].ivalue">
        </el-input>
      </el-col>

    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          评论积分
        </CardRow>
        <el-input
          placeholder="请输入积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[2] && integral[2].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[2] && integral[2].value">
        </el-input>
      </el-col>
    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          点赞积分
        </CardRow>
        <el-input
          placeholder="点赞奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[3] && integral[3].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[3] && integral[3].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          取消点赞扣除积分
        </CardRow>
        <el-input
          placeholder="取消点赞扣除积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[3] && integral[3].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[3] && integral[3].unvalueday">
        </el-input>
      </el-col>
    </el-row>



    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          收藏积分
        </CardRow>
        <el-input
          placeholder="收藏奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[4] && integral[4].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[4] && integral[4].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          取消收藏扣除积分
        </CardRow>
        <el-input
          placeholder="取消收藏扣除积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[4] && integral[4].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[4] && integral[4].unvalueday">
        </el-input>
      </el-col>

    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          关注积分
        </CardRow>
        <el-input
          placeholder="关注奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[5] && integral[5].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[5] && integral[5].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          取消关注扣除积分
        </CardRow>
        <el-input
          placeholder="取消关注扣除积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[5] && integral[5].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[5] && integral[5].unvalueday">
        </el-input>
      </el-col>

    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布帖子积分
        </CardRow>
        <el-input
          placeholder="发布帖子奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[6] && integral[6].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[6] && integral[6].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除帖子扣除积分
        </CardRow>
        <el-input
          placeholder="删除帖子扣除积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[6] && integral[6].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[6] && integral[6].unvalue">
        </el-input>
      </el-col>

    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布文章积分
        </CardRow>
        <el-input
          placeholder="发布文章奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[7] && integral[7].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[7] && integral[7].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除文章扣除积分
        </CardRow>
        <el-input
          placeholder="删除文章扣除积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[7] && integral[7].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[7] && integral[7].unvalueday">
        </el-input>
      </el-col>

    </el-row>



    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布图片奖励积分
        </CardRow>
        <el-input
          placeholder="发布图片奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[8] && integral[8].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[8] && integral[8].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除图片扣除积分
        </CardRow>
        <el-input
          placeholder="删除图片扣除积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[8] && integral[8].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[8] && integral[8].unvalueday">
        </el-input>
      </el-col>

    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布视频奖励积分
        </CardRow>
        <el-input
          placeholder="发布视频奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[9] && integral[9].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[9] && integral[9].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除视频扣除积分
        </CardRow>
        <el-input
          placeholder="删除视频扣除积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[9] && integral[9].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[9] && integral[9].unvalueday">
        </el-input>
      </el-col>

    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布问答奖励积分
        </CardRow>
        <el-input
          placeholder="发布问答奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[10] && integral[10].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[10] && integral[10].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除问答扣除积分
        </CardRow>
        <el-input
          placeholder="删除问答扣除积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[10] && integral[10].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[10] && integral[10].unvalueday">
        </el-input>
      </el-col>

    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          分享奖励积分
        </CardRow>
        <el-input
          placeholder="分享奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[11] && integral[11].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[11] && integral[11].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">

      <el-col :span="6">
        <CardRow class="publishing-title">
          阅读奖励积分
        </CardRow>
        <el-input
          placeholder="阅读奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[12] && integral[12].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[12] && integral[12].value">
        </el-input>
      </el-col>
    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          精华奖励积分
        </CardRow>
        <el-input
          placeholder="精华奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[13] && integral[13].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[13] && integral[13].value">
        </el-input>
      </el-col>
    </el-row>


    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          打赏奖励积分
        </CardRow>
        <el-input
          placeholder="打赏奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[14] && integral[14].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[14] && integral[14].value">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布圈帖奖励积分
        </CardRow>
        <el-input
          placeholder="发布圈帖奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[15] && integral[15].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[15] && integral[15].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除圈帖扣除积分
        </CardRow>
        <el-input
          placeholder="删除圈帖扣除积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[15] && integral[15].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[15] && integral[15].unvalueday">
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="6">
        <CardRow class="publishing-title">
          发布课帖奖励积分
        </CardRow>
        <el-input
          placeholder="发布课帖奖励积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[16] && integral[16].ivalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天奖励次数，0为不限制
        </CardRow>
        <el-input
          placeholder="奖励次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[16] && integral[16].value">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          删除课帖扣除积分
        </CardRow>
        <el-input
          placeholder="删除课帖扣除积分"
          prefix-icon="el-icon-edit"
          v-model.number="integral[16] && integral[16].unvalue">
        </el-input>
      </el-col>
      <el-col :span="6">
        <CardRow class="publishing-title">
          每天扣除次数，0为不限制
        </CardRow>
        <el-input
          placeholder="扣除次数"
          prefix-icon="el-icon-s-data"
          v-model.number="integral[16] && integral[16].unvalueday">
        </el-input>
      </el-col>
    </el-row>



    <el-button type="primary" size="medium" @click="handlePublishingSubmit">提交</el-button>

  </div>
</template>

<script>
  import "../../../../scss/site/module/globalStyle.scss";
  import siteIntegralSetCon from "../../../../controllers/site/global/siteSet/siteIntegralSetCon";
  export default {
    name: "site-Integral-set-view",
    ...siteIntegralSetCon
  };
</script>
