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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\SmartContent\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;

class ContentDataProviderRepository implements DataProviderRepositoryInterface
{
    use ContentDataProviderRepositoryTrait;

    /**
     * @var ContentManagerInterface
     */
    protected $contentManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $entityClassName;

    /**
     * @param class-string<ContentRichEntityInterface> $entityClassName
     */
    public function __construct(
        ContentManagerInterface $contentManager,
        EntityManagerInterface $entityManager,
        string $entityClassName
    ) {
        $this->contentManager = $contentManager;
        $this->entityManager = $entityManager;
        $this->entityClassName = $entityClassName;
    }

    protected function getContentManager(): ContentManagerInterface
    {
        return $this->contentManager;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @return class-string<ContentRichEntityInterface>
     */
    protected function getEntityClass(): string
    {
        /** @var class-string<ContentRichEntityInterface> $entityClassName */
        $entityClassName = $this->entityClassName;

        return $entityClassName;
    }
}
