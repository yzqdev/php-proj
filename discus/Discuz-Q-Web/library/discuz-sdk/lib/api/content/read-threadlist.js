/**
 * 获取文章列表
 */

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
  /**
   * 页码
   */
  page: {
    type: 'number',
  },
  /**
   * 每页数据条数，不传默认20，最大值50
   */
  perPage: {
    type: 'number',
  },
  /**
   * 筛选条件
   */
  filter: {
    type: 'object',
    required: true,
    fields: {
      /**
       * 是否置顶：1 - 置顶，0 - 不筛选
       */
      sticky: {
        type: 'number',
        required: true,
      },
      /**
       * 是否精华：1 - 精华，0 - 不筛选
       */
      essence: {
        type: 'number',
        required: true,
      },
      /**
       * 筛选帖子类型
       */
      types: {
        type: 'array',
      },
      /**
       * 筛选帖子分组 id
       */
      categoryids: {
        type: 'array',
      },
      /**
       * 帖子排序：1 - 不限，2 - 发帖时间，3 - 评论时间
       */
      sort: {
        type: 'number',
      },
      /**
       * 是否关注：1 - 关注，0 - 不关注
       */
      attention: {
        type: 'number',
      },
    },
  },
};

/**
 * 描述：获取文章列表
 * 请求方式：GET
 * @param {axios config} opt 请求配置信息
 * @returns {promise}
 */
export async function readThreadList(opt = {}) {
  try {
    const { params = {}, data = {}, ...others } = opt;
    const options = {
      url: '/api/threads.v2', // 请求地址
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
