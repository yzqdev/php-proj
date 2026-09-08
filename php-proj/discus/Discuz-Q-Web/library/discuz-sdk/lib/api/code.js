/**
 * @constant {object} 响应状态码
 */
export const RESPONSE_CODE = {
  // 接口调用成功
  SUCCESS: 0,
  // 跳转到登录页
  JUMP_TO_LOGIN: -3001,
  // 跳转到注册页
  JUMP_TO_REGISTER: -3002,
  // 跳转到审核页
  JUMP_TO_AUDIT: -3003,
  // 跳转到首页
  JUMP_TO_HOME_INDEX: -3004,
  // 参数错误
  INVALID_PARAMETER: -4001,
  // 没有权限
  UNAUTHORIZED: -4002,
  // 资源已存在
  RESOURCE_EXIST: -4003,
  // 资源不存在
  RESOURCE_NOT_FOUND: -4004,
  // 资源被占用
  RESOURCE_IN_USE: -4005,
  // 网络错误
  NET_ERROR: -5001,
  // 内部系统错误
  INTERNAL_ERROR: -5002,
  // 数据库错误
  DB_ERROR: -5003,
  // 外部接口错误
  EXTERNAL_API_ERROR: -5004,
  // 未知错误
  UNKNOWN_ERROR: -6001,
  // 调试错误
  DEBUG_ERROR: -6002,
};
