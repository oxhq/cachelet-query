# cachelet-query

Query builder and Eloquent result caching with Cachelet coordinates.

`cachelet-query` makes repeated query results inspectable by table, connection, SQL, bindings, pagination inputs, store, prefix, and scope.

## Install

```bash
composer require oxhq/cachelet-query
```

## Best Fit

Use this package when expensive query results are repeated and the invalidation boundary can be expressed by table, family prefix, tag, or explicit scope.

It provides:

- `cachelet()` macros on query builders
- `rememberWithCachelet()` convenience macro
- SQL, binding, connection, and pagination-aware coordinates
- explicit `scope(...)` support
- canonical `module = query` coordinates and telemetry

## Example

```php
$results = User::query()
    ->where('role', 'admin')
    ->cachelet()
    ->ttl(300)
    ->rememberQuery();
```

## Contract

`cachelet-query` guarantees explicit invalidation by query-table prefix and tags. It does not claim automatic relational invalidation for arbitrary query graphs in `0.2.x`.

## Docs

- [`../../docs/operations.md`](../../docs/operations.md)
- [`../../docs/comparison.md`](../../docs/comparison.md)
- [`../../docs/migration.md`](../../docs/migration.md)
