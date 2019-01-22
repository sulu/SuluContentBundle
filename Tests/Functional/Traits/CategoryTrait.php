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
use Sulu\Bundle\CategoryBundle\Entity\Category;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;

trait CategoryTrait
{
    public function createCategory(): CategoryInterface
    {
        $category = new Category();

        $category->setDefaultLocale('en');

        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();

        return $category;
    }

    /**
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();
}
