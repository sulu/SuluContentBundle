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

interface AuthorInterface
{
    public function getLastModifiedEnabled(): ?bool;

    public function getLastModified(): ?\DateTime;

    public function setLastModified(?\DateTime $lastModified): void;

    public function getAuthor(): ?ContactInterface;

    public function setAuthor(?ContactInterface $author): void;

    public function getAuthored(): ?\DateTime;

    public function setAuthored(?\DateTime $authored): void;
}
