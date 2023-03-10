import axios from 'axios';
import Qs from 'qs';
import config from '../config';

let baseURL = '/api';

// SSR 服务端 baseURL 修正处理
if (process.server === true) {
  if (process.env.NODE_ENV === 'production') {
    baseURL = `${config.SSR_API_URL}${baseURL}`;
  } else {
    baseURL = `http://127.0.0.1:${process.env.npm_package_config_nuxt_port || 3000}${baseURL}`;
  }
}

// 创建 Axios 实例
const service = axios.create({
  baseURL,
  // timeout: 60000,  // 请求超时时间，Respone 拦截器要做好提示处理
  paramsSerializer: params =>
    Qs.stringify(params, {
      arrayFormat: 'repeat'
    })
});

// Request 拦截器
service.interceptors.request.use(
  oConfig => {
    oConfig.headers['Accept'] = 'application/vnd.api+json';

    if (process.client && localStorage.getItem('access_token')) {
      oConfig.headers['authorization'] = `Bearer ${localStorage.getItem('access_token')}`;
    }

    // 由于腾讯的网络环境不能发PATCH请求，所有的PATCH请求要改一下，改成用POST，然后在header里加 x-http-method-override: patch
    if (oConfig.method === 'patch') {
      oConfig.method = 'post';
      oConfig.headers['x-http-method-override'] = 'patch';
    }

    return oConfig;
  },
  oError => {
    return Promise.reject(oError);
  }
);
// Respone 拦截器
service.interceptors.response.use(
  oRes => {
    // console.log(new Date(oRes.headers.date), '正确事件')
    if (process.client) window.currentTime = new Date(oRes.headers.date);
    return oRes;
  },
  oError => {
    if (oError.response && oError.response.data && oError.response.data.errors) {
      oError.response.data.errors.forEach(error => {
        switch (error.code) {
          case 'access_denied':
            // token 无效 重新请求
            if (process.client) {
              localStorage.removeItem('access_token');
              localStorage.removeItem('user_id');
              window.location.reload();
            }
            // delete response.config.header.Authorization;
            break;
          case 'model_not_found':
            console.log('模型未找到');
            break;
          case 'permission_denied':
            console.log('没有查看权限');
            // TODO 这块需要根据后端状态码进行细分无权判断
            // window.location.href = '/tips/';
            break;
          case 'site_closed':
            console.log('site_closed');
            break;
          case 'not_install':
            window.location.href = '/install';
            break;
          case 'ban_user':
            break;
          default:
          // if (response.config.custom.showTost) {
          //   clearTimeout(tostTimeout);
          //   tostTimeout = setTimeout(() => {
          //     // eslint-disable-next-line no-nested-ternary
          //     const title = error.detail
          //       ? Array.isArray(error.detail)
          //         ? error.detail[0]
          //         : error.detail
          //       : i18n.t(`core.${error.code}`);
          //     uni.showToast({
          //       icon: 'none',
          //       title,
          //     });
          //   });
          // }
        }
      });
    }
    return Promise.reject(oError);
  }
);

export default service;
