/**
 * 处理 taro 框架的请求
 */
import settle from 'axios/lib/core/settle';
import createError from 'axios/lib/core/createError';
import Taro from '@tarojs/taro';
import { isObject, isNull } from '../../utils/type';
import { transfromRequest, transformResponse } from './common';

export const taroAdapter = config => new Promise((resolve, reject) => {
  const requestConfig = transfromRequest(config);
  // http://taro-docs.jd.com/taro/docs/apis/network/request/request
  // http://taro-docs.jd.com/taro/docs/apis/network/request/RequestTask
  const request = Taro.request(requestConfig);
  const requestTask = request.then(response => transformResponse(response, config, request));
  const requestTaskAbort = requestTask.abort;

  // 设置超时
  let timer = null;
  if (config.timeout) {
    timer = setTimeout(() => {
      requestTaskAbort && requestTaskAbort();
      let { timeoutErrorMessage } = config;
      if (!config.timeoutErrorMessage) timeoutErrorMessage = `timeout of ${config.timeout}ms exceeded`;
      // 和 axios 报错对齐
      reject(createError(timeoutErrorMessage, config, 'ECONNABORTED', requestTask));
    }, config.timeout);
  }

  requestTask
    .then((response) => {
      timer && clearTimeout(timer);
      settle(resolve, reject, response);
    })
    .catch((response) => {
      timer && clearTimeout(timer);
      if (isObject(response) && (!isNull(response.status) || !isNull(response.statusCode))) {
        settle(resolve, reject, transformResponse(response, config, request));
      } else {
        reject(createError('Network Error', config, null, requestTask));
      }
    });

  if (config.cancelToken) {
    config.cancelToken.promise.then((cancel) => {
      timer && clearTimeout(timer);
      requestTaskAbort && requestTaskAbort();
      reject(cancel);
    });
  }
});
