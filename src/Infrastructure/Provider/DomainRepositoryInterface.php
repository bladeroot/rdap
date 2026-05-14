<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Provider;

interface DomainRepositoryInterface
{
    /** Returns a flat associative array of domain-level fields. */
    public function findDomainByName(string $name): array;

    /** Returns an array of contact rows with role and contact data. */
    public function findContactsByDomainName(string $name): array;

    /**
     * Returns an array of DNSSEC dsData rows, or null when the domain
     * is not delegation-signed and no query should be made.
     */
    public function findSecDnsByDomainName(string $name): array;
}
