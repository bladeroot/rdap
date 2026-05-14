<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Query;

final class DomainNamesQuery
{
    /** @var bool|null */
    private $include;

    /** @var int|null */
    private $limit;

    /** @var array|null */
    private $domains;

    public function setIncludeNotChanged(?bool $include): self
    {
        $this->include = $include;
        return $this;
    }

    public function getIncludeNotChanged(): ?bool
    {
        return $this->include;
    }

    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setDomains(?array $domains = null): self
    {
        $this->domains = $domains;
        return $this;
    }

    public function getDomains(): ?array
    {
        return $this->domains;
    }
}
