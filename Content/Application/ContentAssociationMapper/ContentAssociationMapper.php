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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentAssociationMapper;

use Doctrine\ORM\EntityManagerInterface;

class ContentAssociationMapper implements ContentAssociationMapperInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }
    
    /**
     * @param class-string<ContentRichEntityInterface> $contentRichEntityClass
     *
     * @return class-string<DimensionContentInterface>
     */
    public function getDimensionContentClass(string $contentRichEntityClass): string
    {
        $classMetadata = $this->entityManager->getClassMetadata($contentRichEntityClass);
        $associationMapping = $classMetadata->getAssociationMapping('dimensionContents');

        return $associationMapping['targetEntity'];
    }
}