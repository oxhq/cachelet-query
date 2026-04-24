# cachelet-query

Read-only split of the Cachelet monorepo package at `packages/cachelet-query`.

Query builder and Eloquent integration for Cachelet.

## Install

```bash
composer require oxhq/cachelet-query
```

## Features

- `cachelet()` macros on query builders
- `scope(...)` for explicit intervention boundaries
- `rememberWithCachelet()` convenience macro
- SQL, bindings, connection, and pagination-aware coordinates
- Prefix invalidation helpers for table-scoped caches
- Canonical `module = query` coordinates and telemetry

## Example

```php
use Oxhq\Cachelet\ValueObjects\CacheScope;

$scope = /* CacheScope instance for the intervention boundary */;

$results = User::query()
    ->where('role', 'admin')
    ->cachelet()
    ->scope($scope)
    ->ttl(300)
    ->rememberQuery();
```

If you do not define a scope explicitly, `cachelet-query` infers one from the same query/table prefix boundary it already uses for grouped invalidation.

## Invalidation Contract

`cachelet-query` guarantees explicit invalidation by query-table prefix and tags. It does not claim perfect automatic relational invalidation in `0.2.x`.

Use it when:

- the cache key should follow SQL, bindings, connection, and pagination inputs
- invalidation can be expressed by table/model prefixes

Do not assume:

- relationship graph invalidation
- automatic invalidation for arbitrary side effects outside the cached query prefix
