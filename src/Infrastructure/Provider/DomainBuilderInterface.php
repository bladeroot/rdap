<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Provider;

use hiqdev\rdap\core\Domain\Entity\Domain;
use hiqdev\rdap\core\Domain\ValueObject\DomainName;

interface DomainBuilderInterface
{
    /**
     * Assembles a fully populated Domain from raw data arrays.
     *
     * @param DomainName $domainName
     * @param array      $domainData    Row returned by DomainRepositoryInterface::findDomainByName()
     * @param array      $contactsData  Rows returned by DomainRepositoryInterface::findContactsByDomainId()
     * @param array|null $secureDnsData Rows returned by DomainRepositoryInterface::findSecDnsByDomainId(), or null
     */
    public function build(
        DomainName $domainName,
        array $domainData,
        array $contactsData,
        ?array $secureDnsData
    ): Domain;
}
