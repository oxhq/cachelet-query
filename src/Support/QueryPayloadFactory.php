<?php

namespace Oxhq\Cachelet\Query\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Http\Request;

class QueryPayloadFactory
{
    public function __construct(
        protected array $config = []
    ) {}

    public function make(EloquentBuilder|BaseBuilder $builder): array
    {
        $query = $builder instanceof EloquentBuilder ? $builder->getQuery() : $builder;
        $request = app()->bound('request') ? app('request') : null;

        return [
            'connection' => $this->connectionName($query),
            'from' => $query->from,
            'joins' => $this->joinTables($query),
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'pagination' => $this->paginationPayload($request),
        ];
    }

    public function prefixFor(EloquentBuilder|BaseBuilder $builder): string
    {
        $query = $builder instanceof EloquentBuilder ? $builder->getQuery() : $builder;
        $base = $this->config['query']['default_prefix'] ?? 'query';

        return $base.':'.$this->normalizeSegment((string) ($query->from ?: 'anonymous'));
    }

    public function tagsFor(EloquentBuilder|BaseBuilder $builder): array
    {
        $query = $builder instanceof EloquentBuilder ? $builder->getQuery() : $builder;
        $tags = ['query'];

        if ($query->from) {
            $tags[] = 'table:'.$query->from;
        }

        if ($builder instanceof EloquentBuilder) {
            $tags[] = 'model:'.get_class($builder->getModel());
        }

        return array_values(array_unique($tags));
    }

    public function tableFor(EloquentBuilder|BaseBuilder $builder): ?string
    {
        $query = $builder instanceof EloquentBuilder ? $builder->getQuery() : $builder;

        return $query->from ?: null;
    }

    protected function paginationPayload(?Request $request): array
    {
        if (! $request) {
            return [];
        }

        $keys = $this->config['query']['pagination_keys'] ?? ['cursor', 'page', 'per_page'];
        $payload = [];

        foreach ($keys as $key) {
            if ($request->query->has($key)) {
                $payload[$key] = $request->query($key);
            }
        }

        return $payload;
    }

    protected function joinTables(BaseBuilder $query): array
    {
        return collect($query->joins ?? [])
            ->map(static fn (object $join): ?string => $join->table ?? null)
            ->filter()
            ->values()
            ->all();
    }

    protected function connectionName(BaseBuilder $query): string
    {
        $connection = $query->getConnection();

        if (method_exists($connection, 'getName')) {
            return (string) $connection->getName();
        }

        return (string) $connection->getDatabaseName();
    }

    protected function normalizeSegment(string $value): string
    {
        $normalized = preg_replace('/[^A-Za-z0-9:_-]+/', '_', trim($value));
        $normalized = trim((string) $normalized, '_');

        return $normalized === '' ? 'query' : $normalized;
    }
}
