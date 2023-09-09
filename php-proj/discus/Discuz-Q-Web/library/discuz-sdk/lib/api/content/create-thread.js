/**
 * 描述：创建主题接口
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
  // 分类id
  categoriesId: {
    type: 'number',
    required: true
  },
  // 内容
  content: {
    type: 'string',
    required: true
  },
  // 类型 0文字 1帖子 3图片 5问答 6商品
  type: {
    type: 'number',
    required: true
  },
  // 是否匿名 0否 1是
  isAnonymous: {
    type: 'number'
  },
  // 价格
  price: {
    type: 'number'
  },
  // 附件价格
  attachmentPrice: {
    type: 'number'
  },
  // 免费字数百分比
  freeWords: {
    type: 'number'
  },
  // 位置
  location: {
    type: 'string'
  },
  // 经度
  longitude: {
    type: 'number'
  },
  // 纬度
  latitude: {
    type: 'number'
  },
  // 地址
  address: {
    type: 'string'
  },
  // 主题id
  id: {
    type: 'string'
  },
  // 是否为草稿 0不是，1是
  isDraft: {
    type: 'number'
  },
  // 是否添加红包 0未添加，1添加
  isRedPacket: {
    type: 'number'
  },
  // 是否为旧草稿 0不是，1是
  isOldDraft: {
    type: 'number'
  },
  // 标题
  title: {
    type: 'string'
  },
  // 商品id
  postGoodsId: {
    type: 'number'
  },
  // 红包信息
  redPacket: {
    type: 'object',
    fields: {
      // 领取红包条件 0回复，1集赞
      condition: {
        type: 'number'
      },
      // 若红包领取条件为集赞，必填集赞数
      likenum: {
        type: 'number'
      },
      // 红包总金额
      money: {
        type: 'number'
      },
      // 红包个数
      number: {
        type: 'number'
      },
      // 发放规则 0定额，1随机
      rule: {
        type: 'number'
      },
      // 订单id
      orderId: {
        type: 'string'
      },
      // 订单金额 定额时为每个红包价格 随机时为总价格
      orderPrice: {
        type: 'number'
      }
    }
  },
  // 附件信息
  attachments: {
    type: 'array',
    defaultField: {
      type: 'object',
      fields: {
        // 附件id
        id: {
          type: 'number'
        },
        // 附件类型 填attachments
        type: {
          type: 'string'
        }
      }
    }
  },
  // 问答信息
  question: {
    type: 'object',
    fields: {
      // 问答类型 0悬赏问答 1指定人问答
      type: {
        type: 'number'
      },
      // 问答价格
      price: {
        type: 'number'
      },
      // 订单id
      orderId: {
        type: 'string'
      },
      // 是否允许围观
      isOnlooker: {
        type: 'boolean'
      },
      // 被提问人
      beUserId: {
        type: 'number'
      },
      // 悬赏结束时间
      expiredAt: {
        type: 'date'
      }
    }
  }
};

/**
 * 描述：创建主题接口
 * 请求方式：POST
 * @param {axios config} opt 请求配置信息
 * @returns {promise}
 */
export async function createThread(opt = {}) {
  try {
    const { params = {}, data = {}, ...others } = opt;
    const options = {
      url: '/api/thread.v2', // 请求地址
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

