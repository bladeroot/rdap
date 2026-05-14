<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\tests\unit\Domain\ValueObject;

use ArgumentCountError;
use hiqdev\rdap\core\Domain\ValueObject\DomainName;
use hiqdev\rdap\core\Domain\ValueObject\Label\RootLabel;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DomainNameTest extends TestCase
{
    public function testParsesSimpleDomain(): void
    {
        $dn = DomainName::of('example.com');
        $this->assertSame('example.com', (string) $dn);
    }

    public function testCaseNormalization(): void
    {
        $dn = DomainName::of('EXAMPLE.COM');
        $this->assertSame('example.com', (string) $dn);
    }

    public function testGetLevelSize(): void
    {
        $dn = DomainName::of('sub.example.com');
        $this->assertSame(3, $dn->getLevelSize());
    }

    public function testGetTLDLabel(): void
    {
        $dn = DomainName::of('example.com');
        $this->assertSame('com', (string) $dn->getTLDLabel());
    }

    public function testToFQDN(): void
    {
        $dn   = DomainName::of('example.com');
        $fqdn = $dn->toFQDN();
        $this->assertTrue($fqdn->isFQDN());
        $this->assertSame('example.com.', (string) $fqdn);
    }

    public function testToFQDNIsIdempotent(): void
    {
        $dn = DomainName::of('example.com.');
        $this->assertSame($dn, $dn->toFQDN());
    }

    public function testIsFQDN(): void
    {
        $nonFqdn = DomainName::of('example.com');
        $fqdn    = DomainName::of('example.com.');

        $this->assertFalse($nonFqdn->isFQDN());
        $this->assertTrue($fqdn->isFQDN());
    }

    public function testFQDNLevelSizeExcludesRootLabel(): void
    {
        $fqdn = DomainName::of('example.com.');
        $this->assertSame(2, $fqdn->getLevelSize());
    }

    public function testGetLabels(): void
    {
        $dn     = DomainName::of('example.com');
        $labels = $dn->getLabels();
        $this->assertCount(2, $labels);
        $this->assertSame('example', (string) $labels[0]);
        $this->assertSame('com', (string) $labels[1]);
    }

    public function testIDNUnicodeToLDH(): void
    {
        $dn  = DomainName::of('тест.укр');
        $ldh = $dn->toLDH();
        $this->assertSame('xn--e1aybc.xn--j1amh', (string) $ldh);
    }

    public function testLDHToUnicode(): void
    {
        $dn      = DomainName::of('xn--e1aybc.xn--j1amh');
        $unicode = $dn->toUnicode();
        $this->assertSame('тест.укр', (string) $unicode);
    }

    public function testEquality(): void
    {
        $a = DomainName::of('Example.COM');
        $b = DomainName::of('example.com');
        $this->assertTrue($a->equals($b));
    }

    public function testInequalityDifferentDomains(): void
    {
        $a = DomainName::of('foo.com');
        $b = DomainName::of('bar.com');
        $this->assertFalse($a->equals($b));
    }

    public function testRootLabelOnlyInLastPosition(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DomainName::of('example..com');
    }
}
