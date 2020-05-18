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

namespace Sulu\Bundle\ContentBundle\Tests\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Ferrandini\Urlizer;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;

trait ModifyExampleTrait
{
    /**
     * @param mixed $id
     * @param mixed[] $data
     */
    protected static function modifyExample(
        $id,
        array $data = [],
        string $locale = 'en',
        string $template = 'default'
    ): Example {
        $title = $data['title'] ?? 'Test Example';

        $defaultData = [
            'template' => $template,
            'title' => $title,
            'url' => '/' . Urlizer::urlize($title),
            'article' => '<p>Test article</p>',
        ];

        $dimensionAttributes = ['locale' => $locale];

        /** @var Example|null $example */
        $example = static::getEntityManager()->getRepository(Example::class)->find($id);

        if (!$example) {
            throw new \RuntimeException(sprintf('Example with id "%s" was not found!', $id));
        }

        /** @var ExampleDimensionContent $dimensionContent */
        $dimensionContent = static::getContentManager()->persist(
            $example,
            array_merge($defaultData, $data),
            $dimensionAttributes
        );

        if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace()) {
            /** @var ExampleDimensionContent $dimensionContent */
            $dimensionContent = static::getContentManager()->applyTransition(
                $example,
                $dimensionAttributes,
                WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT
            );
        }

        static::getEntityManager()->flush();

        /** @var Example $example */
        $example = $dimensionContent->getContentRichEntity();

        return $example;
    }

    abstract protected static function getContentManager(): ContentManagerInterface;

    abstract protected static function getEntityManager(): EntityManagerInterface;
}
