import Request from '../request';
import LocalBridge from '../localstorage';
import { validateParameter } from '../request/validator';
import qs from 'qs';

// 兼容旧项目中的一些信息获取
const accessTokenLS = 'access_token';
const localBridgeOptions = { prefix: '' };
const locals = new LocalBridge(localBridgeOptions);

/**
 * 设置请求头
 * @param {axios config} config aixos 返回的配置信息
 */
const setRequestHeader = (config) => {
  // 登录态【以前的旧项目的登录态是都存储在本地缓存中的，只有一个 access_token】
  const token = locals.get(accessTokenLS);
  // eslint-disable-next-line no-param-reassign
  if (token) config.headers.authorization = `Bearer ${token}`;
  return config;
};

/**
 * 响应数据处理
 * @param {axios response} resp 响应返回数据
 */
const operateResponseData = (resp) => {
  const { data, status } = resp;
  return { data, status };
};

/**
 * 请求的 API 基类
 * 目前主要是用于初始化
 */
export default class RequestAPI {
  /**
   * 请求api的构造函数
   * @param {axios config} options axios 的配置
   */
  constructor(options) {
    this.axiosConfig = options;
    this.createHttp();
  }

  /**
   * 初始化默认请求实例
   */
  createHttp() {
    this.http = Request.create({
      isValidate: true,
      withCredentials: true,
      paramsSerializer(params) {
        // https://www.npmjs.com/package/qs
        return qs.stringify(params, { arrayFormat: 'indices', encode: false });
      },
      ...this.axiosConfig,
    });
    this.setHttpRequestInterceptors();
    this.setHttpResponseInterceptors();
  }

  /**
   * 设置默认的请求拦截器
   */
  setHttpRequestInterceptors() {
    // 请求拦截，后进先出
    // 设置请求头
    this.http.interceptors.request.use(setRequestHeader);
    // 校验参数处理
    this.http.interceptors.request.use(validateParameter, null, { synchronous: true });
  }

  /**
   * 设置默认的响应实例
   */
  setHttpResponseInterceptors() {
    // 请求响应处理，可以写多个请求响应的拦截处理
    this.http.interceptors.response.use(operateResponseData);
  }
}
