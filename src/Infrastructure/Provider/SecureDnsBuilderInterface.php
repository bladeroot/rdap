<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Provider;

use hiqdev\rdap\core\Domain\ValueObject\SecureDNS;
use hiqdev\rdap\core\Infrastructure\DTO\DnsSecData;

interface SecureDnsBuilderInterface
{
    /**
     * @param DnsSecData[] $dsRows
     */
    public function build(array $dsRows): SecureDNS;
}
