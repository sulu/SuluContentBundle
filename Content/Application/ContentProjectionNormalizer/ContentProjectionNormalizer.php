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

use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionNormalizer\Helper\NormalizerHelperInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ContentProjectionNormalizer implements ContentProjectionNormalizerInterface
{
    /**
     * @var iterable<NormalizerHelperInterface>
     */
    private $helpers;

    /**
     * @var NormalizerInterface
     */
    private $serializer;

    /**
     * @param iterable<NormalizerHelperInterface> $helpers
     */
    public function __construct(
        iterable $helpers,
        ?NormalizerInterface $serializer = null
    ) {
        $this->helpers = $helpers;
        $this->serializer = $serializer ?: $this->createSerializer();
    }

    public function normalize(ContentProjectionInterface $contentProjection): array
    {
        $ignoreAttributes = ['id'];

        foreach ($this->helpers as $resolver) {
            $ignoreAttributes = array_merge(
                $ignoreAttributes,
                $resolver->getIgnoredAttributes($contentProjection)
            );
        }

        /** @var mixed[] $viewData */
        $viewData = $this->serializer->normalize($contentProjection, null, [
            'ignored_attributes' => $ignoreAttributes,
        ]);

        // The view should not be represented by its own id but the id of the content entity
        $viewData['id'] = $viewData['contentId'];
        unset($viewData['contentId']);

        foreach ($this->helpers as $helper) {
            $viewData = $helper->normalize($contentProjection, $viewData);
        }

        ksort($viewData);

        return $viewData;
    }

    private function createSerializer(): NormalizerInterface
    {
        $normalizers = [new DateTimeNormalizer(), new GetSetMethodNormalizer()];

        return new Serializer($normalizers);
    }
}
