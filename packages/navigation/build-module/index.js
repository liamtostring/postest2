import _defineProperty from "@babel/runtime/helpers/esm/defineProperty";
import _typeof from "@babel/runtime/helpers/esm/typeof";
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
/** @format */
/**
 * External dependencies
 */
import { addQueryArgs } from '@wordpress/url';
import { parse } from 'qs';
import { pick, uniq } from 'lodash';
import { applyFilters } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import { getHistory } from './history';

// Expose history so all uses get the same history object.
export { getHistory };

// Export all filter utilities
export * from './filters';

/**
 * Internal dependencies
 */
// Import the module into itself. Functions consumed from this import can be mocked in tests.
import * as navUtils from './index';

/**
 * Get the current path from history.
 *
 * @return {String}  Current path.
 */
export var getPath = function getPath() {
  return getHistory().location.pathname;
};

/**
 * Gets query parameters that should persist between screens or updates
 * to reports, such as filtering.
 *
 * @param {Object} query Query containing the parameters.
 * @return {Object} Object containing the persisted queries.
 */
export var getPersistedQuery = function getPersistedQuery() {
  var query = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : navUtils.getQuery();
  var params = applyFilters('woocommerce_admin_persisted_queries', ['period', 'compare', 'before', 'after', 'interval', 'type']);
  return pick(query, params);
};

/**
 * Get an array of IDs from a comma-separated query parameter.
 *
 * @param {string} queryString string value extracted from URL.
 * @return {Array} List of IDs converted to numbers.
 */
export function getIdsFromQuery() {
  var queryString = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
  return uniq(queryString.split(',').map(function (id) {
    return parseInt(id, 10);
  }).filter(Boolean));
}

/**
 * Get an array of searched words given a query.
 *
 * @param {Object} query Query object.
 * @return {Array} List of search words.
 */
export function getSearchWords() {
  var query = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : navUtils.getQuery();
  if (_typeof(query) !== 'object') {
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
export function getNewPath(query) {
  var path = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : getPath();
  var currentQuery = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : getQuery();
  var args = _objectSpread(_objectSpread({}, currentQuery), query);
  if ('/' !== path) {
    args.path = path;
  }
  return addQueryArgs('', args);
}

/**
 * Get the current query string, parsed into an object, from history.
 *
 * @return {Object}  Current query object, defaults to empty object.
 */
export function getQuery() {
  var search = getHistory().location.search;
  if (search.length) {
    return parse(search.substring(1)) || {};
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
export function onQueryChange(param) {
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
        return updateQueryString((_updateQueryString = {}, _defineProperty(_updateQueryString, queryParam, "compare-".concat(key)), _defineProperty(_updateQueryString, key, ids), _defineProperty(_updateQueryString, "search", undefined), _updateQueryString), path, query);
      };
    default:
      return function (value) {
        return updateQueryString(_defineProperty({}, param, value), path, query);
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
export function updateQueryString(query) {
  var path = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : getPath();
  var currentQuery = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : getQuery();
  var newPath = getNewPath(query, path, currentQuery);
  getHistory().push(newPath);
}
//# sourceMappingURL=index.js.map