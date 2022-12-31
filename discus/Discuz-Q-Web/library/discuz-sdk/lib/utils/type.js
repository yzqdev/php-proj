/**
 * 判断传入的变量是否是字符串
 * @param {any} val 变量
 * @returns {boolean} true：字符串；反之不是
 *
 * @example
 *
 * isString('discuzq'); // true
 * isString(true); // false
 */
export function isString(val) {
  return typeof val === 'string';
}

/**
 * 判断传入的变量是否是对象
 * @param {any} val 变量
 * @returns {boolean} true: 对象；反之不是
 *
 * @example
 *
 * isObject({}); // true
 * isObject('aaa'); // false
 */
export function isObject(val) {
  return Object.prototype.toString.call(val) === '[object Object]';
}

/**
 * 判断是否是null
 *
 * @param {any} val 变量
 * @returns {boolean} true：Null；反之不是
 *
 * @example
 *
 * isNull(null); // true
 * isNull(2); // false
 */

export function isNull(val) {
  return Object.prototype.toString.call(val) === '[object Null]';
}

/**
 * 判断是否是数组
 * @param {any} val 变量
 * @returns {boolean} true：数组；反之不是
 *
 * @example
 *
 * isArray([]); // true
 * isArray(2); // false
 */
export function isArray(val) {
  return Object.prototype.toString.call(val) === '[object Array]';
}
