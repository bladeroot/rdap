<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Domain\Constant;

enum ObjectClassName: string
{
    case ENTITY     = 'entity';
    case NAMESERVER = 'nameserver';
    case DOMAIN     = 'domain';
    case AUTNUM     = 'autnum';
    case IPNETWORK  = 'ipnetwork';
}
