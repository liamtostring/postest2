"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

var _defineProperty2 = _interopRequireDefault(require("@babel/runtime/helpers/defineProperty"));

var _index = require("../index");

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { (0, _defineProperty2.default)(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

jest.mock('../index', function () {
  return _objectSpread({}, require.requireActual('../index'), {
    getQuery: jest.fn().mockReturnValue({
      filter: 'advanced',
      product_includes: 127,
      period: 'year',
      compare: 'previous_year',
      after: '2018-02-01',
      before: '2018-01-01',
      interval: 'day',
      search: 'lorem'
    })
  });
});
describe('getPersistedQuery', function () {
  it("should return an empty object it the query doesn't contain any time related parameters", function () {
    var query = {
      filter: 'advanced',
      product_includes: 127
    };
    var persistedQuery = {};
    expect((0, _index.getPersistedQuery)(query)).toEqual(persistedQuery);
  });
  it('should return time related parameters', function () {
    var query = {
      filter: 'advanced',
      product_includes: 127,
      period: 'year',
      compare: 'previous_year',
      after: '2018-02-01',
      before: '2018-01-01',
      type: 'bar',
      interval: 'day'
    };
    var persistedQuery = {
      period: 'year',
      compare: 'previous_year',
      after: '2018-02-01',
      before: '2018-01-01',
      type: 'bar',
      interval: 'day'
    };
    expect((0, _index.getPersistedQuery)(query)).toEqual(persistedQuery);
  });
  it('should get the query from getQuery() when none is provided in the params', function () {
    var persistedQuery = {
      period: 'year',
      compare: 'previous_year',
      after: '2018-02-01',
      before: '2018-01-01',
      interval: 'day'
    };
    expect((0, _index.getPersistedQuery)()).toEqual(persistedQuery);
  });
});
describe('getSearchWords', function () {
  it('should get the search words from a query object', function () {
    var query = {
      search: 'lorem,dolor sit'
    };
    var searchWords = ['lorem', 'dolor sit'];
    expect((0, _index.getSearchWords)(query)).toEqual(searchWords);
  });
  it('should parse `%2C` as commas', function () {
    var query = {
      search: 'lorem%2Cipsum,dolor sit'
    };
    var searchWords = ['lorem,ipsum', 'dolor sit'];
    expect((0, _index.getSearchWords)(query)).toEqual(searchWords);
  });
  it('should return an empty array if the query has no `search` property', function () {
    var query = {};
    var searchWords = [];
    expect((0, _index.getSearchWords)(query)).toEqual(searchWords);
  });
  it('should use the persisted query when it receives no params', function () {
    var searchWords = ['lorem'];
    expect((0, _index.getSearchWords)()).toEqual(searchWords);
  });
  it('should throw an error if the param is not an object', function () {
    expect(function () {
      return (0, _index.getSearchWords)('lorem');
    }).toThrow(Error);
  });
  it('should throw an error if the `search` property is not a string', function () {
    var query = {
      search: new Object()
    };
    expect(function () {
      return (0, _index.getSearchWords)(query);
    }).toThrow(Error);
  });
});
//# sourceMappingURL=index.js.map