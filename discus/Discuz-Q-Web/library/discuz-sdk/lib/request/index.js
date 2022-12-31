/**
 * Request 请求
 */
import axios from 'axios';
import defaultConfig from './defaults';

const Request = axios;

// 设置默认配置
Request.defaults = { ...Request.defaults, ...defaultConfig, validateRules: null };

export { Request };
export default Request;
