<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\Infrastructure\Serialization\Symfony\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class EnumNormalizer implements NormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return [\BackedEnum::class => true];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \BackedEnum;
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        return $object->value;
    }
}
