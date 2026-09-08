import RequestAPI from './api';
import { RESPONSE_CODE } from './code';

import { readThreadList } from './content/read-threadlist';
import { readThreadDetail } from './content/read-threaddetail';
import { readCategories } from './content/read-categories';
import { readCommentList } from './content/read-commentlist';
import { readStickList } from './content/read-sticklist';
import { readGroupsList } from './content/read-groupslist';

/**
 * 初始化实例
 * @param {axios config} options axios 的配置
 */
const apiIns = (options = {}) => {
  // 实例化请求 api
  const api = new RequestAPI(options);

  // 将要请求的接口挂载在实例化之后的 api 中，统一请求实例
  api.readThreadList = readThreadList;
  api.readThreadDetail = readThreadDetail;
  api.readCategories = readCategories;
  api.readCommentList = readCommentList;
  api.readStickList = readStickList;
  api.readGroupsList = readGroupsList;

  return api;
};

export {
  apiIns,
  RESPONSE_CODE,
};
