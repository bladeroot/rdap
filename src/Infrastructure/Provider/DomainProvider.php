<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Provider;

use hiqdev\rdap\core\Domain\Entity\Domain;
use hiqdev\rdap\core\Domain\ValueObject\DomainName;

final class DomainProvider implements DomainProviderInterface
{
    /** @var DomainRepositoryInterface */
    private $repository;

    /** @var DomainBuilderInterface */
    private $builder;

    public function __construct(DomainRepositoryInterface $repository, DomainBuilderInterface $builder)
    {
        $this->repository = $repository;
        $this->builder    = $builder;
    }

    public function get(DomainName $domainName): Domain
    {
        $name       = (string)$domainName;
        $domainData = $this->repository->findDomainByName($name);

        $contactsData  = $this->repository->findContactsByDomainName($name);
        $secureDnsData = !empty($domainData['delegationsigned'])
            ? $this->repository->findSecDnsByDomainName($name)
            : null;

        return $this->builder->build($domainName, $domainData, $contactsData, $secureDnsData);
    }
}
