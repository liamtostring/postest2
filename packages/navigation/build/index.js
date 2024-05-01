"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");
var _typeof3 = require("@babel/runtime/helpers/typeof");
Object.defineProperty(exports, "__esModule", {
  value: true
});
var _exportNames = {
  getPath: true,
  getPersistedQuery: true,
  getIdsFromQuery: true,
  getSearchWords: true,
  getNewPath: true,
  getQuery: true,
  onQueryChange: true,
  updateQueryString: true,
  getHistory: true
};
Object.defineProperty(exports, "getHistory", {
  enumerable: true,
  get: function get() {
    return _history.getHistory;
  }
});
exports.getIdsFromQuery = getIdsFromQuery;
exports.getNewPath = getNewPath;
exports.getPersistedQuery = exports.getPath = void 0;
exports.getQuery = getQuery;
exports.getSearchWords = getSearchWords;
exports.onQueryChange = onQueryChange;
exports.updateQueryString = updateQueryString;
var _defineProperty2 = _interopRequireDefault(require("@babel/runtime/helpers/defineProperty"));
var _typeof2 = _interopRequireDefault(require("@babel/runtime/helpers/typeof"));
var _url = require("@wordpress/url");
var _qs = require("qs");
var _lodash = require("lodash");
var _hooks = require("@wordpress/hooks");
var _history = require("./history");
var _filters = require("./filters");
Object.keys(_filters).forEach(function (key) {
  if (key === "default" || key === "__esModule") return;
  if (Object.prototype.hasOwnProperty.call(_exportNames, key)) return;
  if (key in exports && exports[key] === _filters[key]) return;
  Object.defineProperty(exports, key, {
    enumerable: true,
    get: function get() {
      return _filters[key];
    }
  });
});
var navUtils = _interopRequireWildcard(require("./index"));
function _getRequireWildcardCache(nodeInterop) { if (typeof WeakMap !== "function") return null; var cacheBabelInterop = new WeakMap(); var cacheNodeInterop = new WeakMap(); return (_getRequireWildcardCache = function _getRequireWildcardCache(nodeInterop) { return nodeInterop ? cacheNodeInterop : cacheBabelInterop; })(nodeInterop); }
function _interopRequireWildcard(obj, nodeInterop) { if (!nodeInterop && obj && obj.__esModule) { return obj; } if (obj === null || _typeof3(obj) !== "object" && typeof obj !== "function") { return { default: obj }; } var cache = _getRequireWildcardCache(nodeInterop); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (key !== "default" && Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { (0, _defineProperty2.default)(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; } /** @format */ /**
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         * External dependencies
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         */ /**
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             * Internal dependencies
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             */ // Expose history so all uses get the same history object.
// Export all filter utilities
/**
 * Internal dependencies
 */ // Import the module into itself. Functions consumed from this import can be mocked in tests.
/**
 * Get the current path from history.
 *
 * @return {String}  Current path.
 */
var getPath = function getPath() {
  return (0, _history.getHistory)().location.pathname;
};

/**
 * Gets query parameters that should persist between screens or updates
 * to reports, such as filtering.
 *
 * @param {Object} query Query containing the parameters.
 * @return {Object} Object containing the persisted queries.
 */
exports.getPath = getPath;
var getPersistedQuery = function getPersistedQuery() {
  var query = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : navUtils.getQuery();
  var params = (0, _hooks.applyFilters)('woocommerce_admin_persisted_queries', ['period', 'compare', 'before', 'after', 'interval', 'type']);
  return (0, _lodash.pick)(query, params);
};

/**
 * Get an array of IDs from a comma-separated query parameter.
 *
 * @param {string} queryString string value extracted from URL.
 * @return {Array} List of IDs converted to numbers.
 */
exports.getPersistedQuery = getPersistedQuery;
function getIdsFromQuery() {
  var queryString = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
  return (0, _lodash.uniq)(queryString.split(',').map(function (id) {
    return parseInt(id, 10);
  }).filter(Boolean));
}

/**
 * Get an array of searched words given a query.
 *
 * @param {Object} query Query object.
 * @return {Array} List of search words.
 */
function getSearchWords() {
  var query = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : navUtils.getQuery();
  if ((0, _typeof2.default)(query) !== 'object') {
    throw new Error('Invalid parameter passed to getSearchWords, it expects an object or no parameters.');
  }
  var search = query.search;
  if (!search) {
    return [];
  }
  if (typeof search !== 'string') {
    throw new Error("Invalid 'search' type. getSearchWords expects query's 'search' property to be a string.");
  }
  return search.split(',').map(function (searchWord) {
    return searchWord.replace('%2C', ',');
  });
}

/**
 * Return a URL with set query parameters.
 *
 * @param {Object} query object of params to be updated.
 * @param {String} path Relative path (defaults to current path).
 * @param {Object} currentQuery object of current query params (defaults to current querystring).
 * @return {String}  Updated URL merging query params into existing params.
 */
function getNewPath(query) {
  var path = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : getPath();
  var currentQuery = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : getQuery();
  var args = _objectSpread(_objectSpread({}, currentQuery), query);
  if ('/' !== path) {
    args.path = path;
  }
  return (0, _url.addQueryArgs)('', args);
}

/**
 * Get the current query string, parsed into an object, from history.
 *
 * @return {Object}  Current query object, defaults to empty object.
 */
function getQuery() {
  var search = (0, _history.getHistory)().location.search;
  if (search.length) {
    return (0, _qs.parse)(search.substring(1)) || {};
  }
  return {};
}

/**
 * This function returns an event handler for the given `param`
 *
 * @param {string} param The parameter in the querystring which should be updated (ex `page`, `per_page`)
 * @param {string} path Relative path (defaults to current path).
 * @param {string} query object of current query params (defaults to current querystring).
 * @return {function} A callback which will update `param` to the passed value when called.
 */
function onQueryChange(param) {
  var path = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : getPath();
  var query = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : getQuery();
  switch (param) {
    case 'sort':
      return function (key, dir) {
        return updateQueryString({
          orderby: key,
          order: dir
        }, path, query);
      };
    case 'compare':
      return function (key, queryParam, ids) {
        var _updateQueryString;
        return updateQueryString((_updateQueryString = {}, (0, _defineProperty2.default)(_updateQueryString, queryParam, "compare-".concat(key)), (0, _defineProperty2.default)(_updateQueryString, key, ids), (0, _defineProperty2.default)(_updateQueryString, "search", undefined), _updateQueryString), path, query);
      };
    default:
      return function (value) {
        return updateQueryString((0, _defineProperty2.default)({}, param, value), path, query);
      };
  }
}

/**
 * Updates the query parameters of the current page.
 *
 * @param {Object} query object of params to be updated.
 * @param {String} path Relative path (defaults to current path).
 * @param {Object} currentQuery object of current query params (defaults to current querystring).
 */
function updateQueryString(query) {
  var path = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : getPath();
  var currentQuery = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : getQuery();
  var newPath = getNewPath(query, path, currentQuery);
  (0, _history.getHistory)().push(newPath);
}
//# sourceMappingURL=index.js.map