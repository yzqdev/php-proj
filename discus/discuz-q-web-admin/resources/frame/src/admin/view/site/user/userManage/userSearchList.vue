<template>
    <div class="user-search-list-box">
      <Card header="用户搜索结果："></Card>

      <div class="user-search-list__table">

        <div class="user-search-list__table-title">
          <p>共搜索到 {{total}} 名符合条件的用户</p>
          <el-button type="text" @click="$router.push({path: '/admin/user-manage'})">重新搜索</el-button>
          <el-button type="text" @click="exporUserInfo">导出用户信息</el-button>
        </div>

        <div class="user-search-list__table-cont">
          <el-table
            :data="tableData"
            style="width: 100%"
            @selection-change="handleSelectionChange">
            <el-table-column
              type="selection"
              width="55">
            </el-table-column>

            <el-table-column prop="_data.id" label="编号" width="80"> </el-table-column>
            <el-table-column label="头像" width="60" align="center">
              <template slot-scope="scope" class="image-slot">
                <el-image style="width: 32px; height: 32px" :src="scope.row._data.avatarUrl ? scope.row._data.avatarUrl : '/static-admin/images/noavatar.gif'" lazy></el-image>
              </template>
            </el-table-column>

            <el-table-column
              prop="_data.username"
              label="用户名"
              min-width="50"
              >
            </el-table-column>

            <el-table-column
              prop="_data.threadCount"
              label="主题数">
            </el-table-column>

            <el-table-column
              prop="_data.walletBalance"
              label="钱包">
            </el-table-column>

            <el-table-column
              prop="_data.integralCount"
              label="积分">
            </el-table-column>

            <el-table-column
              prop="_data.growupCount"
              label="成长值">
            </el-table-column>

            <el-table-column
              prop="groups[0]._data.name"
              label="用户组">
            </el-table-column>

            <el-table-column label="是否已付费">
              <template slot-scope="scope">
                <el-tag :type="$dayjs().isSameOrBefore($dayjs(scope.row._data.expiredAt)) ? 'success' : 'info'">{{ $dayjs().isSameOrBefore($dayjs(scope.row._data.expiredAt)) ? "是" : "否" }}</el-tag>
              </template>

            </el-table-column>

            <el-table-column
              prop="_data.expiredAt"
              label="到期时间">
            </el-table-column>

            <el-table-column
              label="">
              <template slot-scope="scope">
                <el-button type="text" @click="$router.push({path:'/admin/user-details', query: {id: scope.row._data.id}})">详情</el-button>
                <el-button type="text" @click="$router.push({path:'/admin/wallet', query: {id: scope.row._data.id}})">钱包</el-button>
                <el-button type="text" @click="handleDisable(scope)" :disabled="scope.row._data.status === 1">禁用</el-button>
              </template>
            </el-table-column>

          </el-table>
          <Page :total="total" :pageSize="pageLimit" :currentPage="pageNum" @current-change="handleCurrentChange" />
        </div>

        <Card class="footer-btn">
          <!-- <el-button type="primary" size="medium" :disabled="deleteStatus" @click="deleteBatch">删除</el-button> -->
          <el-button size="medium" :disabled="deleteStatus" @click="disabledBatch">禁用</el-button>
        </Card>

      </div>

      <div class="user-search-list__prompt">
        <h1>提示：</h1>
        <p>导出用户信息最多支持 10000 条数据。导出的 .xlsx 文件可用 EXCEL 打开。</p>
      </div>

    </div>
</template>

<script>
import '../../../../scss/site/module/userStyle.scss';
import userSearchListCon from '../../../../controllers/site/user/userMange/userSearchListCon'
export default {
    name: "search-results-view",
  ...userSearchListCon
}
</script>
