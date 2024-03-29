/* eslint-disable max-len */
/**
 * A class containing utility functions for use with jsonapi-vuex
 *
 * **Note** - an instance of this class is exported as `utils` from `jsonapi-vuex`,
 * so it does not need to be used directly.
 *
 * @name Utils
 * @namespace utils
 * @memberof module:jsonapi-vuex
 * @param {object} conf - A jsonapi-vuex config object.
 */

import Vue from 'vue';
import get from 'lodash.get';
import isEqual from 'lodash.isequal';
import merge from 'lodash.merge';

/**
 * Helper methods added to `_jv` by {@link module:jsonapi-vuex.utils.addJvHelpers}
 * @namespace helpers
 * @memberof module:jsonapi-vuex.
 */

/**
 * Documentation for internal functions etc.
 * These are not available when the module is imported,
 * and are documented for module developers only.
 * @namespace _internal
 * @memberof module:jsonapi-vuex
 */

const Utils = class {
  constructor(conf) {
    this.conf = conf;
    this.jvtag = conf.jvtag;
    this.context = null;
  }

  /**
   * @memberof module:jsonapi-vuex._internal
   * @param {object} data - An object to be deep copied
   * @return {object} A deep copied object
   */

  _copy(data) {
    // Recursive object copying function (for 'simple' objects)
    const out = Array.isArray(data) ? [] : {};
    for (const key in data) {
      // null is typeof 'object'
      if (typeof data[key] === 'object' && data[key] !== null) {
        out[key] = this._copy(data[key]);
      } else {
        out[key] = data[key];
      }
    }
    return out;
  }

  /**
   * Add helper functions and getters to a restructured object
   * @memberof module:jsonapi-vuex.utils
   * @param {object} obj - An object to assign helpers to
   * @return {object} A copy of the object with added helper functions/getters
   */
  addJvHelpers(obj) {
    // Avoid 'this' confusion in property definitions
    const { jvtag } = this;
    const { hasProperty } = this;
    if (obj[jvtag] && !hasProperty(obj[jvtag], 'isRel') && !hasProperty(obj[jvtag], 'isAttr')) {
      Object.assign(obj[jvtag], {
        /**
         * @memberof module:jsonapi-vuex.helpers
         * @param {string} name - Name of a (potential) relationship
         * returns {boolean} true if the name given is a relationship of this object
         */
        isRel(name) {
          return hasProperty(get(obj, [jvtag, 'relationships'], {}), name);
        },
        /**
         * @memberof module:jsonapi-vuex.helpers
         * @param {string} name - Name of a (potential) attribute
         * returns {boolean} true if the name given is an attribute of this object
         */
        isAttr(name) {
          return name !== jvtag && hasProperty(obj, name) && !obj[jvtag].isRel(name);
        }
      });
    }
    /**
     * @memberof module:jsonapi-vuex.helpers
     * @name rels
     * @property {object} - An object containing all relationships for this object
     */
    if (hasProperty(obj, jvtag)) {
      Object.defineProperty(obj[jvtag], 'rels', {
        get() {
          const rel = {};
          for (const key of Object.keys(get(obj, [jvtag, 'relationships'], {}))) {
            rel[key] = obj[key];
          }
          return rel;
        },
        // Allow to be redefined
        configurable: true
      });
      /**
       * @memberof module:jsonapi-vuex.helpers
       * @name attrs
       * @property {object} - An object containing all attributes for this object
       */
      Object.defineProperty(obj[jvtag], 'attrs', {
        get() {
          const att = {};
          for (const [key, val] of Object.entries(obj)) {
            if (obj[jvtag].isAttr(key)) {
              att[key] = val;
            }
          }
          return att;
        },
        // Allow to be redefined
        configurable: true
      });
    }
    return obj;
  }

  /**
   * If `followRelationshipData` is set, call `followRelationships` for either an item or a collection
   * See {@link module:jsonapi-vuex~Configuration|Configuration}
   * @memberof module:jsonapi-vuex._internal
   * @param {object} state - Vuex state object
   * @param {object} getters - Vuex getters object
   * @param {object} records - Record(s) to follow relationships for.
   * @param {array} seen - internal recursion state-tracking
   * @return {object} records with relationships followed
   */
  checkAndFollowRelationships(state, getters, records, seen) {
    if (this.conf.followRelationshipsData) {
      let resData = {};
      if (this.hasProperty(records, this.jvtag)) {
        // single item
        resData = this.followRelationships(state, getters, records, seen);
      } else {
        // multiple items
        for (const [key, item] of Object.entries(records)) {
          resData[key] = this.followRelationships(state, getters, item, seen);
        }
      }
      if (resData) {
        return resData;
      }
    }
    return records;
  }

  /**
   * A function that cleans up a patch object, so that it doesn't introduce unexpected changes when sent to the API.
   * It removes any attributes which are unchanged from the store, to minimise accidental reversions.
   * It also strips out any of links, relationships and meta from `_jv` - See {@link module:jsonapi-vuex~Configuration|Configuration}
   * @memberof module:jsonapi-vuex.utils
   * @param {object} patch - A restructured object to be cleaned
   * @param {object} state={} - Vuex state object (for patch comparison)
   * @param {array} jvProps='[]' - _jv Properties to be kept
   * @return {object} A cleaned copy of the patch object
   */
  cleanPatch(patch, state = {}, jvProps = []) {
    // Add helper properties (use a copy to prevent side-effects)
    const modPatch = this.deepCopy(patch);
    const attrs = get(modPatch, [this.jvtag, 'attrs']);
    const clean = { [this.jvtag]: {}};
    // Only try to clean the patch if it exists in the store
    const stateRecord = get(state, [modPatch[this.jvtag].type, modPatch[this.jvtag].id]);
    if (stateRecord) {
      for (const [k, v] of Object.entries(attrs)) {
        if (!this.hasProperty(stateRecord, k) || !isEqual(stateRecord[k], v)) {
          clean[k] = v;
        }
      }
    } else {
      Object.assign(clean, attrs);
    }

    // Add _jv data, as required
    clean[this.jvtag].type = patch[this.jvtag].type;
    clean[this.jvtag].id = patch[this.jvtag].id;
    for (const prop of jvProps) {
      const propVal = get(patch, [this.jvtag, prop]);
      if (propVal) {
        clean[this.jvtag][prop] = propVal;
      }
    }

    return clean;
  }

  /**
   * Deep copy a normalised object, then re-add helper nethods
   * @memberof module:jsonapi-vuex.utils
   * @param {object} obj - An object to be deep copied
   * @return {object} A deep copied object, with Helper functions added
   */
  deepCopy(obj) {
    let copyObj = this._copy(obj);
    if (Object.entries(copyObj).length) {
      if (this.hasProperty(copyObj, this.jvtag)) {
        copyObj = this.addJvHelpers(copyObj);
      }
      return copyObj;
    }
    return obj;
  }

  /**
   * A thin wrapper around `getRelationships, making a copy of the object.
   * We can't add rels to the original object, otherwise Vue's watchers
   * spot the potential for loops (which we are guarding against) and throw an error
   *
   * @memberof module:jsonapi-vuex._internal
   * @param {object} state - Vuex state object
   * @param {object} getters - Vuex getters object
   * @param {object} record - Record to get relationships for.
   * @param {array} seen - internal recursion state-tracking
   * @return {object} records with relationships followed and helper functions added (see {@link module:jsonapi-vuex.utils.addJvHelpers})
   */
  followRelationships(state, getters, record, seen) {
    const data = {};

    if (typeof Object.getOwnPropertyDescriptors === 'undefined') {
      Object.getOwnPropertyDescriptors = function(obj) {
        if (obj === null || obj === undefined) {
          throw new TypeError('Cannot convert undefined or null to object');
        }
        const protoPropDescriptor = Object.getOwnPropertyDescriptor(obj, '__proto__');
        const descriptors = protoPropDescriptor ? { ['__proto__']: protoPropDescriptor } : {};

        for (const name of Object.getOwnPropertyNames(obj)) {
          descriptors[name] = Object.getOwnPropertyDescriptor(obj, name);
        }
        return descriptors;
      };
    }

    Object.defineProperties(data, Object.getOwnPropertyDescriptors(record));

    const relationships = this.getRelationships(getters, data, seen);
    Object.defineProperties(data, Object.getOwnPropertyDescriptors(relationships));

    return this.addJvHelpers(data);
  }

  /**
   * Make a copy of a restructured object, adding (js) getters for its relationships
   * That call the (vuex) get getter to fetch that record from the store
   *
   * Already seen objects are tracked using the 'seen' param to avoid loops.
   *
   * @memberof module:jsonapi-vuex._internal
   * @param {object} getters - Vuex getters object
   * @param {object} parent - The object whose relationships should be fetched
   * @param {array} seen - Internal recursion state tracking
   * @returns {object} A copy of the object with getter relationships added
   */
  getRelationships(getters, parent, seen = []) {
    // Avoid 'this' confusion in Object.defineProperty
    const { conf } = this;
    const { jvtag } = this;
    const relationships = get(parent, [jvtag, 'relationships'], {});
    const relationshipsData = {};
    for (const relName of Object.keys(relationships)) {
      const relations = get(relationships, [relName, 'data']);
      relationshipsData[relName] = {};
      if (relations) {
        const isItem = !Array.isArray(relations);
        const relationsData = [];

        for (const relation of isItem ? Array.of(relations) : relations) {
          const relType = relation.type;
          const relId = relation.id;

          const current = [relName, relType, relId];
          let data = {};
          // Stop if seen contains an array which matches 'current'
          if (!conf.recurseRelationships && seen.some(a => a.every((v, i) => v === current[i]))) {
            data = { [jvtag]: { type: relType, id: relId }};
          } else {
            // prettier-ignore
            data = getters.get(
              `${relType}/${relId}`,
              undefined,
              [...seen, [relName, relType, relId]]
            );
          }

          if (relationsData.indexOf(data) === -1) {
            relationsData.push(data);
          }
        }
        if (isItem) {
          relationshipsData[relName] = relationsData[0];
        } else {
          relationshipsData[relName] = relationsData;
        }
      }
    }
    return relationshipsData;
  }

  /**
   * Get the type, id and relationships from a restructured object
   * @memberof module:jsonapi-vuex.utils
   * @param {object} data - A restructured object
   * @return {array} An array (optionally) containing type, id and rels
   */
  getTypeId(data) {
    let type;
    let id;
    let rel;
    if (typeof data === 'string') {
      [type, id, rel] = data.replace(/^\//, '').split('/');
    } else {
      ({ type, id } = data[this.jvtag]);
    }

    // Spec: The values of the id and type members MUST be strings.
    // uri encode to prevent mis-interpretation as url parts.
    // Strip any empty strings (falsey items)
    return [
      type && encodeURIComponent(type),
      id && encodeURIComponent(id),
      rel && encodeURIComponent(rel)
    ].filter(Boolean);
  }

  /**
   * Return the URL path (links.self) or construct from type/id
   * @memberof module:jsonapi-vuex.utils
   * @param {object} data - A restructured object
   * @return {string} The record's URL path
   */
  getURL(data, post = false) {
    let path = data;
    if (typeof data === 'object') {
      if (get(data, [this.jvtag, 'links', 'self']) && !post) {
        path = data[this.jvtag].links.self;
      } else {
        const { type, id } = data[this.jvtag];
        path = type;
        // POST endpoints are always to collections, not items
        if (id && !post) {
          path += `/${id}`;
        }
      }
    }
    return path;
  }

  /**
   * Shorthand for the 'safe' `hasOwnProperty` as described here:
   * [eslint: no-prototype-builtins](https://eslint.org/docs/rules/no-prototype-builtins])
   * @name hasProperty
   * @memberof module:jsonapi-vuex._internal
   */
  hasProperty(obj, prop) {
    return Object.prototype.hasOwnProperty.call(obj, prop);
  }

  /**
   * Convert JSONAPI record(s) to restructured data
   * @memberof module:jsonapi-vuex.utils
   * @param {object} data - The `data` object from a JSONAPI record
   * @return {object} Restructured data
   */
  jsonapiToNorm(data) {
    const norm = {};
    if (Array.isArray(data)) {
      data.forEach(item => {
        const { id } = item;
        if (!this.hasProperty(norm, id)) {
          norm[id] = {};
        }
        Object.assign(norm[id], this.jsonapiToNormItem(item));
      });
    } else {
      Object.assign(norm, this.jsonapiToNormItem(data));
    }
    return norm;
  }

  /**
   * Restructure a single jsonapi item. Used internally by {@link module:jsonapi-vuex.utils.jsonapiToNorm}
   * @memberof module:jsonapi-vuex._internal
   * @param {object} data - JSONAPI record
   * @return {object} Restructured data
   */
  jsonapiToNormItem(data) {
    if (!data) {
      return {};
    }
    // Move attributes to top-level, nest original jsonapi under _jv
    const norm = { [this.jvtag]: data, ...data.attributes };
    // Create a new object omitting attributes
    const { attributes, ...normNoAttrs } = norm[this.jvtag]; // eslint-disable-line no-unused-vars
    norm[this.jvtag] = normNoAttrs;
    return norm;
  }

  /**
   * Convert one or more restructured records to jsonapi
   * @memberof module:jsonapi-vuex.utils
   * @param {object} record - A restructured record to be convert to JSONAPI
   * @return {object} JSONAPI record
   */
  normToJsonapi(record) {
    const jsonapi = [];
    if (!this.hasProperty(record, this.jvtag)) {
      // Collection of id-indexed records
      for (const item of Object.values(record)) {
        jsonapi.push(this.normToJsonapiItem(item));
      }
    } else {
      jsonapi.push(this.normToJsonapiItem(record));
    }
    if (jsonapi.length === 1) {
      return { data: jsonapi[0] };
    }
    return { data: jsonapi };
  }

  /**
   * Convert a single restructured item to JSONAPI. Used internally by {@link module:jsonapi-vuex.utils.normToJsonapi}
   * @memberof module:jsonapi-vuex._internal
   * @param {object} data - Restructured data
   * @return {object}  JSONAPI record
   */
  normToJsonapiItem(data) {
    const jsonapi = {};
    // Pick out expected resource members, if they exist
    for (const member of ['id', 'type', 'relationships', 'meta', 'links']) {
      if (this.hasProperty(data[this.jvtag], member)) {
        jsonapi[member] = data[this.jvtag][member];
      }
    }
    // User-generated data (e.g. post) has no helper functions
    if (this.hasProperty(data[this.jvtag], 'attrs')) {
      jsonapi.attributes = data[this.jvtag].attrs;
    } else {
      jsonapi.attributes = { ...data };
      delete jsonapi.attributes[this.jvtag];
    }
    return jsonapi;
  }

  /**
   * Convert one or more restructured records to nested (type & id) 'store' object
   * @memberof module:jsonapi-vuex.utils
   * @param {object} record - A restructured record to be convert to JSONAPI
   * @return {object} Structured 'store' object
   */
  normToStore(record) {
    const store = {};
    if (this.hasProperty(record, this.jvtag)) {
      // Convert item to look like a collection
      record = { [record[this.jvtag].id]: record };
    }
    for (const item of Object.values(record)) {
      const { type, id } = item[this.jvtag];
      if (!this.hasProperty(store, type)) {
        store[type] = {};
      }
      if (this.conf.followRelationshipsData) {
        for (const rel in item[this.jvtag].rels) {
          delete item[rel];
        }
      }
      store[type][id] = item;
    }
    return store;
  }

  /**
   * If `preserveJSON` is set, add the returned JSONAPI in a get action to _jv.json
   * See {@link module:jsonapi-vuex~Configuration|Configuration}
   * @memberof module:jsonapi-vuex._internal
   * @param {object} data - Restructured record
   * @param {object} json - JSONAPI record
   * @return {object} data record, with JSONAPI added in _jv.json
   */
  preserveJSON(data, json) {
    if (this.conf.preserveJson && data) {
      if (!this.hasProperty(data, this.jvtag)) {
        data[this.jvtag] = {};
      }
      // Store original json in _jv
      data[this.jvtag].json = json;
    }
    return data;
  }

  /**
   * Restructure all records in 'included' (using {@link module:jsonapi-vuex._internal.jsonapiToNormItem})
   * and add to the store.
   * @memberof module:jsonapi-vuex._internal
   * @param {object} context - Vuex actions context object
   * @param {object} results - JSONAPI record
   */
  processIncludedRecords(context, results) {
    for (const item of get(results, ['data', 'included'], [])) {
      const includedItem = this.jsonapiToNormItem(item);
      context.commit('addRecords', includedItem);
    }
  }

  /**
   * Push resources contained within an API payload into the store.
   *
   * @param {Object} payload
   * @return {Model|Model[]} The model(s) representing the resource(s) contained
   *     within the 'data' key of the payload.
   * @public
   */
  pushPayload(payload, context) {
    this.context = context;

    if (payload.included) payload.included.map(this.pushObject.bind(this));

    let result
      = payload.data instanceof Array
        ? payload.data.map(this.pushObject.bind(this))
        : this.pushObject(payload.data);

    result = this.preserveJSON(result, payload);

    return result;
  }

  /**
   * Create a model to represent a resource object (or update an existing one),
   * and push it into the store.
   *
   * @param {Object} data The resource object
   * @return {Model|null} The model, or null if no model class has been
   *     registered for this resource type.
   * @public
   */
  pushObject(data) {
    let item = this.jsonapiToNormItem(data);
    // 注册拓展的时候不存store,因为id为空，type一致
    if (data && data.type !== 'user_sign_in') {
      this.context.commit('addRecords', item);
    }

    if (this.conf.followRelationshipsData) {
      item = this.followRelationships(this.context.state, this.context.getters, item);
    }
    return item;
  }

  /**
   * Transform args to always be an array (data and config options).
   * See {@link module:jsonapi-vuex.actions} for an explanation of why this function is needed.
   *
   * @memberof module:jsonapi-vuex._internal
   * @param {(string|array)} args - Array of data and configuration info
   * @return {array} Array of data and config options
   */
  unpackArgs(args) {
    if (Array.isArray(args)) {
      return args;
    }
    return [args, {}];
  }

  /**
   * A single function to encapsulate the different merge approaches of the record mutations.
   * See {@link module:jsonapi-vuex.mutations} to see the mutations that use this function.
   *
   * @memberof module:jsonapi-vuex._internal
   * @param {object} state - Vuex state object
   * @param {object} records - Restructured records to be updated
   * @param {boolean} merging - Whether or not to merge or overwrite records
   */
  updateRecords(state, records, merging = this.conf.mergeRecords) {
    const storeRecords = this.normToStore(records);
    for (const [type, item] of Object.entries(storeRecords)) {
      if (!this.hasProperty(state, type)) {
        Vue.set(state, type, {});
        // If there's no type, then there are no existing records to merge
        merging = false;
      }
      // eslint-disable-next-line prefer-const
      for (let [id, data] of Object.entries(item)) {
        if (merging) {
          const oldRecord = get(state, [type, id]);
          if (oldRecord) {
            if (
              this.hasProperty(oldRecord, '_jv')
              && this.hasProperty(oldRecord._jv, 'relationships')
            ) {
              Object.keys(oldRecord._jv.relationships).forEach(item => {
                if (this.hasProperty(data, '_jv') && this.hasProperty(data._jv, 'relationships')) {
                  if (this.hasProperty(data._jv.relationships, item)) {
                    oldRecord._jv.relationships[item] = data._jv.relationships[item];
                  }
                }
              });
            }
            if (data._jv.type === 'users') {
              data.avatarUrl = oldRecord.avatarUrl;
            }
            data = merge(oldRecord, data);
          }
        }
        Vue.set(state[type], id, data);
      }
    }
  }
};

/**
 * A class for tracking the status of actions.
 *
 * **Note** - an instance of this class is exported as `status` from `jsonapi-vuex`,
 * so it does not need to be used directly.
 *
 * @namespace status
 * @memberof module:jsonapi-vuex
 * @param {object} maxId=-1 - Limit ID 'history' to N items (default is unlimited).
 */
const ActionStatus = class {
  constructor(maxID = -1) {
    this.PENDING = 0;
    this.SUCCESS = 1;
    this.ERROR = -1;
    this.maxID = maxID || -1;
    this.status = {};
    this.counter = 0;
  }

  _count() {
    if (this.counter === this.maxID) {
      this.counter = 0;
    }
    return ++this.counter;
  }

  /**
   * This method:
   * - Creates a new ID property in the class' `status` object.
   * - Sets status to PENDING
   * - Calls the provided function
   * - Attaches then/catch blocks to the promise which will set status to SUCCESS/ERROR
   *
   * @memberof module:jsonapi-vuex.status
   * @params {function} func - A function to be executed which returns a promised
   * @returns {integer} The status ID for this function.
   */
  run(func) {
    const id = this._count();
    this.status[id] = this.PENDING;
    const promise = new Promise((resolve, reject) => {
      func()
        .then(result => {
          this.status[id] = this.SUCCESS;
          resolve(result);
        })
        .catch(error => {
          this.status[id] = this.ERROR;
          reject(error);
        });
    });
    promise._statusID = id;
    return promise;
  }
};

export { Utils, ActionStatus };
