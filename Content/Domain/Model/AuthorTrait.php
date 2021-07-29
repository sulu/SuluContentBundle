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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Model;

use Sulu\Component\Security\Authentication\UserInterface;

/**
 * Basic implementation of the AuthorTrait.
 */
trait AuthorTrait
{
    /**
     * @var UserInterface|null
     */
    private $author;

    /**
     * @var \DateTimeImmutable|null
     */
    private $authored;

    public function getAuthor(): ?UserInterface
    {
        return $this->author;
    }

    public function setAuthor(?UserInterface $author): void
    {
        $this->author = $author;
    }

    public function getAuthored(): ?\DateTimeImmutable
    {
        return $this->authored;
    }

    public function setAuthored(?\DateTimeImmutable $authored): void
    {
        $this->authored = $authored;
    }
}
