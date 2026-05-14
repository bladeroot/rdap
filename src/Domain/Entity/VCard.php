<?php

namespace hiqdev\rdap\core\Domain\Entity;

final class VCard implements \JsonSerializable
{
    const VERSION = '4.0';

    /** @var array */
    private $availableProperties = [
        'version' => 'text',
        'fn' => 'text',
        'n' => 'text',
        'tel' => 'text',
        'email' => 'text',
        'org' => 'text',
        'adr' => 'text',
        'url'  => 'uri',
    ];

    /** @var array  */
    private $vCard;

    public function __construct(array $vCard = [])
    {
        $this->setVersion(self::VERSION);
        $this->vCard = array_merge($this->vCard, $vCard);
    }

    public function jsonSerialize(): mixed
    {
        return array_values($this->vCard);
    }

    public function setVersion(string $version): VCard
    {
        return $this->setProperty('version', $version);
    }

    public function setFullName(string $name, array $properties = []): VCard
    {
        return $this->setProperty('fn', $name, $properties);
    }

    public function setName(string $name, array $properties = []): VCard
    {
        return $this->setProperty('n', $name, $properties);
    }

    public function setEmail(string $email, array $properties = []): VCard
    {
        return $this->setProperty('email', $email, $properties);
    }

    public function setOrg(?string $org = null, array $properties = []): VCard
    {
        if (empty($org)) {
            return $this;
        }

        return $this->setProperty('org', $org, $properties);
    }

    public function setCompany(?string $company = null, array $properties = []): VCard
    {
        return $this->setOrg($company, $properties);
    }

    public function setUrl(string $url, array $properties = []): VCard
    {
        return $this->setProperty('url', $url, $properties);
    }

    public function setTel(string $tel, array $properties = []): VCard
    {
        if (empty($properties) || empty($properties['type'])) {
            $properties['type'] = 'voice';
        }

        return $this->setProperty('tel', $tel, $properties);
    }

    public function setAddress(
        string $street,
        string $city,
        string $zip,
        ?string $country = null,
        ?string $state = '',
        string $ext = '',
        string $name = '',
        array $properties = []
    ): VCard {
        $properties['cc'] = strtoupper($properties['cc'] ?? $country);

        $this->vCard['adr'] = [
            'adr',
            $properties,
            $this->availableProperties['adr'],
            [
                $name,
                $ext,
                $street,
                $city,
                $state,
                $zip,
                '',
            ],
        ];
        return $this;
    }

    /**
     * @return array
     */
    public function getVCard(): array
    {
        return $this->vCard;
    }

    protected function setProperty(string $property, string $value, array $properties = [], string $type = 'text'): VCard
    {
        $this->vCard[$property] = [
            $property,
            empty($properties) ? new \stdClass() : $properties,
            $this->availableProperties[$property] ?? $type,
            $value
        ];
        return $this;
    }
}
