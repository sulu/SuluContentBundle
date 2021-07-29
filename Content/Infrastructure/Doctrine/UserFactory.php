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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\UserFactoryInterface;
use Sulu\Component\Security\Authentication\UserInterface;

class UserFactory implements UserFactoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(?int $userId): ?UserInterface
    {
        if (!$userId) {
            return null;
        }

        /** @var UserInterface|null $user */
        $user = $this->entityManager->getPartialReference(
            UserInterface::class,
            $userId
        );

        return $user;
    }
}
