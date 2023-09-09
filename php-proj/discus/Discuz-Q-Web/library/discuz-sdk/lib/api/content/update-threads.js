/**
 * 描述：修改主题（单个）
 */
// import { handleError } from '../utils/handle-error';
/**
 * 入参验证规则，具体配置请看：https://github.com/yiminghe/async-validator
 *
 * @example
 *  const validateRules = {
 *    name: {
 *      type: 'number',
 *      required: true,
 *    },
 *  };
 */
const validateRules = {
  id: {
    type: 'number',
    required: true
  }
};

/**
 * 描述：修改主题（单个）
 * 请求方式：POST
 * @param {axios config} opt 请求配置信息
 * @returns {promise}
 */
export async function updateThreads(opt = {}) {
  try {
    const { params = {}, data = {}, ...others } = opt;
    console.log(data);
    const options = {
      url: '/api/threads/update.v2', // 请求地址
      method: 'POST',
      params,
      data,
      ...others,
      validateRules
    };
    const result = await this.http(options);
    return result;
  } catch (error) {
    // return handleError(error);
  }
}

