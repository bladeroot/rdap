<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Provider;

use hiqdev\rdap\core\Infrastructure\DTO\ContactData;
use hiqdev\rdap\core\Infrastructure\DTO\DnsSecData;
use hiqdev\rdap\core\Infrastructure\DTO\DomainData;
use hiqdev\rdap\core\Infrastructure\Exception\ObjectNotAvailableException;

interface DomainRepositoryInterface
{
    /** @throws ObjectNotAvailableException if domain was not found */
    public function findDomainByName(string $name): DomainData;

    /** @return ContactData[] */
    public function findContactsByDomainName(string $name): array;

    /** @return DnsSecData[] */
    public function findSecDnsByDomainName(string $name): array;
}
