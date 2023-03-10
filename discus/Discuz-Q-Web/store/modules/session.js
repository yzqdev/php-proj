/* eslint no-underscore-dangle: ["error", { "allow": ["_jv"] }] */
import service from '@/api/request';
import { utils } from '@/store/modules/jsonapi-vuex/index';
import {
  SET_USER_ID,
  CHECK_SESSION,
  SET_ACCESS_TOKEN,
  SET_AUTH,
  DELETE_USER_ID,
  DELETE_ACCESS_TOKEN,
  SET_AUDIT_INFO
} from '@/store/types/session';

const accessToken = process.client ? localStorage.getItem('access_token') : '';

// 设置用户id,token
const setUserInfoStore = (context, results, resolve) => {
  const resData = utils.jsonapiToNorm(results.data.data);
  context.commit(SET_USER_ID, resData._jv.id);
  context.commit(CHECK_SESSION, true);
  context.commit(SET_ACCESS_TOKEN, resData.access_token);
  resolve(resData);
};

const state = () => {
  return {
    userId: 0, // 用户id
    wxLogin: false,
    accessToken, // 用户token
    auth: {},
    categoryId: 0,
    categoryIndex: 0,
    auditInfo: {
      username: '',
      reason: '',
      errorCode: ''
    }
  };
};

const actions = {
  /**
   * 设置用户ID
   */
  setUserId: (context, payload) => {
    context.commit(SET_USER_ID, payload);
  },
  checkSession: (context, payload) => {
    context.commit(CHECK_SESSION, payload);
  },
  setAccessToken: (context, payload) => {
    context.commit(SET_ACCESS_TOKEN, payload);
  },
  setAuth: (context, payload) => {
    context.commit(SET_AUTH, payload);
  },
  setAuditInfo: (context, payload) => {
    context.commit(SET_AUDIT_INFO, payload);
  },
  // 手机号验证码登录
  verificationCodeh5Login: (context, payload = {}) => {
    return new Promise(resolve => {
      return service
        .post('sms/verify', payload)
        .then(results => {
          resolve(results);
          setUserInfoStore(context, results, resolve);
        })
        .catch(error => {
          resolve(error.response);
        });
    });
  },
  // 用户登录
  h5Login: (context, payload = {}) => {
    return new Promise(resolve => {
      return service
        .post('login', payload)
        .then(results => {
          resolve(results);
          setUserInfoStore(context, results, resolve);
        })
        .catch(error => {
          resolve(error.response);
        });
    });
  },
  // 用户注册
  h5Register: (context, payload = {}) => {
    // const options = { custom: { showTost: false }}
    return new Promise(resolve => {
      return service
        .post('register', payload)
        .then(results => {
          resolve(results);
          setUserInfoStore(context, results, resolve);
        })
        .catch(error => {
          resolve(error.response);
        });
    });
  },
  // 退出登录
  logout: context => {
    return new Promise(resolve => {
      context.commit(DELETE_USER_ID);
      context.commit(DELETE_ACCESS_TOKEN);
      resolve();
    });
  }
};

const mutations = {
  // 设置用户id
  [SET_USER_ID](state, payload) {
    if (process.client) localStorage.setItem('user_id', payload);
    state.userId = payload;
  },
  [CHECK_SESSION](state, payload) {
    state.wxLogin = payload;
  },
  // 设置用户token
  [SET_ACCESS_TOKEN](state, payload) {
    if (process.client) localStorage.setItem('access_token', payload);
    state.accessToken = payload;
  },
  [SET_AUTH](state, payload) {
    state.auth = payload;
  },
  // 删除用户id
  [DELETE_USER_ID](state) {
    if (process.client) localStorage.removeItem('user_id');
    state.userId = 0;
  },
  // 删除用户token
  [DELETE_ACCESS_TOKEN](state) {
    if (process.client) localStorage.removeItem('access_token');
    state.accessToken = '';
  },
  // 审核信息
  [SET_AUDIT_INFO](state, payload) {
    const { username = '', reason = '', errorCode = '' } = payload || {};
    state.auditInfo = { ...state.auditInfo, username, reason, errorCode };
  }
};

const getters = {
  get: state => {
    return data => {
      switch (data) {
        case 'userId':
          return process.client ? localStorage.getItem('user_id') : state.userId;
        case 'isWxLogin':
          return state.wxLogin;
        case 'isLogin':
          return process.client ? !!localStorage.getItem('access_token') : false;
        default:
          return state[data];
      }
    };
  }
};

export default {
  namespaced: true,
  state,
  actions,
  getters,
  mutations
};
