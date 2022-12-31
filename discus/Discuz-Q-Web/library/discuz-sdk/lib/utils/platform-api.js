/**
 * 平台
 */
export const ENV_TYPE = {
  WEAPP: 'WEAPP',
  SWAN: 'SWAN',
  ALIPAY: 'ALIPAY',
  TT: 'TT',
  QQ: 'QQ',
  JD: 'JD',
  H5: 'H5',
};

/**
 * 适配小程序 API 宿主对象
 */
function getApi() {
  if (typeof jd !== 'undefined') {
    jd.platform = ENV_TYPE.JD;
  }
  if (typeof my !== 'undefined') {
    my.platform = ENV_TYPE.ALIPAY;
    return my;
  }
  if (typeof tt !== 'undefined') {
    tt.platform = ENV_TYPE.TT;
    return tt;
  }
  if (typeof swan !== 'undefined') {
    swan.platform = ENV_TYPE.SWAN;
    return swan;
  }
  if (typeof qq !== 'undefined') {
    qq.platform = ENV_TYPE.QQ;
    return qq;
  }
  if (typeof wx !== 'undefined') {
    // 微信平台下分为小程序和微信 h5
    wx.platform = typeof window !== 'undefined' && typeof location !== 'undefined' ? ENV_TYPE.H5 : ENV_TYPE.WEAPP;
    return wx;
  }
  return { platform: 'other' };
}

export default getApi();
