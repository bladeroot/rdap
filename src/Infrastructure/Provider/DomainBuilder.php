<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Provider;

use DateTimeImmutable;
use hiqdev\rdap\core\Domain\Constant\EventAction;
use hiqdev\rdap\core\Domain\Constant\Role;
use hiqdev\rdap\core\Domain\Constant\Status;
use hiqdev\rdap\core\Domain\Entity\Domain;
use hiqdev\rdap\core\Domain\Entity\Entity;
use hiqdev\rdap\core\Domain\Entity\Nameserver;
use hiqdev\rdap\core\Domain\Entity\VCard;
use hiqdev\rdap\core\Domain\ValueObject\DomainName;
use hiqdev\rdap\core\Domain\ValueObject\Event;
use hiqdev\rdap\core\Domain\ValueObject\Link;
use hiqdev\rdap\core\Domain\ValueObject\Notice;
use hiqdev\rdap\core\Domain\ValueObject\PublicId;
use hiqdev\rdap\core\Domain\ValueObject\SecureDNS;
use hiqdev\rdap\core\Infrastructure\DTO\ContactData;
use hiqdev\rdap\core\Infrastructure\DTO\DnsSecData;
use hiqdev\rdap\core\Infrastructure\DTO\DomainData;
use hiqdev\rdap\core\Infrastructure\Provider\DomainBuilderInterface;

final class DomainBuilder implements DomainBuilderInterface
{
    /** @var string */
    private $whoisUrl;

    /** @var string[] */
    private $rdapConformance;

    /** @var string */
    private $rdapUrl;

    /** @var array */
    private $noticesConfig;

    public function __construct(string $whoisUrl, array $rdapConformance, string $rdapUrl, array $noticesConfig)
    {
        $this->whoisUrl      = $whoisUrl;
        $this->rdapConformance = $rdapConformance;
        $this->rdapUrl       = $rdapUrl;
        $this->noticesConfig = $noticesConfig;
    }

    /**
     * @param ContactData[] $contacts
     * @param DnsSecData[]|null $secureDnsData
     */
    public function build(
        DomainName $domainName,
        DomainData $domainData,
        array $contacts,
        ?array $secureDnsData
    ): Domain {
        $domain = new Domain($domainName);
        $domain->setRdapConformance($this->rdapConformance);
        $domain->setHandle($domainData->getHandle());

        $domainUrl = $this->rdapUrl . (string)$domainName;
        $selfLink  = new Link($domainUrl);
        $selfLink->setValue($domainUrl);
        $selfLink->setType('application/rdap+json');
        $selfLink->setRel('self');
        $domain->addLink($selfLink);

        if ($secureDnsData !== null) {
            $domain->setSecureDNS($this->buildSecureDns($secureDnsData));
        }

        foreach ($this->buildContactEntities($contacts) as $entity) {
            $domain->addEntity($entity);
        }
        $domain->addEntity($this->buildRegistrarEntity());

        $domain->addEvent(Event::occurred(EventAction::REGISTRATION,                 $this->date($domainData->getCreationDate())));
        $domain->addEvent(Event::occurred(EventAction::LAST_CHANGED,                 $this->date($domainData->getUpdatedDate())));
        $domain->addEvent(Event::occurred(EventAction::REGISTRAR_EXPIRATION,         $this->date($domainData->getRegistrarExpiration())));
        $domain->addEvent(Event::occurred(EventAction::EXPIRATION,                   $this->date($domainData->getExpiration())));
        $domain->addEvent(Event::occurred(EventAction::LAST_UPDATE_OF_RDAP_DATABASE, new DateTimeImmutable()));

        $statuses = $domainData->getStatuses();
        if (!empty($statuses)) {
            foreach (explode(',', $statuses) as $status) {
                $domain->addStatus(Status::fromName(strtoupper($status)));
            }
        } else {
            $domain->addStatus(Status::OK);
        }

        $nameservers = $domainData->getNameservers();
        if (!empty($nameservers)) {
            foreach (explode(',', $nameservers) as $host) {
                $domain->addNameserver(new Nameserver(DomainName::of($host)));
            }
        }

        $domain->setPort43(DomainName::of($this->whoisUrl));
        $domain->setLang(getenv('RDAP_LANG') ?: 'en');
        $domain->setRedacted($domainData->isWhoisProtected());

        $this->addNotices($domain, $domainUrl);

        return $domain;
    }

