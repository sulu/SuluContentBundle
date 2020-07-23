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
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;
use Symfony\Component\String\Slugger\AsciiSlugger;

trait CreateExampleTrait
{
    /**
     * @param mixed[] $data
     */
    protected static function createExample(
        array $data = [],
        string $locale = 'en',
        string $template = 'default'
    ): ExampleDimensionContent {
        $title = $data['title'] ?? 'Test Example';

        $defaultData = [
            'template' => $template,
            'title' => $title,
            'url' => '/' . (new AsciiSlugger())->slug($title),
            'article' => '<p>Test article</p>',
        ];

        $dimensionAttributes = ['locale' => $locale];

        $example = new Example();

        static::getEntityManager()->persist($example);
        static::getEntityManager()->flush();

        /** @var ExampleDimensionContent $dimensionContent */
        $dimensionContent = static::getContentManager()->persist(
            $example,
            array_merge($defaultData, $data),
            $dimensionAttributes
        );

        static::getEntityManager()->flush();

        return $dimensionContent;
    }

    abstract protected static function getContentManager(): ContentManagerInterface;

    abstract protected static function getEntityManager(): EntityManagerInterface;
}
