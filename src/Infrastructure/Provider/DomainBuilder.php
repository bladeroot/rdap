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

    public function __construct(string $whoisUrl, array $rdapConformance)
    {
        $this->whoisUrl        = $whoisUrl;
        $this->rdapConformance = $rdapConformance;
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

        $rdapUrl  = getenv('RDAP_URL');
        $selfLink = new Link($rdapUrl . (string)$domainName);
        $selfLink->setValue($rdapUrl . (string)$domainName);
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

        $this->addTermsNotice($domain);
        $this->addStatusCodesNotice($domain);
        $this->addRDDSInaccuracyComplaintFormNotice($domain);

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
                $contact->getProvince(),
                $wp ? '' : $contact->getPostalCode(),
                $contact->getCountry()
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
                getenv('REGISTRAR_PROVINCE') ?: '',
                getenv('REGISTRAR_ZIP') ?: '',
                getenv('REGISTRAR_COUNTRY') ?: ''
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

    private function addTermsNotice(Domain $domain): void
    {
        $link = new Link(getenv('TERMS_OF_USE') ?: '');
        $link->setType('text/html');
        $link->setValue(getenv('RDAP_URL') . (string)$domain->getLdhName());
        $link->setRel('terms-of-service');
        $domain->addNotice(new Notice('Terms of Service', ['Service subject to Terms of Use.'], [$link]));
    }

    private function addStatusCodesNotice(Domain $domain): void
    {
        $link = new Link('https://icann.org/epp');
        $link->setType('text/html');
        $link->setValue(getenv('RDAP_URL') . (string)$domain->getLdhName());
        $link->setRel('glossary');
        $domain->addNotice(new Notice(
            'Status Codes',
            ['For more information on domain status codes, please visit https://icann.org/epp'],
            [$link]
        ));
    }

    private function addRDDSInaccuracyComplaintFormNotice(Domain $domain): void
    {
        $link = new Link('https://icann.org/wicf');
        $link->setType('text/html');
        $link->setValue(getenv('RDAP_URL') . (string)$domain->getLdhName());
        $link->setRel('help');
        $domain->addNotice(new Notice(
            'RDDS Inaccuracy Complaint Form',
            ['URL of the ICANN RDDS Inaccuracy Complaint Form: https://icann.org/wicf'],
            [$link]
        ));
    }

    private function date(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value, new \DateTimeZone('UTC'));
    }
}
