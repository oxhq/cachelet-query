# cachelet-query

Read-only split of the Cachelet monorepo package at `packages/cachelet-query`.

Query builder and Eloquent integration for Cachelet.

## Install

```bash
composer require oxhq/cachelet-query
```

## Features

- `cachelet()` macros on query builders
- `rememberWithCachelet()` convenience macro
- SQL, bindings, connection, and pagination-aware coordinates
- Prefix invalidation helpers for table-scoped caches

## Example

```php
$results = User::query()
    ->where('role', 'admin')
    ->cachelet()
    ->ttl(300)
    ->rememberQuery();
```
