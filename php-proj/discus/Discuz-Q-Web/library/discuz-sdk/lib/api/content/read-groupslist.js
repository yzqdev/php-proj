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
*  付费用户组接口 , 用户付费用户组列表  /groups/paid
 */
const validateRules = {
};

/**
 * 描述：获取置顶列表
 * 请求方式：GET
 * @param {axios config} opt 请求配置信息
 * @returns {promise}
 */
export async function readGroupsList(opt = {}) {
  try {
    const { params = {}, data = {}, ...others } = opt;
    const options = {
      url: '/api/groups', // 请求地址
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
