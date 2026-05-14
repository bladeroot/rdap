<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Provider;

use hiqdev\rdap\core\Domain\Entity\Entity;
use hiqdev\rdap\core\Infrastructure\DTO\ContactData;

interface ContactBuilderInterface
{
    /**
     * @param ContactData[] $contacts
     * @return Entity[]
     */
    public function build(array $contacts): array;
}
