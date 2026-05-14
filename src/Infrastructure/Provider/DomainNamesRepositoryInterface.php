<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Provider;

use hiqdev\rdap\core\Infrastructure\Query\DomainNamesQuery;

interface DomainNamesRepositoryInterface
{
    public function findNamesByQuery(DomainNamesQuery $query): array;
}
