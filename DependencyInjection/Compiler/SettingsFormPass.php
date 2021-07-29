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

namespace Sulu\Bundle\ContentBundle\DependencyInjection\Compiler;

use Sulu\Bundle\AdminBundle\FormMetadata\FormXmlLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class SettingsFormPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $formDirectories = $container->getParameter('sulu_admin.forms.directories');
        $formMappingTags = [];

        $finder = new Finder();
        $finder->files()->in($formDirectories);
        foreach ($finder as $file) {
            $document = new \DOMDocument();

            if (!$xmlContent = file_get_contents($file->getPathname())) {
                continue;
            }

            $document->loadXML($xmlContent);
            $path = new \DOMXPath($document);
            $path->registerNamespace('x', FormXmlLoader::SCHEMA_NAMESPACE_URI);
            $tagNodes = $path->query('/x:form/x:tag');

            if (!$tagNodes) {
                continue;
            }

            /** @var \DOMElement $tagNode */
            foreach ($tagNodes as $tagNode) {
                $tag = [
                    'name' => $tagNode->getAttribute('name'),
                    'instanceOf' => $tagNode->getAttribute('instanceOf'),
                    'priority' => $tagNode->getAttribute('priority'),
                    'path' => $file->getPathname(),
                ];

                if (!empty($tag['instanceOf'])) {
                    $formMappingTags[] = $tag;
                }
            }
        }

        $container->setParameter('sulu_content.settings_mapping_tags', $formMappingTags);
    }
}
