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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentAssociationMapper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentAssociationMapper\ContentAssociationMapperInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;

class ContentAssociationMapperTest extends TestCase
{
    protected function createContentAssociationMapperTestInstance(
        EntityManagerInterface $entityManager
    ): ContentAssociationMapperInterface {
        return new \Sulu\Bundle\ContentBundle\Content\Application\ContentAssociationMapper(
            $entityManager
        );
    }

    public function testGetDimensionContentClass(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $classMetadata = $this->prophesize(ClassMetadata::class);
        $classMetadata->getAssociationMapping('dimensionContents')
            ->willReturn(['targetEntity' => ExampleDimensionContent::class]);

        $entityManager->getClassMetadata(Example::class)->willReturn($classMetadata->reveal());

        $contentAssociationManager = $this->createContentAssociationMapperTestInstance($entityManager);
        
        $dimensionContentClass = $contentAssociationManager->getDimensionContentClass(Example::class);
        
        $this->assertSame(ExampleDimensionContent::class, $dimensionContentClass);
    }
}
