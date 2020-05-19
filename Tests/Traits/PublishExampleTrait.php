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
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;

trait PublishExampleTrait
{
    /**
     * @param mixed $id
     */
    protected static function publishExample(
        $id,
        string $locale = 'en'
    ): ExampleDimensionContent {
        $dimensionAttributes = ['locale' => $locale];

        /** @var Example|null $example */
        $example = static::getEntityManager()->getRepository(Example::class)->find($id);

        if (!$example) {
            throw new \RuntimeException(sprintf('Example with id "%s" was not found!', $id));
        }

        /** @var ExampleDimensionContent $dimensionContent */
        $dimensionContent = static::getContentManager()->applyTransition(
            $example,
            $dimensionAttributes,
            WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
        );

        static::getEntityManager()->flush();

        return $dimensionContent;
    }

    abstract protected static function getContentManager(): ContentManagerInterface;

    abstract protected static function getEntityManager(): EntityManagerInterface;
}
