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

namespace Sulu\Bundle\ContentBundle\Content\Application\ViewResolver;

use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\Resolver\ResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ApiViewResolver implements ViewResolverInterface, ApiViewResolverInterface
{
    /**
     * @var iterable<ResolverInterface>
     */
    private $resolvers;

    /**
     * @var NormalizerInterface
     */
    private $serializer;

    /**
     * @param iterable<ResolverInterface> $resolvers
     */
    public function __construct(
        iterable $resolvers,
        ?NormalizerInterface $serializer = null
    ) {
        $this->resolvers = $resolvers;
        $this->serializer = $serializer ?: $this->createSerializer();
    }

    public function resolve(ContentViewInterface $contentView): array
    {
        $ignoreAttributes = ['id'];

        foreach ($this->resolvers as $resolver) {
            $ignoreAttributes = array_merge(
                $ignoreAttributes,
                $resolver->getIgnoredAttributes($contentView)
            );
        }

        /** @var mixed[] $viewData */
        $viewData = $this->serializer->normalize($contentView, null, [
            'ignored_attributes' => $ignoreAttributes,
        ]);

        // The view should not be represented by its own id but the id of the content entity
        $viewData['id'] = $viewData['contentId'];
        unset($viewData['contentId']);

        foreach ($this->resolvers as $resolver) {
            $viewData = $resolver->resolve($contentView, $viewData);
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
