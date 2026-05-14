<?php
/**
 * Registration Data Access Protocol – core objects implementation package according to the RFC 7483
 *
 * @link      https://github.com/hiqdev/rdap
 * @package   rdap
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2019, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\rdap\core\tests\unit\Domain\Entity;

use hiqdev\rdap\core\Domain\Constant\EventAction;
use hiqdev\rdap\core\Domain\Constant\Role;
use hiqdev\rdap\core\Domain\Constant\Status;
use hiqdev\rdap\core\Domain\Entity\Domain;
use hiqdev\rdap\core\Domain\Entity\Entity;
use hiqdev\rdap\core\Domain\Entity\IPNetwork;
use hiqdev\rdap\core\Domain\Entity\Nameserver;
use hiqdev\rdap\core\Domain\ValueObject\DomainName;
use hiqdev\rdap\core\Domain\ValueObject\DomainVariant\Name;
use hiqdev\rdap\core\Domain\ValueObject\DomainVariant\Variant;
use hiqdev\rdap\core\Domain\ValueObject\Event;
use hiqdev\rdap\core\Domain\ValueObject\IpAddresses;
use hiqdev\rdap\core\Domain\ValueObject\Link;
use hiqdev\rdap\core\Domain\ValueObject\PublicId;
use hiqdev\rdap\core\Domain\ValueObject\SecureDNS;
use PHPUnit\Framework\TestCase;

class DomainTest extends TestCase
{
    public function testLdhName(): void
    {
        $domain = new Domain(DomainName::of('example.com'));
        $this->assertSame('example.com', (string) $domain->getLdhName());
    }

    public function testNameserver(): void
    {
        $domain = new Domain(DomainName::of('example.com'));
        $ns1 = new Nameserver(
            DomainName::of('ns1.example.com'),
            IpAddresses::getInstanceByInetAddr(['8.8.8.8', '1.1.1.1', 'beef:babe::1'])
        );
        $handle = 'handle';
        $ns1->setHandle($handle);
        $this->assertSame($handle, $ns1->getHandle());
        $ns2 = new Nameserver(
            DomainName::of('ns2.example.com'),
            IpAddresses::getInstanceByInetAddr(['8.8.8.8', '1.1.1.1', 'beef:babe::1'])
        );
        $domain->addNameserver($ns1);
        $domain->addNameserver($ns2);
        $this->assertSame([$ns1, $ns2], $domain->getNameservers());
    }

    public function testPublicId(): void
    {
        $domain = new Domain(DomainName::of('example.com'));
        $pubId1 = new PublicId('type', 'identifier');
        $pubId2 = new PublicId('type2', 'identifier2');
        $domain->addPublicId($pubId1);
        $domain->addPublicId($pubId2);
        $this->assertSame([$pubId1, $pubId2], $domain->getPublicIds());
    }

    public function testVariant(): void
    {
        $domain = new Domain(DomainName::of('example.com'));
        $name1 = new Name(DomainName::of('ns1.example.com'), DomainName::of('ns1.example.com'));
        $name2 = new Name(DomainName::of('ns2.example.com'), DomainName::of('ns2.example.com'));
        $variant1 = new Variant([], 'idnTable1', [$name1, $name2]);
        $variant2 = new Variant([], 'idnTable2', [$name1, $name2]);
        $domain->addVariant($variant1);
        $domain->addVariant($variant2);
        $this->assertSame([$variant1, $variant2], $domain->getVariants());
    }

    public function testSecureDNS(): void
    {
        $domain = new Domain(DomainName::of('example.com'));
        $eventArr = [
            Event::occurred(EventAction::LAST_CHANGED, new \DateTimeImmutable()),
        ];
        $linkArr = [
            new Link('scheme'),
        ];
        $dsData = [
            new SecureDNS\DSData($eventArr, $linkArr, 0, 0, 'digest', 0),
        ];
        $keyData = [
            new SecureDNS\KeyData($eventArr, $linkArr, 0, 'flags', 'protocol', 'pb_key'),
        ];
        $secureDns = new SecureDNS(true, true, 10, $dsData, $keyData);
        $domain->setSecureDNS($secureDns);
        $this->assertSame($secureDns, $domain->getSecureDNS());
    }

    public function testEntity(): void
    {
        $domain = new Domain(DomainName::of('example.com'));
        $entity1 = new Entity();
        $entity1->addStatus(Status::OK);
        $entity2 = new Entity();
        $entity2->addStatus(Status::LOCKED);
        $domain->addEntity($entity1);
        $domain->addEntity($entity2);
        $this->assertSame([$entity1, $entity2], $domain->getEntities());
    }

    public function testNetwork(): void
    {
        $domain = new Domain(DomainName::of('example.com'));
        $ipNetwork = new IPNetwork();
        $domain->setNetwork($ipNetwork);
        $this->assertSame($ipNetwork, $domain->getNetwork());
    }

    public function testHandle(): void
    {
        $domain = new Domain(DomainName::of('example.com'));
        $handle = 'handle';
        $domain->setHandle($handle);
        $this->assertSame($handle, $domain->getHandle());
    }

    public function testUnicodeDomain(): void
    {
        $domain = new Domain(DomainName::of('тест.укр'));

        $this->assertSame('xn--e1aybc.xn--j1amh', (string) $domain->getLdhName());
        $this->assertSame('тест.укр', (string) $domain->getLdhName()->toUnicode());
        $this->assertSame('xn--e1aybc.xn--j1amh', (string) $domain->getLdhName()->toLDH());

        $this->assertSame('тест.укр.', (string) $domain->getLdhName()->toUnicode()->toFQDN());
        $this->assertSame('xn--e1aybc.xn--j1amh.', (string) $domain->getLdhName()->toFQDN());
        $this->assertSame('xn--e1aybc.xn--j1amh.', (string) $domain->getLdhName()->toLDH()->toFQDN());
    }

    public function testMixedCaseDomain(): void
    {
        $domain = new Domain(DomainName::of('UPPERCASED.com'));
        $this->assertSame('uppercased.com', (string) $domain->getLdhName());

        $domain = new Domain(DomainName::of('ЗмішанаКапіталізація.укр'));
        $this->assertSame('змішанакапіталізація.укр', (string) $domain->getLdhName()->toUnicode());
        $this->assertSame('xn--80aaaaa1bevlem1a3byds8jrehdd.xn--j1amh', (string) $domain->getLdhName());
    }

    public function testDefaultRedactedRulesOnlyRegistrantAndTech(): void
    {
        $entity = new Entity();
        $entity->addRole(Role::fromName('REGISTRANT'));
        $entity->addRole(Role::fromName('ADMINISTRATIVE'));
        $entity->addRole(Role::fromName('TECHNICAL'));
        $entity->addRole(Role::fromName('BILLING'));

        $withHandle = new Entity();
        $withHandle->setHandle('REG-001');
        $withHandle->addRole(Role::fromName('REGISTRANT'));

        $domain = new Domain(DomainName::of('example.com'));
        $domain->addEntity($entity);
        $domain->addEntity($withHandle);
        $domain->setRedacted(false);

        $redacted = $domain->getRedacted();
        $types = array_column(array_column($redacted, 'name'), 'type');

        $this->assertContains('Registry Registrant ID', $types);
        $this->assertContains('Registry Tech ID', $types);
        $this->assertNotContains('Registry Admin ID', $types);
        $this->assertNotContains('Registry Billing ID', $types);

        foreach ($redacted as $entry) {
            $this->assertMatchesRegularExpression('/\.roles\[\d+\]==/', $entry['prePath']);
        }
    }

    public function testWPRedactedRulesRegistrantCompleteSet(): void
    {
        $entity = new Entity();
        $entity->addRole(Role::fromName('REGISTRANT'));

        $domain = new Domain(DomainName::of('example.com'));
        $domain->addEntity($entity);
        $domain->setRedacted(true);

        $redacted = $domain->getRedacted();
        $byType = [];
        foreach ($redacted as $r) {
            $byType[$r['name']['type']] = $r;
        }

        $base = "$.entities[?(@.roles[0]=='registrant')].vcardArray[1]";

        $this->assertArrayHasKey('Registrant Name', $byType);
        $this->assertSame("{$base}[?(@[0]=='fn')][3]", $byType['Registrant Name']['postPath']);
        $this->assertSame('emptyValue', $byType['Registrant Name']['method']);

        $this->assertArrayHasKey('Registrant Organization', $byType);
        $this->assertSame("{$base}[?(@[0]=='org')]", $byType['Registrant Organization']['prePath']);
        $this->assertSame('removal', $byType['Registrant Organization']['method']);

        $this->assertArrayHasKey('Registrant Street', $byType);
        $this->assertSame("{$base}[?(@[0]=='adr')][3][2]", $byType['Registrant Street']['postPath']);
        $this->assertSame('emptyValue', $byType['Registrant Street']['method']);

        $this->assertArrayHasKey('Registrant City', $byType);
        $this->assertSame("{$base}[?(@[0]=='adr')][3][3]", $byType['Registrant City']['postPath']);

        $this->assertArrayHasKey('Registrant Postal Code', $byType);
        $this->assertSame("{$base}[?(@[0]=='adr')][3][5]", $byType['Registrant Postal Code']['postPath']);

        $this->assertArrayHasKey('Registrant Phone', $byType);
        $this->assertSame("{$base}[?(@[1].type=='voice')]", $byType['Registrant Phone']['prePath']);
        $this->assertSame('removal', $byType['Registrant Phone']['method']);

        $this->assertArrayHasKey('Registrant Phone Ext', $byType);
        $this->assertSame("{$base}[?(@[1].type=='voice')]", $byType['Registrant Phone Ext']['prePath']);

        $this->assertArrayHasKey('Registrant Fax', $byType);
        $this->assertSame("{$base}[?(@[1].type=='fax')]", $byType['Registrant Fax']['prePath']);

        $this->assertArrayHasKey('Registrant Fax Ext', $byType);
        $this->assertSame("{$base}[?(@[1].type=='fax')]", $byType['Registrant Fax Ext']['prePath']);

        $this->assertArrayHasKey('Registrant Email', $byType);
        $this->assertSame("{$base}[?(@[0]=='email')][3]", $byType['Registrant Email']['postPath']);
        $this->assertSame('replacementValue', $byType['Registrant Email']['method']);

        $this->assertArrayNotHasKey('Registrant Province', $byType);
        $this->assertArrayNotHasKey('Registrant Tel', $byType);
        $this->assertArrayNotHasKey('Registrant E-Mail', $byType);
    }

    public function testWPRedactedRulesTechLimitedSet(): void
    {
        $entity = new Entity();
        $entity->addRole(Role::fromName('TECHNICAL'));

        $domain = new Domain(DomainName::of('example.com'));
        $domain->addEntity($entity);
        $domain->setRedacted(true);

        $redacted = $domain->getRedacted();
        $byType = [];
        foreach ($redacted as $r) {
            $byType[$r['name']['type']] = $r;
        }

        $base = "$.entities[?(@.roles[0]=='technical')].vcardArray[1]";

        $this->assertArrayHasKey('Tech Name', $byType);
        $this->assertSame("{$base}[?(@[0]=='fn')][3]", $byType['Tech Name']['postPath']);
        $this->assertSame('emptyValue', $byType['Tech Name']['method']);

        $this->assertArrayHasKey('Tech Phone', $byType);
        $this->assertSame("{$base}[?(@[1].type=='voice')]", $byType['Tech Phone']['prePath']);
        $this->assertSame('removal', $byType['Tech Phone']['method']);

        $this->assertArrayHasKey('Tech Phone Ext', $byType);
        $this->assertSame("{$base}[?(@[1].type=='voice')]", $byType['Tech Phone Ext']['prePath']);

        $this->assertArrayHasKey('Tech Email', $byType);
        $this->assertSame("{$base}[?(@[0]=='email')][3]", $byType['Tech Email']['postPath']);
        $this->assertSame('replacementValue', $byType['Tech Email']['method']);

        $this->assertArrayNotHasKey('Tech Street', $byType);
        $this->assertArrayNotHasKey('Tech City', $byType);
        $this->assertArrayNotHasKey('Tech Province', $byType);
        $this->assertArrayNotHasKey('Tech Postal Code', $byType);
        $this->assertArrayNotHasKey('Tech Tel', $byType);
        $this->assertArrayNotHasKey('Tech E-Mail', $byType);
    }

    public function testAdminAndBillingProduceNoRedactedEntries(): void
    {
        $admin = new Entity();
        $admin->addRole(Role::fromName('ADMINISTRATIVE'));

        $billing = new Entity();
        $billing->addRole(Role::fromName('BILLING'));

        $domain = new Domain(DomainName::of('example.com'));
        $domain->addEntity($admin);
        $domain->addEntity($billing);
        $domain->setRedacted(true);

        $redacted = $domain->getRedacted();
        $this->assertEmpty($redacted, 'Admin and Billing entities must not produce any redacted entries');
    }

    public function testMergedRegistrantTechEntityGeneratesBothBlocks(): void
    {
        // Same contact has both registrant (index 0) and technical (index 1) roles.
        // Both Registrant and Tech entries must be generated using the actual
        // role indices so @.roles[0]=='registrant' and @.roles[1]=='technical'
        // both correctly resolve to the same merged entity.
        $merged = new Entity();
        $merged->addRole(Role::fromName('REGISTRANT'));
        $merged->addRole(Role::fromName('TECHNICAL'));

        $domain = new Domain(DomainName::of('example.com'));
        $domain->addEntity($merged);
        $domain->setRedacted(true);

        $types = array_column(array_column($domain->getRedacted(), 'name'), 'type');

        $this->assertContains('Registrant Name', $types);
        $this->assertContains('Registrant Email', $types);
        $this->assertContains('Tech Name', $types);
        $this->assertContains('Tech Email', $types);
        $this->assertContains('Tech Phone', $types);

        $byType = [];
        foreach ($domain->getRedacted() as $r) {
            $byType[$r['name']['type']] = $r;
        }
        $this->assertMatchesRegularExpression("/@\.roles\[0\]=='registrant'/", $byType['Registrant Name']['postPath']);
        $this->assertMatchesRegularExpression("/@\.roles\[1\]=='technical'/", $byType['Tech Name']['postPath']);
    }

    public function testRdapConformance(): void
    {
        $domain = new Domain(DomainName::of('example.com'));
        $this->assertSame(['rdap_level_0'], $domain->getRdapConformance());

        $full = ['rdap_level_0', 'icann_rdap_technical_implementation_guide_1', 'icann_rdap_response_profile_1', 'redacted'];
        $domain->setRdapConformance($full);
        $this->assertSame($full, $domain->getRdapConformance());
    }
}
