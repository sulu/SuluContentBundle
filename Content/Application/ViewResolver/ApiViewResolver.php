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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ApiViewResolver implements ViewResolverInterface, ApiViewResolverInterface
{
    /**
     * @var NormalizerInterface
     */
    private $serializer;

    public function __construct(?NormalizerInterface $serializer = null)
    {
        if (null === $serializer) {
            $serializer = $this->createSerializer();
        }

        $this->serializer = $serializer;
    }

    public function resolve(ContentViewInterface $contentView): array
    {
        /** @var mixed[] $viewData */
        $viewData = $this->serializer->normalize($contentView, null, ['ignored_attributes' => ['id']]);

        // The view should not be represented by its own id but the id of the content entity
        $viewData['id'] = $viewData['contentId'];
        unset($viewData['contentId']);

        // normalize data for the sulu frontend
        if ($contentView instanceof TemplateInterface) {
            $viewData = array_merge($viewData, $viewData['templateData']);
            unset($viewData['templateData']);

            $viewData['template'] = $viewData['templateKey'];
            unset($viewData['templateKey']);
        }

        return $viewData;
    }

    private function createSerializer(): NormalizerInterface
    {
        $normalizers = [new GetSetMethodNormalizer()];

        return new Serializer($normalizers);
    }
}
