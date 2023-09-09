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
  categoryId: {
    type: 'array',
  },
};

/**
 * 描述：获取置顶列表
 * 请求方式：GET
 * @param {axios config} opt 请求配置信息
 * @returns {promise}
 */
export async function readStickList(opt = {}) {
  try {
    const { params = {}, data = {}, ...others } = opt;
    const options = {
      url: '/api/sticks.v2', // 请求地址
      method: 'GET',
      params,
      data,
      ...others,
      validateRules,
    };
    const result = await this.http(options);
    return result;
  } catch (error) {
    return error;
  }
};
