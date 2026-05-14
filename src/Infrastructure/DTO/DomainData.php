<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\DTO;

final class DomainData
{
    /** @var string */
    private $handle;

    /** @var string|null comma-separated EPP status names */
    private $statuses;

    /** @var string|null comma-separated nameserver host names */
    private $nameservers;

    /** @var string */
    private $creationDate;

    /** @var string */
    private $updatedDate;

    /** @var string registrar expiration date */
    private $registrarExpiration;

    /** @var string registry expiration date */
    private $expiration;

    /** @var bool */
    private $whoisProtected;

    /** @var bool */
    private $delegationSigned;

    public function __construct(
        string $handle,
        ?string $statuses,
        ?string $nameservers,
        string $creationDate,
        string $updatedDate,
        string $registrarExpiration,
        string $expiration,
        bool $whoisProtected,
        bool $delegationSigned
    ) {
        $this->handle              = $handle;
        $this->statuses            = $statuses;
        $this->nameservers         = $nameservers;
        $this->creationDate        = $creationDate;
        $this->updatedDate         = $updatedDate;
        $this->registrarExpiration = $registrarExpiration;
        $this->expiration          = $expiration;
        $this->whoisProtected      = $whoisProtected;
        $this->delegationSigned    = $delegationSigned;
    }

    public function getHandle(): string { return $this->handle; }
    public function getStatuses(): ?string { return $this->statuses; }
    public function getNameservers(): ?string { return $this->nameservers; }
    public function getCreationDate(): string { return $this->creationDate; }
    public function getUpdatedDate(): string { return $this->updatedDate; }
    public function getRegistrarExpiration(): string { return $this->registrarExpiration; }
    public function getExpiration(): string { return $this->expiration; }
    public function isWhoisProtected(): bool { return $this->whoisProtected; }
    public function isDelegationSigned(): bool { return $this->delegationSigned; }
}
