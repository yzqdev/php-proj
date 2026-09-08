/**
 * 描述：点赞
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
  data: {
    type: 'object',
    required: true,
    fields: {
      attributes: {
        type: 'object',
        required: true,
        fields: {
          // isLiked: {
          //   type: 'boolean',
          //   required: true
          // }
        }
      }
    }
  }
  // pid: {
  //   type: 'number',
  //   required: true
  // }
};

/**
 * 描述：点赞
 * 请求方式：POST
 * @param {axios config} opt 请求配置信息
 * @returns {promise}
 */
export async function updatePosts(opt = {}) {
  try {
    const { params = {}, data = {}, ...others } = opt;
    const options = {
      url: '/api/posts.update.v2', // 请求地址
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

