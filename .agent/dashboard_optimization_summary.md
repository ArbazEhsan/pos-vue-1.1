# Dashboard Optimization Summary

## Overview
Significant performance optimizations were applied to the `DashboardController` by consolidating multiple database queries into single, efficient aggregation queries. This reduces the database load and improves page load times.

## Key Changes

### 1. Sales Statistics
- **Before:** 4 separate queries (`today`, `month`, `lastMonth`, `total`).
- **After:** 1 single query using conditional aggregation (`SUM(CASE WHEN ...)`).

### 2. Purchase Statistics
- **Before:** 4 separate queries.
- **After:** 1 single query.

### 3. Product Statistics
- **Before:** 3 separate queries (`total`, `lowStock`, `outOfStock`).
- **After:** 1 single query.

### 4. Account Statistics
- **Before:** 2 separate queries (`customers`, `vendors`).
- **After:** 1 single query.

### 5. Manufacturing Statistics
- **Before:** 2 separate queries.
- **After:** 1 single query.

### 6. Cash Flow Calculation
- **Before:** 
    - 2 queries to fetch Account IDs.
    - 4 queries to sum Ledger debits/credits using `whereIn`.
    - Total: 6 queries + PHP memory usage for IDs.
- **After:** 
    - 1 single query joining `ledgers` and `accounts` tables.
    - Calculates all 4 sums (Customer DR/CR, Vendor DR/CR) in one go.

## Performance Impact
- **Database Round-Trips:** Reduced from ~21 scalar queries to just 6.
- **Memory Usage:** Reduced by calculating aggregates in the database rather than hydrating models or arrays (especially for Account IDs).
- **Scalability:** The new approach handles large datasets much better as it doesn't load intermediate data into PHP.

## Code Details
The controller now uses `selectRaw` with `CASE` statements to perform conditional sums and counts directly within the database engine. `COALESCE` is used to ensure 0 is returned instead of NULL for empty results.

```php
// Example Optimization
$salesStats = Sale::selectRaw("
    COALESCE(SUM(CASE WHEN DATE(day) = ? THEN final ELSE 0 END), 0) as today_sales,
    ...
", [...])->first();
```
