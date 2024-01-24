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

use Sulu\Bundle\ContactBundle\Entity\ContactInterface;

/**
 * Basic implementation of the AuthorInterface.
 */
trait AuthorTrait
{
    /**
     * @var ContactInterface|null
     */
    private $author;

    /**
     * @var \DateTime|null
     */
    private $authored;

    /**
     * @var \DateTime|null
     */
    private $lastModified;

    public function getLastModifiedEnabled(): ?bool
    {
        return null !== $this->lastModified;
    }

    public function getLastModified(): ?\DateTime
    {
        return $this->lastModified;
    }

    public function setLastModified(?\DateTime $lastModified): void
    {
        $this->lastModified = $lastModified;
    }

    public function getAuthor(): ?ContactInterface
    {
        return $this->author;
    }

    public function setAuthor(?ContactInterface $author): void
    {
        $this->author = $author;
    }

    public function getAuthored(): ?\DateTime
    {
        return $this->authored;
    }

    public function setAuthored(?\DateTime $authored): void
    {
        $this->authored = $authored;
    }
}
