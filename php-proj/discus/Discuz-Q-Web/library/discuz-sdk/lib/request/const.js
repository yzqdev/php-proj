/**
 * https://developer.mozilla.org/zh-CN/docs/Web/HTTP/Methods
 * 请求方法
 */
export const METHODS = {
  // GET方法请求一个指定资源的表示形式. 使用GET的请求应该只被用于获取数据.
  GET: 'GET',
  // HEAD方法请求一个与GET请求的响应相同的响应，但没有响应体.
  HEAD: 'HEAD',
  // POST方法用于将实体提交到指定的资源，通常导致在服务器上的状态变化或副作用.
  POST: 'POST',
  // PUT方法用请求有效载荷替换目标资源的所有当前表示
  PUT: 'PUT',
  // PATCH方法用于对资源应用部分修改
  PATCH: 'PATCH',
  // OPTIONS方法用于描述目标资源的通信选项
  OPTIONS: 'OPTIONS',
  // TRACE方法沿着到目标资源的路径执行一个消息环回测试
  TRACE: 'TRACE',
  // CONNECT方法建立一个到由目标资源标识的服务器的隧道
  CONNECT: 'CONNECT',
  // DELETE方法删除指定的资源
  DELETE: 'DELETE',
};

/**
 * https://developer.mozilla.org/zh-CN/docs/Web/HTTP/Status
 * 请求状态映射表，这里只列举部分
 */
export const STATUS_TEXT_MAP = {
  200: 'OK',
  204: 'No Content',
  301: 'Moved Permanently',
  302: 'Found',
  304: 'Not Modified',
  307: 'Temporary Redirect',
  400: 'Bad Request',
  401: 'Unauthorized',
  403: 'Forbidden',
  404: 'Not Found',
  500: 'Internal Server Error',
  502: 'Bad Gateway',
  503: 'Service Unavailable',
  504: 'Gateway Timeout',
};
