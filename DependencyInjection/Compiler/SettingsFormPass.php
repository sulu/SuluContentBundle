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

        $finder = new Finder();
        $finder->files()->in($formDirectories);
        $settingsForms = [];
        foreach ($finder as $file) {
            $document = new \DOMDocument();
            $document->load($file->getPathname());
            $path = new \DOMXPath($document);
            $path->registerNamespace('x', FormXmlLoader::SCHEMA_NAMESPACE_URI);
            $tagNodes = $path->query('/x:form/x:tag');

            if (!$tagNodes || 0 === $tagNodes->count()) {
                continue;
            }

            /** @var \DOMElement $tagNode */
            foreach ($tagNodes as $tagNode) {
                $instanceOf = $tagNode->getAttribute('instanceOf');
                $priority = $tagNode->getAttribute('priority');

                if (empty($instanceOf)) {
                    continue;
                }

                $settingsForms[$tagNode->getAttribute('name')] = [
                    'instanceOf' => $instanceOf,
                    'priority' => $priority,
                ];
            }
        }

        uasort($settingsForms, static function ($a, $b) {
            return $b['priority'] ?? 0 <=> $a['priority'] ?? 0;
        });

        $container->setParameter('sulu_content.content_settings_forms', $settingsForms);
    }
}
