<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\tests\unit\Domain\ValueObject;

use hiqdev\rdap\core\Domain\ValueObject\IpAddresses;
use hiqdev\rdap\core\Domain\ValueObject\IpV4Address;
use hiqdev\rdap\core\Domain\ValueObject\IpV6Address;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class IpAddressTest extends TestCase
{
    public function testIPv4Construction(): void
    {
        $ip = new IpV4Address('192.168.1.1');
        $this->assertSame('192.168.1.1', $ip->getHostAddress());
        $this->assertSame('192.168.1.1', (string) $ip);
    }

    public function testIPv4InvalidThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IpV4Address('999.999.999.999');
    }

    public function testIPv4RejectsIPv6(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IpV4Address('2001:db8::1');
    }

    public function testIPv6Construction(): void
    {
        $ip = new IpV6Address('2001:db8::1');
        $this->assertSame('2001:db8::1', $ip->getHostAddress());
        $this->assertSame('2001:db8::1', (string) $ip);
    }

    public function testIPv6InvalidThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IpV6Address('not-an-ip');
    }

    public function testIPv6RejectsIPv4(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IpV6Address('192.168.1.1');
    }

    public function testIpAddressesByProtocol(): void
    {
        $v4   = [new IpV4Address('1.2.3.4')];
        $v6   = [new IpV6Address('::1')];
        $ips  = IpAddresses::getInstanceByProtocol($v4, $v6);

        $this->assertCount(1, $ips->getV4());
        $this->assertCount(1, $ips->getV6());
        $this->assertSame('1.2.3.4', (string) $ips->getV4()[0]);
        $this->assertSame('::1', (string) $ips->getV6()[0]);
    }

    public function testIpAddressesByInetAddr(): void
    {
        $addresses = [
            new IpV4Address('10.0.0.1'),
            new IpV6Address('fe80::1'),
            new IpV4Address('10.0.0.2'),
        ];
        $ips = IpAddresses::getInstanceByInetAddr($addresses);

        $this->assertCount(2, $ips->getV4());
        $this->assertCount(1, $ips->getV6());
    }

    public function testIpAddressesEmpty(): void
    {
        $ips = IpAddresses::getInstanceByProtocol([], []);
        $this->assertSame([], $ips->getV4());
        $this->assertSame([], $ips->getV6());
    }
}
