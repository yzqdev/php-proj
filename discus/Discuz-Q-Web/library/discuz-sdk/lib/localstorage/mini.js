import api, { ENV_TYPE } from '../utils/platform-api';

/**
 * 本地存储类
 * 对应 web 端的 api 方法
 */
class LocalStorage {
  /**
   * 获取数据项
   * @param {string} key 键
   */
  getItem(key) {
    // 兼容支付宝小程序语法差异
    if (api.platform === ENV_TYPE.ALIPAY) {
      return api.getStorageSync({ key }).data;
    }
    return api.getStorageSync(key);
  }

  /**
   * 设置数据项
   * @param {string} key 键
   * @param {any} value 值
   */
  setItem(key, value) {
    // 兼容支付宝小程序语法差异
    if (api.platform === ENV_TYPE.ALIPAY) {
      return api.setStorageSync({ key, data: value });
    }
    return api.setStorageSync(key, value);
  }

  /**
   * 移除数据项
   * @param {string} key 键
   */
  removeItem(key) {
    // 兼容支付宝小程序语法差异
    if (api.platform === ENV_TYPE.ALIPAY) {
      return api.setStorageSync({ key });
    }
    return api.setStorageSync(key);
  }

  /**
   * 移除所有数据项
   */
  clear() {
    return api.clearStorageSync();
  }
}

export const localStorage = new LocalStorage(api);
export default localStorage;
