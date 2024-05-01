"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");
Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.getHistory = getHistory;
var _defineProperty2 = _interopRequireDefault(require("@babel/runtime/helpers/defineProperty"));
var _history2 = require("history");
var _qs = require("qs");
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { (0, _defineProperty2.default)(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; } /** @format */ /**
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         * External dependencies
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         */
// See https://github.com/ReactTraining/react-router/blob/master/FAQ.md#how-do-i-access-the-history-object-outside-of-components

var _history;

/**
 * Recreate `history` to coerce React Router into accepting path arguments found in query
 * parameter `path`, allowing a url hash to be avoided. Since hash portions of the url are
 * not sent server side, full route information can be detected by the server.
 *
 * `<Router />` and `<Switch />` components use `history.location()` to match a url with a route.
 * Since they don't parse query arguments, recreate `get location` to return a `pathname` with the
 * query path argument's value.
 *
 * @returns {object} React-router history object with `get location` modified.
 */

function getHistory() {
  if (!_history) {
    var browserHistory = (0, _history2.createBrowserHistory)({
      basename: '/'
    });
    _history = {
      get length() {
        return browserHistory.length;
      },
      get action() {
        return browserHistory.action;
      },
      get location() {
        var location = browserHistory.location;
        var query = (0, _qs.parse)(location.search.substring(1));
        var pathname = query.path || location.pathname;
        return _objectSpread(_objectSpread({}, location), {}, {
          pathname: pathname
        });
      },
      createHref: function createHref() {
        for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
          args[_key] = arguments[_key];
        }
        return browserHistory.createHref.apply(browserHistory, args);
      },
      push: function push() {
        for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
          args[_key2] = arguments[_key2];
        }
        return browserHistory.push.apply(browserHistory, args);
      },
      replace: function replace() {
        for (var _len3 = arguments.length, args = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
          args[_key3] = arguments[_key3];
        }
        return browserHistory.replace.apply(browserHistory, args);
      },
      go: function go() {
        for (var _len4 = arguments.length, args = new Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
          args[_key4] = arguments[_key4];
        }
        return browserHistory.go.apply(browserHistory, args);
      },
      goBack: function goBack() {
        for (var _len5 = arguments.length, args = new Array(_len5), _key5 = 0; _key5 < _len5; _key5++) {
          args[_key5] = arguments[_key5];
        }
        return browserHistory.goBack.apply(browserHistory, args);
      },
      goForward: function goForward() {
        for (var _len6 = arguments.length, args = new Array(_len6), _key6 = 0; _key6 < _len6; _key6++) {
          args[_key6] = arguments[_key6];
        }
        return browserHistory.goForward.apply(browserHistory, args);
      },
      block: function block() {
        for (var _len7 = arguments.length, args = new Array(_len7), _key7 = 0; _key7 < _len7; _key7++) {
          args[_key7] = arguments[_key7];
        }
        return browserHistory.block.apply(browserHistory, args);
      },
      listen: function listen(listener) {
        var _this = this;
        return browserHistory.listen(function () {
          listener(_this.location, _this.action);
        });
      }
    };
  }
  return _history;
}
//# sourceMappingURL=history.js.map