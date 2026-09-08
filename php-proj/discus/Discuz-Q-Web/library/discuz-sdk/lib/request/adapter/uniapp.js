/**
 * 处理 uniapp 框架的请求
 */
import settle from 'axios/lib/core/settle';
import createError from 'axios/lib/core/createError';
import { transfromRequest, transformResponse } from './common';

export const uniAdapter = config => new Promise((resolve, reject) => {
  // 设置超时
  let timer = null;
  const requestConfig = transfromRequest(config);
  // https://uniapp.dcloud.io/api/request/request?id=request
  const requestTask = uni.request({
    ...requestConfig,
    success: (response) => {
      timer && clearTimeout(timer);
      settle(resolve, reject, transformResponse(response, config, requestTask));
    },
    fail: () => {
      timer && clearTimeout(timer);
      reject(createError('Network Error', config, null, requestTask));
    },
  });
  const requestTaskAbort = requestTask.abort;

  if (config.timeout) {
    timer = setTimeout(() => {
      requestTaskAbort && requestTaskAbort();
      let { timeoutErrorMessage } = config;
      if (!config.timeoutErrorMessage) timeoutErrorMessage = `timeout of ${config.timeout}ms exceeded`;
      // 和 axios 报错对齐
      reject(createError(timeoutErrorMessage, config, 'ECONNABORTED', requestTask));
    }, config.timeout);
  }

  if (config.cancelToken) {
    config.cancelToken.promise.then((cancel) => {
      timer && clearTimeout(timer);
      requestTaskAbort && requestTaskAbort();
      reject(cancel);
    });
  }
});
