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

namespace Sulu\Bundle\ContentBundle\Resolver;

use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;
use Sulu\Component\Content\Compat\StructureInterface;
use Sulu\Component\Content\Compat\StructureManagerInterface;
use Sulu\Component\Content\ContentTypeManagerInterface;

class ContentViewResolver implements ContentViewResolverInterface
{
    /**
     * @var StructureManagerInterface
     */
    private $structureManager;

    /**
     * @var ContentTypeManagerInterface
     */
    private $contentTypeManager;

    public function __construct(
        StructureManagerInterface $structureManager,
        ContentTypeManagerInterface $contentTypeManager
    ) {
        $this->structureManager = $structureManager;
        $this->contentTypeManager = $contentTypeManager;
    }

    public function resolve(ContentViewInterface $contentView): array
    {
        $structure = $this->getStructure($contentView);
        $data = $contentView->getData() ?? [];

        if (!$structure) {
            return [];
        }

        return [
            'id' => $contentView->getResourceId(),
            'template' => $contentView->getType(),
            'content' => $this->resolveContent($structure, $data),
            'view' => $this->resolveView($structure, $data),
        ];
    }

    private function getStructure(ContentViewInterface $contentView): ?StructureInterface
    {
        $contentType = $contentView->getType();
        if (!$contentType) {
            return null;
        }

        $structure = $this->structureManager->getStructure($contentType, $contentView->getResourceKey());
        $structure->setLanguageCode($contentView->getLocale());

        return $structure;
    }

    private function resolveView(StructureInterface $structure, array $data): array
    {
        $view = [];
        foreach ($structure->getProperties(true) as $child) {
            if (\array_key_exists($child->getName(), $data)) {
                $child->setValue($data[$child->getName()]);
            }

            $contentType = $this->contentTypeManager->get($child->getContentTypeName());
            $view[$child->getName()] = $contentType->getViewData($child);
        }

        return $view;
    }

    private function resolveContent(StructureInterface $structure, array $data): array
    {
        $content = [];
        foreach ($structure->getProperties(true) as $child) {
            if (\array_key_exists($child->getName(), $data)) {
                $child->setValue($data[$child->getName()]);
            }

            $contentType = $this->contentTypeManager->get($child->getContentTypeName());
            $content[$child->getName()] = $contentType->getContentData($child);
        }

        return $content;
    }
}
