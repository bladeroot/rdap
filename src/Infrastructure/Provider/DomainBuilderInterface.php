<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Provider;

use hiqdev\rdap\core\Domain\Entity\Domain;
use hiqdev\rdap\core\Domain\ValueObject\DomainName;
use hiqdev\rdap\core\Infrastructure\DTO\ContactData;
use hiqdev\rdap\core\Infrastructure\DTO\DnsSecData;
use hiqdev\rdap\core\Infrastructure\DTO\DomainData;

interface DomainBuilderInterface
{
    /**
     * @param ContactData[] $contacts
     * @param DnsSecData[]|null $secureDnsData
     */
    public function build(
        DomainName $domainName,
        DomainData $domainData,
        array $contacts,
        ?array $secureDnsData
    ): Domain;
}
