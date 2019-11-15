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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableInterface;
use Sulu\Bundle\RouteBundle\Generator\RouteGeneratorInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\PropertyMetadata;
use Sulu\Component\Content\Metadata\StructureMetadata;

class RouteMapper implements MapperInterface
{
    /**
     * @var StructureMetadataFactoryInterface
     */
    private $factory;

    /**
     * @var RouteGeneratorInterface
     */
    private $routeGenerator;

    /**
     * @var RouteManagerInterface
     */
    private $routeManager;

    public function __construct(
        StructureMetadataFactoryInterface $factory,
        RouteGeneratorInterface $routeGenerator,
        RouteManagerInterface $routeManager
    ) {
        $this->factory = $factory;
        $this->routeGenerator = $routeGenerator;
        $this->routeManager = $routeManager;
    }

    public function map(
        array $data,
        object $contentDimension,
        ?object $localizedContentDimension = null
    ): void {
        if (!$localizedContentDimension || !$localizedContentDimension instanceof RoutableInterface || !$localizedContentDimension instanceof ContentDimensionInterface) {
            return;
        }

        if (!isset($data['template'])) {
            throw new \RuntimeException('Expected "template" to be set in the data array.');
        }

        $template = $data['template'];
        $type = $localizedContentDimension->getTemplateType();

        $metadata = $this->factory->getStructureMetadata($type, $template);
        if (!$metadata) {
            return;
        }

        $property = $this->getRouteProperty($metadata);
        if (!$property) {
            return;
        }

        if (!$localizedContentDimension->getContentId()) {
            // FIXME the code only works if the content-dimension is flushed once and has a valid id

            return;
        }

        $locale = $localizedContentDimension->getDimension()->getLocale();
        if (!$locale) {
            return;
        }

        $routePath = $data[$property->getName()] ?? null;
        if (!$routePath) {
            $routePath = $this->routeGenerator->generate(
                $localizedContentDimension,
                ['route_schema' => '/{object.getTitle()}']
            );

            $localizedContentDimension->setTemplateData(
                array_merge(
                    $localizedContentDimension->getTemplateData(),
                    [$property->getName() => $routePath]
                )
            );
        }

        $this->routeManager->createOrUpdateByAttributes(
            $localizedContentDimension->getContentClass(),
            (string) $localizedContentDimension->getContentId(),
            $locale,
            $routePath
        );
    }

    private function getRouteProperty(StructureMetadata $metadata): ?PropertyMetadata
    {
        foreach ($metadata->getProperties() as $property) {
            if ('route' === $property->getType()) {
                return $property;
            }
        }

        return null;
    }
}
