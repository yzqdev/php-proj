import Card from "../../../view/site/common/card/card";
import TableContAdd from "../../../view/site/common/table/tableContAdd";

export default {
  data: function() {
    return {
      groupsList: [], //分类列表
      browseCategories: true, // 浏览分类
      content: true, // 发表内容
      comment: true, // 发表评论
      categoriesListLength: "", //分类列表长度
      createCategoriesStatus: false, //添加分类状态
      deleteStatus: true,
      multipleSelection: [], //分类多选列表
      visible: false,
      delLoading: false, //删除按钮状态
      subLoading: false //提交按钮状态
    };
  },

  created() {
    this.getGroups();
    this.query = this.$route.query;
  },

  methods: {
    getGroups() {
      this.appFetch({
        url: "groups",
        method: "get",
        data: {
          include: ["permission"]
        }
      })
        .then(res => {
          if (res.errors) {
            this.$message.error(res.errors[0].code);
          } else {
            const categoryId = this.query.id;
            this.groupsList = [];
            res.readdata.forEach((item, index) => {
              // 该用户组的所有权限数组
              const allPermissions = [];
              item.permission.map(value => {
                allPermissions.push(value._data.permission);
              });

              const viewThreads =
                allPermissions.indexOf(`category${categoryId}.viewThreads`) !==
                -1;
              const viewaskThreads =
                allPermissions.indexOf(`category${categoryId}.viewaskThreads`) !==
                -1;
              const createThread =
                allPermissions.indexOf(`category${categoryId}.createThread`) !==
                -1;
              const createaskThread =
                allPermissions.indexOf(`category${categoryId}.createaskThread`) !==
                -1;
              const askThread =
                allPermissions.indexOf(`category${categoryId}.thread.ask`) !==
                -1;
              const replyaskThread =
                allPermissions.indexOf(`category${categoryId}.threadask.reply`) !==
                -1;
              const replyThread =
                allPermissions.indexOf(`category${categoryId}.thread.reply`) !==
                -1;
              const editThread =
                allPermissions.indexOf(`category${categoryId}.thread.edit`) !==
                -1;
              const hideThread =
                allPermissions.indexOf(`category${categoryId}.thread.hide`) !==
                -1;
              const checkAll = viewThreads && viewaskThreads && createThread && createaskThread && askThread && replyThread && replyaskThread && editThread && hideThread;
              const isIndeterminate = !checkAll;

              this.groupsList.push({
                id: item._data.id,
                name: item._data.name,
                viewThreads: viewThreads,
                viewaskThreads: viewaskThreads,
                createThread: createThread,
                createaskThread: createaskThread,
                askThread: askThread,
                replyThread: replyThread,
                replyaskThread: replyaskThread,
                editThread: editThread,
                hideThread: hideThread,
                checkAll: checkAll,
                isIndeterminate: isIndeterminate
              });
            });
          }
        })
        .catch(err => {});
    },
    handleCheckAllChange(scope) {
      const flag = scope.row.checkAll;
      scope.row.viewThreads = flag;
      scope.row.viewaskThreads = flag;
      scope.row.createThread = flag;
      scope.row.createaskThread = flag;
      scope.row.askThread = flag;
      scope.row.replyThread = flag;
      scope.row.replyaskThread = flag;
      scope.row.editThread = flag;
      scope.row.hideThread = flag;
    },
    submitClick() {
      const viewThreads = [];
      const viewaskThreads = [];
      const createThread = [];
      const createaskThread = [];
      const askThread = [];
      const replyThread = [];
      const replyaskThread = [];
      const editThread = [];
      const hideThread = [];
      const categoryId = this.query.id;
      let error = "";
      this.groupsList.map(item => {
        item.viewThreads && viewThreads.push(item.id);
        item.createThread && createThread.push(item.id);
        item.createaskThread && createaskThread.push(item.id);
        item.askThread && askThread.push(item.id);
        item.replyThread && replyThread.push(item.id);
        item.replyaskThread && replyaskThread.push(item.id);
        item.editThread && editThread.push(item.id);
        item.hideThread && hideThread.push(item.id);
      });
      console.log(viewThreads, viewaskThreads, createThread, createaskThread, askThread, replyThread, replyaskThread);
      this.appFetch({
        url: "setPermission",
        method: "post",
        data: {
          permission: `category${categoryId}.viewThreads`,
          groupIds: viewThreads
        }
      }).then(res => {
        if (res.errors) {
          error = res.errors[0].code;
        }
      });
      this.appFetch({
        url: "setPermission",
        method: "post",
        data: {
          permission: `category${categoryId}.viewaskThreads`,
          groupIds: viewaskThreads
        }
      }).then(res => {
        if (res.errors) {
          error = res.errors[0].code;
        }
      });
      this.appFetch({
        url: "setPermission",
        method: "post",
        data: {
          permission: `category${categoryId}.createThread`,
          groupIds: createThread
        }
      }).then(res => {
        if (res.errors) {
          error = res.errors[0].code;
        }
      });
      this.appFetch({
        url: "setPermission",
        method: "post",
        data: {
          permission: `category${categoryId}.createaskThread`,
          groupIds: createaskThread
        }
      }).then(res => {
        if (res.errors) {
          error = res.errors[0].code;
        }
      });
      this.appFetch({
        url: "setPermission",
        method: "post",
        data: {
          permission: `category${categoryId}.thread.ask`,
          groupIds: askThread
        }
      }).then(res => {
        if (res.errors) {
          error = res.errors[0].code;
        }
      });
      this.appFetch({
        url: "setPermission",
        method: "post",
        data: {
          permission: `category${categoryId}.thread.reply`,
          groupIds: replyThread
        }
      }).then(res => {
        if (res.errors) {
          error = res.errors[0].code;
        }
      });
      this.appFetch({
        url: "setPermission",
        method: "post",
        data: {
          permission: `category${categoryId}.threadask.reply`,
          groupIds: replyaskThread
        }
      }).then(res => {
        if (res.errors) {
          error = res.errors[0].code;
        }
      });
      this.appFetch({
        url: "setPermission",
        method: "post",
        data: {
          permission: `category${categoryId}.thread.edit`,
          groupIds: editThread
        }
      }).then(res => {
        if (res.errors) {
          error = res.errors[0].code;
        }
      });
      this.appFetch({
        url: "setPermission",
        method: "post",
        data: {
          permission: `category${categoryId}.thread.hide`,
          groupIds: hideThread
        }
      }).then(res => {
        if (res.errors) {
          error = res.errors[0].code;
        }
      });
      if (error) {
        this.$message.error(error);
      } else {
        this.$message({
          message: "提交成功！",
          type: "success"
        });
      }
    }
  },

  components: {
    Card,
    TableContAdd
  }
};
