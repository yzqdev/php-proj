/**
 * 基础配置
 * @default
 */
export default {
  // 请求 url
  url: '',
  // 请求方法：GET | POST | PUT | PATCH | DELETE | CONNECT | TRACE | OPTIONS
  method: 'GET',
  // 返回的数据格式
  dataType: 'json',
  // 超时时间，默认 30s
  timeout: 30000,
  // 是否校验请求参数
  isValidate: false,
  // 校验规则
  validateRules: null,
};
