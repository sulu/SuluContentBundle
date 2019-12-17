<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionNormalizer;

use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionNormalizer\Enhancer\NormalizeEnhancerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ContentProjectionNormalizer implements ContentProjectionNormalizerInterface
{
    /**
     * @var iterable<NormalizeEnhancerInterface>
     */
    private $enhancers;

    /**
     * @var NormalizerInterface
     */
    private $serializer;

    /**
     * @param iterable<NormalizeEnhancerInterface> $enhancers
     */
    public function __construct(
        iterable $enhancers,
        ?NormalizerInterface $serializer = null
    ) {
        $this->enhancers = $enhancers;
        $this->serializer = $serializer ?: $this->createSerializer();
    }

    public function normalize(ContentProjectionInterface $contentProjection): array
    {
        $ignoreAttributes = ['id'];

        foreach ($this->enhancers as $enhancer) {
            $ignoreAttributes = array_merge(
                $ignoreAttributes,
                $enhancer->getIgnoredAttributes($contentProjection)
            );
        }

        /** @var mixed[] $normalizedData */
        $normalizedData = $this->serializer->normalize($contentProjection, null, [
            'ignored_attributes' => $ignoreAttributes,
        ]);

        // The view should not be represented by its own id but the id of the content entity
        $normalizedData['id'] = $normalizedData['contentId'];
        unset($normalizedData['contentId']);

        foreach ($this->enhancers as $enhancer) {
            $normalizedData = $enhancer->enhance($contentProjection, $normalizedData);
        }

        ksort($normalizedData);

        return $normalizedData;
    }

    private function createSerializer(): NormalizerInterface
    {
        $normalizers = [new DateTimeNormalizer(), new GetSetMethodNormalizer()];

        return new Serializer($normalizers);
    }
}
