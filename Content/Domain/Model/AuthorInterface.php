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

interface AuthorInterface
{
    public function getAuthor(): ?UserInterface;

    public function setAuthor(?UserInterface $author): void;

    public function getAuthored(): ?\DateTimeImmutable;

    public function setAuthored(?\DateTimeImmutable $authored): void;
}
