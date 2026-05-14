<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\tests\unit\Domain\ValueObject;

use hiqdev\rdap\core\Domain\ValueObject\PublicId;
use PHPUnit\Framework\TestCase;

class PublicIdTest extends TestCase
{
    public function testConstruction(): void
    {
        $publicId = new PublicId('IANA Registrar ID', '1234');
        $this->assertSame('IANA Registrar ID', $publicId->getType());
        $this->assertSame('1234', $publicId->getIdentifier());
    }

    public function testDifferentTypes(): void
    {
        $publicId = new PublicId('ICANN ROID', 'EXAMPLE-REP');
        $this->assertSame('ICANN ROID', $publicId->getType());
        $this->assertSame('EXAMPLE-REP', $publicId->getIdentifier());
    }
}
