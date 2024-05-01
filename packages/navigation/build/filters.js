"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");
Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.flattenFilters = flattenFilters;
exports.getActiveFiltersFromQuery = getActiveFiltersFromQuery;
exports.getDefaultOptionValue = getDefaultOptionValue;
exports.getQueryFromActiveFilters = getQueryFromActiveFilters;
exports.getUrlKey = getUrlKey;
var _defineProperty2 = _interopRequireDefault(require("@babel/runtime/helpers/defineProperty"));
var _toConsumableArray2 = _interopRequireDefault(require("@babel/runtime/helpers/toConsumableArray"));
var _lodash = require("lodash");
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { (0, _defineProperty2.default)(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; } /** @format */ /**
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         * External dependencies
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         */
/**
 * Collapse an array of filter values with subFilters into a 1-dimensional array.
 *
 * @param {Array} filters Set of filters with possible subfilters.
 * @return {Array} Flattened array of all filters.
 */
function flattenFilters(filters) {
  var allFilters = [];
  filters.forEach(function (f) {
    if (!f.subFilters) {
      allFilters.push(f);
    } else {
      allFilters.push((0, _lodash.omit)(f, 'subFilters'));
      var subFilters = flattenFilters(f.subFilters);
      allFilters.push.apply(allFilters, (0, _toConsumableArray2.default)(subFilters));
    }
  });
  return allFilters;
}

/**
 * Describe activeFilter object.
 *
 * @typedef {Object} activeFilter
 * @property {string} key - filter key.
 * @property {string} [rule] - a modifying rule for a filter, eg 'includes' or 'is_not'.
 * @property {string} value - filter value(s).
 */

/**
 * Given a query object, return an array of activeFilters, if any.
 *
 * @param {object} query - query oject
 * @param {object} config - config object
 * @return {activeFilters[]} - array of activeFilters
 */
function getActiveFiltersFromQuery(query, config) {
  return (0, _lodash.compact)(Object.keys(config).map(function (configKey) {
    var filter = config[configKey];
    if (filter.rules) {
      var match = (0, _lodash.find)(filter.rules, function (rule) {
        return query.hasOwnProperty(getUrlKey(configKey, rule.value));
      });
      if (match) {
        var value = query[getUrlKey(configKey, match.value)];
        return {
          key: configKey,
          rule: match.value,
          value: value
        };
      }
      return null;
    }
    if (query[configKey]) {
      return {
        key: configKey,
        value: query[configKey]
      };
    }
    return null;
  }));
}

/**
 * Get the default option's value from the configuration object for a given filter. The first
 * option is used as default if no `defaultOption` is provided.
 *
 * @param {object} config - a filter config object.
 * @param {array} options - select options.
 * @return {string|undefined}  - the value of the default option.
 */
function getDefaultOptionValue(config, options) {
  var defaultOption = config.input.defaultOption;
  if (config.input.defaultOption) {
    var option = (0, _lodash.find)(options, {
      value: defaultOption
    });
    if (!option) {
      /* eslint-disable no-console */
      console.warn("invalid defaultOption ".concat(defaultOption, " supplied to ").concat(config.labels.add));
      /* eslint-enable */
      return undefined;
    }
    return option.value;
  }
  return (0, _lodash.get)(options, [0, 'value']);
}

/**
 * Given activeFilters, create a new query object to update the url. Use previousFilters to
 * Remove unused params.
 *
 * @param {activeFilters[]} activeFilters - activeFilters shown in the UI
 * @param {object} query - the current url query object
 * @param {object} config - config object
 * @return {object} - query object representing the new parameters
 */
function getQueryFromActiveFilters(activeFilters, query, config) {
  var previousFilters = getActiveFiltersFromQuery(query, config);
  var previousData = previousFilters.reduce(function (data, filter) {
    data[getUrlKey(filter.key, filter.rule)] = undefined;
    return data;
  }, {});
  var nextData = activeFilters.reduce(function (data, filter) {
    if ('between' === filter.rule && (!Array.isArray(filter.value) || filter.value.some(function (value) {
      return !value;
    }))) {
      return data;
    }
    if (filter.value) {
      data[getUrlKey(filter.key, filter.rule)] = filter.value;
    }
    return data;
  }, {});
  return _objectSpread(_objectSpread({}, previousData), nextData);
}

/**
 * Get the url query key from the filter key and rule.
 *
 * @param {string} key - filter key.
 * @param {string} rule - filter rule.
 * @return {string} - url query key.
 */
function getUrlKey(key, rule) {
  if (rule && rule.length) {
    return "".concat(key, "_").concat(rule);
  }
  return key;
}
//# sourceMappingURL=filters.js.map