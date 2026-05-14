<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Domain\Constant;

enum Relation: string
{
    case BASIC                   = 'basic';
    case REGISTERED              = 'registered';
    case UNREGISTERED            = 'unregistered';
    case RESTRICTED_REGISTRATION = 'restricted registration';
    case OPEN_REGISTRATION       = 'open registration';
    case CONJOINED               = 'conjoined';
}
