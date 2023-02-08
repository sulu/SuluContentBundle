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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Traits;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

trait ResolveContentDimensionUrlTrait
{
    /**
     * @param mixed[] $data
     */
    protected function getUrl(DimensionContentInterface $dimensionContent, array $data): ?string
    {
        if (!$dimensionContent instanceof TemplateInterface) {
            // TODO FIXME add testcase for it
            return null; // @codeCoverageIgnore
        }

        $type = $dimensionContent::getTemplateType();
        $template = $dimensionContent->getTemplateKey();

        $metadata = $this->getStructureMetadataFactory()->getStructureMetadata($type, $template);

        if (!$metadata) {
            // TODO FIXME add testcase for it
            return null; // @codeCoverageIgnore
        }

        foreach ($metadata->getProperties() as $property) {
            if ('route' === $property->getType()) {
                return $dimensionContent->getTemplateData()[$property->getName()] ?? null;
            }
        }

        return null;
    }

    abstract protected function getStructureMetadataFactory(): StructureMetadataFactoryInterface;
}
