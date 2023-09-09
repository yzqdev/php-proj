import buildFullPath from 'axios/lib/core/buildFullPath';
import buildUrl from 'axios/lib/helpers/buildURL';
import { METHODS, STATUS_TEXT_MAP } from '../const';
import { isString } from '../../utils/type';

/**
 * 获取请求的链接：使用 axios 提供的方法拼接请求 URL
 * @param {string} baseURL 基础 url
 * @param {*} url 请求的 url 链接
 * @param {*} params 请求的参数
 * @param {*} paramsSerializer 用于序列化参数的方法
 * @returns {string}
 */
// eslint-disable-next-line arrow-body-style
export const getRequestUrl = (baseURL, url, params, paramsSerializer) => {
  return buildUrl(buildFullPath(baseURL, url), params, paramsSerializer);
};

/**
 * 获取请求的方法
 * @param {string} method 请求的方法
 * @returns {string}
 */
export const getRequestMethod = method => (isString(method) ? method : METHODS.GET).toUpperCase();

/**
 * 获取请求的数据
 * @param {string} method 请求的方法
 * @param {object|string} data 请求的数据
 * @returns {object|string}
 */
export const getRequestData = (method, data) => {
  if (method === METHODS.POST || method === METHODS.PUT || method === METHODS.PATCH) return data;
  return '';
};

/**
 * 处理taro或uniapp返回的响应数据，抹平和axios的差异
 * @param {object} miniResponse taro | uniapp 的响应内容
 * @param {object} config axios 处理过的请求配置对象
 * @param {object} miniRequest taro | uniapp 的调用发起请求时传递的请求配置
 * @returns {object} 响应内容
 */
export const transformResponse = (miniResponse, config, miniRequest) => {
  const headers = miniResponse.header;
  const status = miniResponse.statusCode;
  const statusText = STATUS_TEXT_MAP[status] || '';
  const response = {
    data: miniResponse.data,
    status,
    statusText,
    headers,
    config,
    request: miniRequest,
  };
  return response;
};

/**
 * 处理taro和uniapp请求的参数，统一和axios对齐
 * @param {object} config axios 处理过的请求配置对象
 * @returns {object} 请求配置
 */
export const transfromRequest = (config) => {
  const { baseURL, url, params, paramsSerializer, method, data } = config;
  const requestUrl = getRequestUrl(baseURL, url, params, paramsSerializer);
  const requestMethod = getRequestMethod(method);
  const requestData = getRequestData(method, data);
  const requestConfig = {
    url: requestUrl,
    method: requestMethod,
    header: config.headers,
    data: requestData,
    // 响应的数据类型："text" | "arraybuffer"
    responseType: config.responseType === 'arraybuffer' ? 'arraybuffer' : 'text',
    // 返回数据格式："json" | "其他"
    dataType: config.responseType === 'json' ? 'json' : config.responseType,
  };
  return requestConfig;
};
