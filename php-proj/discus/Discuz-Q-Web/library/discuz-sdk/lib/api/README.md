# API 集合

## 安装
> TODO: 目前此包没有上传到任何的包管理平台，暂时没有安装方式

## 使用

```javascript
// 现在只是一个例子，还有很多需要完善
// 引入想要的 api
import { apiIns } from '@discuz/sdk/lib/api';

// 实例化请求，传的参数是 axios request config 的配置
const api = apiIns({
  baseURL: 'https://www.qq.com',
  ...,
});

const { http } = api;
// 可以另外设置请求拦截
http.interceptors.request.use((res) => console.log(res));
// 可以另外设置响应拦截
http.interceptors.response.use((res) => {
  const { data, status } = res;
  // 可以进行一些针对状态的错误处理 handleError(status)
  return data;
})

// 请求参数
const params = {
  page: 1,
  perPage: 10,
  filter: {
    sticky: 0,
    essence: 1,
  },
};
// 获取文章列表
api.readThreadList({ params })
  .then(result => console.log('read', result));

```

## api 说明

## 规范
> 创建 `api` 文件的时候，可以直接使用项目 `README.md` 中关于 `api` 文件初始化的创建命令进行创建，这样可以直接生成一个文件

1. 目前分为 6 个模块： `内容（content）`，`注册登录（login）`、`用户信息（user）`、`订单支付（pay）`、`通知（notice）`、`其它（other）`

2. 文件名
- 使用 `全小写短横线连接符` 的命名方式
- 动名词的形式：动词为数据库增删改查，这样能够明确 `api` 的含义。即为 `read`（读取数据），`create`（创建数据），`update`（更新数据），`delete`（删除数据）。
- 例如，文件名可为：`read-threaddetail`，`update-theaddetail` 等

3. `api` 方法名
- 使用 `小写开头的驼峰` 的命名方式
- 例如，方法名可为：`readThreadDetail`，`updateTheadDetail` 等

4. 代码规范遵循仓库设置的 `eslint` 规范
