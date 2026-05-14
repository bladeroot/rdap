<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Provider;

use hiqdev\rdap\core\Domain\ValueObject\SecureDNS;
use hiqdev\rdap\core\Infrastructure\DTO\DnsSecData;

final class SecureDnsBuilder
{
    /**
     * @param DnsSecData[] $dsRows
     */
    public function build(array $dsRows): SecureDNS
    {
        $dsData = [];
        foreach ($dsRows as $row) {
            $dsData[] = [
                'keyTag'     => $row->getKeyTag(),
                'algorithm'  => $row->getAlgorithm(),
                'digestType' => $row->getDigestType(),
                'digest'     => $row->getDigest(),
            ];
        }

        return new SecureDNS(true, true, null, $dsData);
    }
}
