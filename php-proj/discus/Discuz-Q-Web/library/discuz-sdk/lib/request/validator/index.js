import Schema from 'async-validator';
import createError from 'axios/lib/core/createError';

/**
 * 请求字段校验
 * https://github.com/yiminghe/async-validator
 *
 * @param {object} fields 要校验的字段
 * @param {object} rules 校验规则
 * @returns {promise}
 */
export function requestParamsValidator(fields = {}, rules = {}) {
  return new Promise((resolve, reject) => {
    const validator = new Schema(rules);
    validator.validate(fields, { firstFields: true }, (errors, fieldsData) => {
      const status = !errors ? 'success' : 'fail';
      const message = errors ? errors[0].message : '';
      if (status === 'success') {
        resolve({ status, message, fieldsData });
      } else {
        console.warn('The current parameter validation failed.');
        reject(createError('Parameter Validator Error', { status, message, fieldsData, errors }, null));
      }
    });
  });
}

/**
 * 入参校验
 * @param {axios config} config axios 返回的配置信息
 */
export const validateParameter = async (config) => {
  const { params = {}, data = {}, isValidate, validateRules } = config;
  if (isValidate && validateRules) {
    const source = { ...params, ...data };
    await requestParamsValidator(source, validateRules, config);
  }
  return config;
};
