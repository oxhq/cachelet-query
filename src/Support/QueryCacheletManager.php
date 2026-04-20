<?php

namespace Oxhq\Cachelet\Query\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Oxhq\Cachelet\Facades\Cachelet;
use Oxhq\Cachelet\Query\Builders\QueryCacheletBuilder;

class QueryCacheletManager
{
    public function __construct(
        protected array $config = [],
        protected ?QueryPayloadFactory $payloadFactory = null,
    ) {}

    public function for(EloquentBuilder|BaseBuilder $query, ?string $prefix = null): QueryCacheletBuilder
    {
        return new QueryCacheletBuilder($query, $this->payloadFactory(), $prefix);
    }

    public function prefixFor(EloquentBuilder|BaseBuilder|string $queryOrTable): string
    {
        if (is_string($queryOrTable)) {
            $base = $this->config['query']['default_prefix'] ?? 'query';

            return $base.':'.$queryOrTable;
        }

        return $this->payloadFactory()->prefixFor($queryOrTable);
    }

    public function invalidateTable(string $table, string $reason = 'manual'): array
    {
        return Cachelet::for($this->prefixFor($table))->invalidatePrefix($reason);
    }

    public function invalidateModel(Model|string $model, string $reason = 'manual'): array
    {
        $instance = is_string($model) ? new $model : $model;

        return $this->invalidateTable($instance->getTable(), $reason);
    }

    protected function payloadFactory(): QueryPayloadFactory
    {
        return $this->payloadFactory ??= new QueryPayloadFactory($this->config);
    }
}
