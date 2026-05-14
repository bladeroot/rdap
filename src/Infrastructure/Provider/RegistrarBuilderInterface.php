<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Provider;

use hiqdev\rdap\core\Domain\Entity\Entity;

interface RegistrarBuilderInterface
{
    public function build(): Entity;
}
