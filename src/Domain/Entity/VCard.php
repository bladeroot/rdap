<?php

namespace hiqdev\rdap\core\Domain\Entity;

final class VCard implements \JsonSerializable
{
    /** @var array  */
    private $vCard;

    public function __construct(array $vCard)
    {
        $this->vCard = $vCard;
    }

    public function jsonSerialize()
    {
        return $this->vCard;
    }
}