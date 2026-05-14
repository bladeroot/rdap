<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Domain\Constant;

enum Role: string
{
    case REGISTRANT     = 'registrant';
    case TECHNICAL      = 'technical';
    case ADMINISTRATIVE = 'administrative';
    case ABUSE          = 'abuse';
    case BILLING        = 'billing';
    case REGISTRAR      = 'registrar';
    case RESELLER       = 'reseller';
    case SPONSOR        = 'sponsor';
    case PROXY          = 'proxy';
    case NOTIFICATIONS  = 'notifications';
    case NOC            = 'noc';

    public static function fromName(string $name): self
    {
        return constant('self::' . $name);
    }
}
