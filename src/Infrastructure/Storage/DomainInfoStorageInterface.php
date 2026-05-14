<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Storage;

interface DomainInfoStorageInterface
{
    public function save(string $domainName, string $json): void;

    public function find(string $domainName): ?string;
}
