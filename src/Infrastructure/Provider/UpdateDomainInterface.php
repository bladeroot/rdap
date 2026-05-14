<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Provider;

interface UpdateDomainInterface
{
    public function setSuccessUpdateStatus(string $domainName): void;
}