    /** @param ContactData[] $contacts */
    private function buildContactEntities(array $contacts): array
    {
        $rolesByContact = [];
        $vcardByContact = [];

        foreach ($contacts as $contact) {
            $id = $contact->getId();
            $rolesByContact[$id][] = Role::fromName(strtoupper($contact->getRole()));
            $vcardByContact[$id]   = $this->buildVCard($contact);
        }

        $entities = [];
        foreach ($vcardByContact as $id => $vcard) {
            $entity = new Entity();
            foreach ($rolesByContact[$id] as $role) {
                $entity->addRole($role);
            }
            $entity->addVcard($vcard);
            $entities[] = $entity;
        }

        return $entities;
    }

    private function buildVCard(ContactData $contact): VCard
    {
        $wp    = $contact->isWhoisProtected();
        $vcard = (new VCard())
            ->setCompany($wp ? '' : $contact->getCompany())
            ->setFullName($wp ? '' : $contact->getFullName())
            ->setEmail($contact->getEmail())
            ->setAddress(
                $wp ? '' : $contact->getStreet(),
                $wp ? '' : $contact->getCity(),
                $wp ? '' : $contact->getPostalCode(),
                $contact->getCountry(),
                $contact->getProvince()
            );

        if (!$wp) {
            $vcard->setTel($contact->getPhone());
        }

        return $vcard;
    }

    private function buildRegistrarEntity(): Entity
    {
        $ianaId = getenv('REGISTRAR_IANAID') ?: '';
        $vcard  = (new VCard())
            ->setFullName(getenv('REGISTRAR_ORG') ?: '')
            ->setCompany(getenv('REGISTRAR_ORG') ?: '')
            ->setEmail(getenv('REGISTRAR_EMAIL') ?: '')
            ->setTel(getenv('REGISTRAR_PHONE') ?: '')
            ->setAddress(
                getenv('REGISTRAR_STREET') ?: '',
                getenv('REGISTRAR_CITY') ?: '',
                getenv('REGISTRAR_ZIP') ?: '',
                getenv('REGISTRAR_COUNTRY') ?: '',
                getenv('REGISTRAR_PROVINCE') ?: ''
            )
            ->setUrl(getenv('REGISTRAR_URL') ?: '');

        $entity = new Entity();
        $entity->addVcard($vcard);
        $entity->setHandle($ianaId);
        $entity->addPublicId(new PublicId('IANA Registrar ID', $ianaId));

        $link = new Link(getenv('REGISTRAR_URL') ?: '');
        $link->setValue(getenv('RDAP_SITE') ?: '');
        $link->setRel('about');
        $entity->addLink($link);
        $entity->addRole(Role::REGISTRAR);

        $abuseEntity = new Entity();
        $abuseEntity->addVcard($vcard);
        $abuseEntity->setHandle('ABUSE-' . $ianaId);
        $abuseEntity->addRole(Role::ABUSE);
        $entity->addEntity($abuseEntity);

        return $entity;
    }

    /** @param DnsSecData[] $dsRows */
    private function buildSecureDns(array $dsRows): SecureDNS
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

    private function addNotices(Domain $domain, string $currentUrl): void
    {
        foreach ($this->noticesConfig as $config) {
            $link = new Link($config['link']['href']);
            $link->setType('text/html');
            $link->setValue($currentUrl);
            $link->setRel($config['link']['rel']);
            $domain->addNotice(new Notice($config['title'], [$config['description']], [$link]));
        }
    }

    private function date(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value, new \DateTimeZone('UTC'));
    }
}
