<template>
  <div class="home-box">

<!--    <Card class="home-card-box" id="other_content_header" intercept="true" style="border:0;padding:0;">-->
<!--    </Card>-->

    <Card class="home-card-box" header="待处理事项" intercept="true">
      <p class="pending-p">
        等待审核的主题数
        <router-link
          to="/admin/cont-review"
          style="color: #336699;"
        >(&nbsp;{{siteInfo.unapprovedThreads}}&nbsp;)</router-link>
      </p>
      <p class="pending-p">
        等待审核的回复数
        <router-link
          to="/admin/reply-review"
          style="color: #336699;"
        >(&nbsp;{{siteInfo.unapprovedPosts}}&nbsp;)</router-link>
      </p>
      <p class="pending-p">
        等待审核的提现数
        <router-link
          to="/admin/withdrawal-application"
          style="color: #336699;"
        >(&nbsp;{{siteInfo.unapprovedMoneys}}&nbsp;)</router-link>
      </p>
      <p class="pending-p">
        等待审核的用户数
        <router-link
          to="/admin/user-review"
          style="color: #336699;"
        >(&nbsp;{{siteInfo.unapprovedUsers}}&nbsp;)</router-link>
      </p>
    </Card>

    <Card class="home-card-box" header="系统信息" intercept="true">
      <p class="section">
        <span class="section-title">Discuz!Q<br> (知识付费社区)系统<br>（UniApp）版本：</span>
        <span>{{siteInfo.version}}</span>
        <span v-if="newVersion" class="section-title section-title-right">
          <span>[</span>
          <a href="https://discuzq.cc/category/10?search_ids=10" target="_blank">新版本： {{versionNumber}}</a>
          <span>]</span>
        </span>
        <span class="section-title section-title-right">
          <a href="https://discuzq.cc/" target="_blank">官方社区：discuzq.cc</a>
        </span>
      </p>
      <p class="section">
        <span class="section-title">服务器系统及PHP：</span>
        <span>{{siteInfo.php_version}}</span>
      </p>
      <p class="section">
        <span class="section-title">服务器软件：</span>
        <span>{{siteInfo.server_software}}</span>
      </p>
      <p class="section">
        <span class="section-title">服务器MySQL版本：</span>
        <span>{{siteInfo.db}}</span>
      </p>
      <p class="section">
        <span class="section-title">上传许可：</span>
        <span>{{siteInfo.upload_size}}</span>
      </p>
      <p class="section">
        <span class="section-title">当前数据库尺寸：</span>
        <span>{{siteInfo.db_size}}</span>
      </p>
      <p class="section">
        <span class="section-title">开源贡献者：</span>
        <span><a href="https://discuzq.cc/user/1" target="_blank">盛想团队：张安冠</a></span>
      </p>
    </Card>

    <Card class="home-card-box home-card__footer" id="other_content_footer" header="相关链接">
      <a href="https://discuzq.cc/category/5?search_ids=5&search_ids=26&search_ids=27&search_ids=28&search_ids=29&search_ids=30&search_ids=31&search_ids=32&search_ids=33&search_ids=34&search_ids=35&search_ids=36&search_ids=37&search_ids=38&search_ids=39&search_ids=40&search_ids=41&search_ids=42&search_ids=43&search_ids=44&search_ids=45&search_ids=46&search_ids=47&search_ids=48" target="_blank">使用手册</a>
      <a href="https://www.discuz.com" target="_blank">Discuz! Q 官方</a>
      <a href="https://discuzq.cc" target="_blank">支持论坛</a>
      <a href="https://cloud.tencent.com/" target="_blank">腾讯云</a>
      <a href="http://www.dnspod.cn/" target="_blank">DNSPod</a>
      <a href="https://da.do/hmos" target="_blank">免费SSL证书</a>
    </Card>
  </div>
</template>

<script>
import "../../../scss/site/module/globalStyle.scss";
import homeCon from "../../../controllers/site/home/homeCon";
export default {
  name: "home-view",
  ...homeCon,
};
</script>
