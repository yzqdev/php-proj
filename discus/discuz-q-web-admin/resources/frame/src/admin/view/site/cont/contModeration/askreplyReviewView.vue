<template>
    <div class="reply-review-box">
      <div class="cont-review-header">
        <div class="cont-review-header__lf">
          <div >
            <span class="cont-review-header__lf-title">用户名：</span>
            <el-input size="medium" placeholder="搜索用户名" clearable v-model="searchUserName"></el-input>
          </div>
          <div >
            <span  class="cont-review-header__lf-title">每页显示：</span>
            <el-select v-model="pageSelect" size="medium" placeholder="选择每页显示">
              <el-option
                v-for="item in pageOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </div>
        </div>

        <div class="cont-review-header__rt">
          <div>
            <span  class="cont-review-header__lf-title">内容包含：</span>
            <el-input size="medium" class="content-contains-input" clearable placeholder="搜索关键词" v-model="keyWords" ></el-input>
            <el-checkbox v-model="showSensitiveWords">显示敏感词</el-checkbox>
          </div>

          <div class="cont-review-header__rt-search">
            <span  class="cont-review-header__lf-title">搜索范围：</span>
            <el-select v-model="searchReviewSelect" size="medium" placeholder="选择审核状态">
              <el-option
                v-for="item in searchReview"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
            <!-- <el-select v-model="categoriesListSelect" size="medium" clearable placeholder="选择搜索分类">
              <el-option
                v-for="item in categoriesList"
                :key="item.id"
                :label="item.name"
                :value="item.id">
              </el-option>
            </el-select> -->
            <el-cascader
              v-model="categoriesListSelect"
              :options="categoriesList"
              :props="{ expandTrigger: 'hover', checkStrictly: true }"
              @change="handleChange">
            </el-cascader>
            <el-select v-model="searchTimeSelect" @change="searchTimeChange"  size="medium" placeholder="选择搜索时间">
              <el-option
                v-for="item in searchTime"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
            <el-button size="small" @click="postSearch" type="primary">搜索</el-button>
          </div>

        </div>
      </div>

      <div class="cont-review-table">
        <ContArrange
          v-for="(items,index) in  themeList"
          :replyBy="!items.user?'该用户被删除':items.user._data.username"
          :themeName="items.thread._data.isLongArticle?items.thread._data.title:items.thread.firstPost._data.content"
          :titleIcon="titleIcon(items.thread._data)"
          :finalPost="formatDate(items._data.updatedAt)"
          :ip="items._data.ip"
          :userId="!items.user?'该用户被删除':items.user._data.id"
          :key="items._data.id"
        >
          <div class="cont-review-table__side" slot="side">
            <el-radio-group v-model="submitForm[index].radio" @change="radioChange($event,index)">
              <el-radio :label="0">通过</el-radio>
              <el-radio :label="1">删除</el-radio>
              <el-radio :label="2" v-if="items._data.isApproved !== 2" :disabled="items._data.isApproved === 2">忽略</el-radio>
            </el-radio-group>
          </div>

          <!-- <a slot="longText" class="cont-review-table__long-text" v-if="items.thread._data.isLongArticle" :href="'/topic/index?id=' + items._data.id" >
            {{items.thread._data.title}}
            <span  class="iconfont" :class="parseInt(items.thread._data.price) > 0?'iconmoney':'iconchangwen'" ></span>
          </a> -->

          <div class="cont-review-table__main" slot="main">
            <!--<a :href="'/topic/index?id=' + items._data.id" style="color: #333;" target="_blank" v-html="items._data.contentHtml"></a>-->
            <a class="cont-review-table__main__cont-text" :href="'/topic/index?id=' + items.thread._data.id" target="_blank" v-html="items._data.contentHtml"></a>
            <div class="cont-review-table__main__cont-imgs">
              <p class="cont-review-table__main__cont-imgs-p" v-for="(item,index) in items.images" :key="index">
                <img  v-lazy="item._data.thumbUrl" @click="imgShowClick(items.images,index)" :alt="item._data.fileName">
              </p>
            </div>
          </div>

          <div class="cont-review-table__footer" slot="footer">
            <div class="cont-review-table__footer__lf">
              <el-button type="text" @click="singleOperationSubmit(1,items.thread.category._data.id,items._data.id,index)">通过</el-button>
              <i></i>
              <el-button type="text" @click="singleOperationSubmit(2,items.thread.category._data.id,items._data.id,index)">删除</el-button>
              <i></i>
              <el-button type="text" v-if="items._data.isApproved !== 2" @click="singleOperationSubmit(3,items.thread.category._data.id,items._data.id,index)">忽略</el-button>
            </div>

            <div class="cont-review-table__footer__rt">
              <span>操作理由：</span>
              <el-input size="medium" clearable v-model="submitForm[index].attributes.message" ></el-input>
              <el-select size="medium" @change="reasonForOperationChange($event,index)" v-model="submitForm[index].Select" placeholder="选择操作理由">
                <el-option
                  v-for="item in reasonForOperation"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
            </div>

            <div class="cont-review-table__footer__bottom">
              <el-button type="text" @click="viewClick(items.thread._data.id)">查看</el-button>
            </div>

          </div>

        </ContArrange>

        <el-image-viewer
          v-if="showViewer"
          :on-close="closeViewer"
          :url-list="url" />

        <tableNoList v-show="themeList.length < 1"></tableNoList>

        <Page
          v-if="pageCount > 1"
          @current-change="handleCurrentChange"
          :current-page="currentPaga"
          :page-size="pageSelect"
          :total="total">
        </Page>
      </div>

      <div class="cont-review-footer footer-btn">
        <el-button size="small" type="primary" :loading="subLoading" @click="submitClick">提交</el-button>
        <el-button type="text" :loading="btnLoading === 1" @click="allOperationsSubmit(1)" >全部通过</el-button>
        <el-button type="text" :loading="btnLoading === 2" @click="allOperationsSubmit(2)" >全部删除</el-button>
        <el-button type="text" :loading="btnLoading === 3" v-show="ignoreStatus" @click="allOperationsSubmit(3)" >全部忽略</el-button>
        <!-- <el-checkbox v-model="appleAll">将操作应用到其他所有页面</el-checkbox> -->
      </div>

    </div>
</template>

<script>
import '../../../../scss/site/module/contStyle.scss';
import askreplyReviewCon from '../../../../controllers/site/cont/contModeration/askreplyReviewCon'
export default {
    name: "askreply-review-view",
  ...askreplyReviewCon
}
</script>
