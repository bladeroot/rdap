<?php
declare(strict_types=1);

namespace hiqdev\rdap\core\Domain\Entity;

/**
 * Trait TopMostEntityTrait
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
trait TopMostEntityTrait
{
    /**
     * @var string[] The data structure named "rdapConformance" is an array of strings,
     * each providing a hint as to the specifications used in the construction of the response.
     * This data structure appears only in the topmost JSON object of a response.
     */
    private $rdapConformance = [
        'rdap_level_0',
        'icann_rdap_technical_implementation_guide_1',
        'icann_rdap_response_profile_1',
    ];

    /**
     * @return string[]
     */
    public function getRdapConformance(): array
    {
        return $this->rdapConformance;
    }

    /**
     * @param string[] $rdapConformance
     */
    public function setRdapConformance(array $rdapConformance): void
    {
        $this->rdapConformance = $rdapConformance;
    }

    /**
     * @param string $rdapConformance
     */
    public function addRdapConformance(string $rdapConformance): void
    {
        $this->rdapConformance[] = $rdapConformance;
    }
}
