<?php

namespace Oxhq\Cachelet\Query\Builders;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Oxhq\Cachelet\Contracts\CacheletBuilderInterface;
use Oxhq\Cachelet\Facades\Cachelet;
use Oxhq\Cachelet\Query\Support\QueryPayloadFactory;
use Oxhq\Cachelet\ValueObjects\CacheCoordinate;
use Oxhq\Cachelet\ValueObjects\CacheScope;

class QueryCacheletBuilder implements CacheletBuilderInterface
{
    protected CacheletBuilderInterface $builder;

    protected string $inferredScopeIdentifier;

    protected bool $hasExplicitScope = false;

    public function __construct(
        protected EloquentBuilder|BaseBuilder $query,
        protected QueryPayloadFactory $payloadFactory,
        ?string $prefix = null,
    ) {
        $resolvedPrefix = $prefix ?? $this->payloadFactory->prefixFor($query);

        $builder = Cachelet::for($resolvedPrefix)
            ->from($this->payloadFactory->make($query))
            ->withTags($this->payloadFactory->tagsFor($query))
            ->withMetadata([
                'connection' => $this->connectionName(),
                'table' => $this->payloadFactory->tableFor($query),
            ]);

        $builder->asModule('query');

        $this->builder = $builder;
        $this->inferredScopeIdentifier = $resolvedPrefix;
        $this->applyInferredScope();
    }

    public function from(mixed $payload): static
    {
        $this->builder->from($payload);

        return $this;
    }

    public function ttl(null|int|string|\DateTimeInterface|Closure $ttl): static
    {
        $this->builder->ttl($ttl);

        return $this;
    }

    public function withTags(string|array $tags): static
    {
        $this->builder->withTags($tags);

        return $this;
    }

    public function withMetadata(array $metadata): static
    {
        $this->builder->withMetadata($metadata);

        return $this;
    }

    public function onStore(?string $store): static
    {
        $this->builder->onStore($store);

        return $this;
    }

    public function scope(CacheScope $scope): static
    {
        $this->hasExplicitScope = true;
        $this->builder->scope($scope);

        return $this;
    }

    public function withInferredScope(CacheScope $scope): static
    {
        if (! $this->hasExplicitScope) {
            $this->builder->withInferredScope($scope);
        }

        return $this;
    }

    public function versioned(?string $version = null): static
    {
        $this->builder->versioned($version);

        return $this;
    }

    public function only(array $fields): static
    {
        $this->builder->only($fields);

        return $this;
    }

    public function exclude(array $fields): static
    {
        $this->builder->exclude($fields);

        return $this;
    }

    public function key(): string
    {
        return $this->builder->key();
    }

    public function duration(): ?int
    {
        return $this->builder->duration();
    }

    public function fetch(?Closure $callback = null): mixed
    {
        return $this->builder->fetch($callback ?? $this->defaultCallback());
    }

    public function remember(Closure $callback): mixed
    {
        return $this->builder->remember($callback);
    }

    public function rememberForever(Closure $callback): mixed
    {
        return $this->builder->rememberForever($callback);
    }

    public function staleWhileRevalidate(Closure $callback, ?Closure $fallback = null): mixed
    {
        return $this->builder->staleWhileRevalidate($callback, $fallback);
    }

    public function invalidate(): void
    {
        $this->builder->invalidate();
    }

    public function invalidatePrefix(string $reason = 'manual'): array
    {
        return $this->builder->invalidatePrefix($reason);
    }

    public function coordinate(): CacheCoordinate
    {
        return $this->builder->coordinate();
    }

    public function rememberQuery(): mixed
    {
        return $this->builder->remember($this->defaultCallback());
    }

    public function rememberQueryForever(): mixed
    {
        return $this->builder->rememberForever($this->defaultCallback());
    }

    public function staleQuery(?Closure $fallback = null): mixed
    {
        return $this->builder->staleWhileRevalidate($this->defaultCallback(), $fallback);
    }

    protected function defaultCallback(): Closure
    {
        $builder = $this->query;

        return static function () use ($builder): mixed {
            $clone = clone $builder;

            return $clone->get();
        };
    }

    protected function baseQuery(): BaseBuilder
    {
        return $this->query instanceof EloquentBuilder
            ? $this->query->getQuery()
            : $this->query;
    }

    protected function connectionName(): string
    {
        $connection = $this->baseQuery()->getConnection();

        if (method_exists($connection, 'getName')) {
            return (string) $connection->getName();
        }

        return (string) $connection->getDatabaseName();
    }

    protected function applyInferredScope(): void
    {
        if ($this->hasExplicitScope) {
            return;
        }

        $scope = $this->makeInferredScope($this->inferredScopeIdentifier);

        $this->builder->withInferredScope($scope);
    }

    protected function makeInferredScope(string $identifier): ?CacheScope
    {
        return CacheScope::inferred($identifier);
    }
}
