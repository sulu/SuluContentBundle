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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\TagBundle\Entity\Tag;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

trait TagTrait
{
    public function createTag(string $name): TagInterface
    {
        $tag = new Tag();

        $tag->setName($name);

        $this->getEntityManager()->persist($tag);
        $this->getEntityManager()->flush();

        return $tag;
    }

    /**
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();
}
