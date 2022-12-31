/**
 * 针对给出的请求实例进行本地化配置
 */
import Vue from 'vue';
import { apiIns, RESPONSE_CODE } from '../library/discuz-sdk/lib/api';
import config from '../config';

let baseURL = '';

// SSR 服务端 baseURL 修正处理
if (process.server === true) {
  if (process.env.NODE_ENV === 'production') {
    baseURL = config.SSR_API_URL;
  } else {
    baseURL = `http://127.0.0.1:${process.env.npm_package_config_nuxt_port || 3000}`;
  }
}

const api = apiIns({
  baseURL
});

const { http } = api;

// 搬运一下老逻辑
const app = Vue.prototype;

// 响应结果进行设置
http.interceptors.response.use(res => {
  const { data } = res;
  const { Code, Message } = data || {};
  console.log(Message);
  switch (Number(Code)) {
    case RESPONSE_CODE.SUCCESS:
      break;
    default:
      app.$message.warning(Message);
  }

  return data;
});

/**
 * 2021-03-09：目前有 5 个新的接口需要改造更换。请看 library/discuz-sdk/lib/api/content 下面对应的文件，里面有具体的入参说明
 * 1. 获取板块分类: api.readCategories
 * 2. 获取文章列表: api.readThreadList
 * 3. 获取置顶列表：api.readStickList
 * 4. 获取文章详情：api.readThreadDetail
 * 5. 获取评论列表：api.readCommentList
 */
export default api;
