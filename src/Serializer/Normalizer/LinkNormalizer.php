<?php

namespace App\Serializer\Normalizer;

use App\Entity\Link;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LinkNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
           private UrlGeneratorInterface $router
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $data['href']['qr_code'] = $this->router->generate('link.qrCode', [
            'link' => $object->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Link;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Link::class => true];
    }
}