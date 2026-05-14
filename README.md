# hiqdev/rdap

PHP library implementing core RDAP objects, serialization, and provider interfaces according to [RFC 7483](https://tools.ietf.org/html/rfc7483) / [RFC 9083](https://tools.ietf.org/html/rfc9083).

## Requirements

- PHP 8.1+
- `symfony/serializer`

## Installation

```bash
composer require hiqdev/rdap:dev-master
```

## Architecture

The library is split into two layers:

**`Domain/`** — pure protocol objects with no external dependencies:

| Namespace | Contents |
|---|---|
| `Domain/Entity` | `Domain`, `Entity`, `Nameserver`, `AutNum`, `IPNetwork`, `VCard` |
| `Domain/ValueObject` | `DomainName`, `Event`, `Link`, `Notice`, `SecureDNS`, `IpAddresses`, … |
| `Domain/Constant` | Native PHP 8.1 enums: `Role`, `Status`, `EventAction`, `ObjectClassName` |

**`Infrastructure/`** — interfaces and default implementations for building RDAP responses:

| Namespace | Contents |
|---|---|
| `Infrastructure/Provider` | Builder interfaces and implementations (see below) |
| `Infrastructure/Storage` | `DomainInfoStorageInterface` |
| `Infrastructure/DTO` | `DomainData`, `ContactData`, `DnsSecData` |
| `Infrastructure/Query` | `DomainNamesQuery` |
| `Infrastructure/Serialization` | `SerializerInterface`, Symfony implementation |

## Builders

`DomainBuilder` assembles a `Domain` object from raw DTOs. It delegates to four focused sub-builders, each with its own interface:

| Interface | Default implementation | Responsibility |
|---|---|---|
| `ContactBuilderInterface` | `ContactBuilder` | Builds `Entity[]` with `VCard` from `ContactData[]` |
| `RegistrarBuilderInterface` | `RegistrarBuilder` | Builds the registrar + abuse `Entity` from env vars |
| `NoticeBuilderInterface` | `NoticeBuilder` | Builds `Notice[]` with `Link` from a config array |
| `SecureDnsBuilderInterface` | `SecureDnsBuilder` | Builds a `SecureDNS` object from `DnsSecData[]` |

All four interfaces are injected into `DomainBuilder` via constructor, so any implementation can be swapped through DI.

### Implementing a custom builder

```php
use hiqdev\rdap\core\Infrastructure\Provider\RegistrarBuilderInterface;
use hiqdev\rdap\core\Domain\Entity\Entity;

final class MyRegistrarBuilder implements RegistrarBuilderInterface
{
    public function build(): Entity
    {
        $entity = new Entity();
        // populate from your own source
        return $entity;
    }
}
```

## Domain repository interfaces

To feed data into `DomainProvider`, implement the three repository interfaces:

| Interface | Method | Returns |
|---|---|---|
| `DomainRepositoryInterface` | `findDomainByName(string)` | `DomainData` |
| `DomainNamesRepositoryInterface` | `getDomainNames(DomainNamesQuery)` | `iterable<DomainName>` |
| `UpdateDomainInterface` | `setSuccessUpdateStatus(string)` | `void` |

## Storage interface

`DomainInfoStorageInterface` abstracts where serialized RDAP JSON is kept:

```php
interface DomainInfoStorageInterface
{
    public function save(string $domainName, string $json): void;
    public function find(string $domainName): ?string;
    public function delete(string $domainName): void;
    public function removeNotUpdatedSince(\DateTimeImmutable $threshold): void;
}
```

## DTOs

### `DomainData`

Constructed with raw strings from the database. Date fields are converted to `DateTimeImmutable` (UTC) internally:

```php
$data = new DomainData(
    handle: 'ABC123',
    statuses: 'clientTransferProhibited,serverHold',
    nameservers: 'ns1.example.com,ns2.example.com',
    creationDate: '2010-01-15 12:00:00',
    updatedDate: '2024-06-01 08:30:00',
    registrarExpiration: '2025-01-15 00:00:00',
    expiration: '2025-01-15 00:00:00',
    whoisProtected: false,
    delegationSigned: true,
);

$data->getCreationDate(); // DateTimeImmutable (UTC)
```

### `ContactData`

Holds registrant/admin/tech contact fields. `isWhoisProtected()` signals that personal data should be redacted in the RDAP response.

## Serialization

`SymfonySerializer` converts a `Domain` entity to RDAP-compliant JSON. Dates are serialized with a `Z` suffix (UTC).

```php
$serializer = new SymfonySerializer();
$json = $serializer->serialize($domain); // RFC 9083 JSON string
```

## Running tests

```bash
php vendor/bin/phpunit
```

## License

BSD-3-Clause. Copyright © HiQDev.
