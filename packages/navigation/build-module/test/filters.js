/** @format */

/**
 * Internal dependencies
 */
import { getActiveFiltersFromQuery, getDefaultOptionValue, getQueryFromActiveFilters, getUrlKey } from '../filters';
var config = {
  with_select: {
    labels: {
      add: 'Order Status'
    },
    rules: [{
      value: 'is'
    }],
    input: {
      component: 'SelectControl',
      options: [{
        value: 'pending'
      }]
    }
  },
  with_search: {
    labels: {
      add: 'Search'
    },
    rules: [{
      value: 'includes'
    }],
    input: {
      component: 'Search'
    }
  },
  with_no_rules: {
    labels: {
      add: 'Order Status'
    },
    input: {
      component: 'SelectControl',
      options: [{
        value: 'pending'
      }]
    }
  }
};
describe('getUrlKey', function () {
  it('should return a correctly formatted string', function () {
    var key = getUrlKey('key', 'rule');
    expect(key).toBe('key_rule');
  });
  it('should return a correctly formatted string with no rule', function () {
    var key = getUrlKey('key');
    expect(key).toBe('key');
  });
});
describe('getActiveFiltersFromQuery', function () {
  it('should return activeFilters from a query', function () {
    var query = {
      with_select_is: 'pending',
      with_search_includes: '1,2,3',
      with_no_rules: 'pending'
    };
    var activeFilters = getActiveFiltersFromQuery(query, config);
    expect(Array.isArray(activeFilters)).toBeTruthy();
    expect(activeFilters.length).toBe(3); // with_select

    var with_select = activeFilters[0];
    expect(with_select.key).toBe('with_select');
    expect(with_select.rule).toBe('is');
    expect(with_select.value).toBe('pending'); // with_search

    var with_search = activeFilters[1];
    expect(with_search.key).toBe('with_search');
    expect(with_search.rule).toBe('includes');
    expect(with_search.value).toEqual('1,2,3'); // with_search

    var with_no_rules = activeFilters[2];
    expect(with_no_rules.key).toBe('with_no_rules');
    expect(with_no_rules.rule).toBeUndefined();
    expect(with_no_rules.value).toEqual('pending');
  });
  it('should ignore irrelevant query parameters', function () {
    var query = {
      with_select: 'pending',
      // no rule associated
      status: 45
    };
    var activeFilters = getActiveFiltersFromQuery(query, config);
    expect(activeFilters.length).toBe(0);
  });
  it('should return an empty array with no relevant parameters', function () {
    var query = {};
    var activeFilters = getActiveFiltersFromQuery(query, config);
    expect(Array.isArray(activeFilters)).toBe(true);
    expect(activeFilters.length).toBe(0);
  });
});
describe('getQueryFromActiveFilters', function () {
  it('should return a query object from activeFilters', function () {
    var activeFilters = [{
      key: 'status',
      rule: 'is',
      value: 'open'
    }, {
      key: 'things',
      rule: 'includes',
      value: '1,2,3'
    }, {
      key: 'customer',
      value: 'new'
    }];
    var query = {};
    var nextQuery = getQueryFromActiveFilters(activeFilters, query, config);
    expect(nextQuery.status_is).toBe('open');
    expect(nextQuery.things_includes).toBe('1,2,3');
    expect(nextQuery.customer).toBe('new');
  });
  it('should remove parameters from the previous filters', function () {
    var activeFilters = [];
    var query = {
      with_select_is: 'complete',
      with_search_includes: '45'
    };
    var nextQuery = getQueryFromActiveFilters(activeFilters, query, config);
    expect(nextQuery.with_select_is).toBeUndefined();
    expect(nextQuery.with_search_includes).toBeUndefined();
  });
  it('should only reflect complete filters with multiple values', function () {
    var activeFilters = [{
      key: 'valid_date',
      rule: 'between',
      value: ['2018-04-04', '2018-04-10']
    }, {
      key: 'invalid_date_1',
      rule: 'between',
      value: ['2018-04-04', undefined]
    }, {
      key: 'invalid_date_2',
      rule: 'between',
      value: '2018-04-04'
    }];
    var query = {};
    var nextQuery = getQueryFromActiveFilters(activeFilters, query, config);
    expect(nextQuery.valid_date_between).toBeDefined();
    expect(nextQuery.invalid_date_1_between).toBeUndefined();
    expect(nextQuery.invalid_date_2_between).toBeUndefined();
  });
});
describe('getDefaultOptionValue', function () {
  it('should return the default option value', function () {
    var options = [{
      value: 'new'
    }, {
      value: 'returning'
    }];
    var currentFilter = {
      labels: {
        add: 'Customer Type'
      },
      input: {
        component: 'SelectControl',
        options: options,
        defaultOption: 'returning'
      }
    };
    var value = getDefaultOptionValue(currentFilter, options);
    expect(value).toBe('returning');
  });
  it('should return the first option value when no default option', function () {
    var options = [{
      value: 'new'
    }, {
      value: 'returning'
    }];
    var currentFilter = {
      labels: {
        add: 'Customer Type'
      },
      input: {
        component: 'SelectControl',
        options: options
      }
    };
    var value = getDefaultOptionValue(currentFilter, options);
    expect(value).toBe('new');
  });
  it('should return undefined when no options are provided', function () {
    var options = [];
    var currentFilter = {
      labels: {
        add: 'Product'
      },
      input: {
        component: 'Search'
      }
    };
    var value = getDefaultOptionValue(currentFilter, options);
    expect(value).toBeUndefined();
  });
});
//# sourceMappingURL=filters.js.map