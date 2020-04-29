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

namespace Sulu\Bundle\ContentBundle\Tests\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CategoryBundle\Entity\Category;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryTranslation;

trait CreateCategoryTrait
{
    protected static function createCategory(string $title, string $locale = 'en'): CategoryInterface
    {
        $category = new Category();
        $category->setDefaultLocale($locale);

        $translation = new CategoryTranslation();
        $translation->setCategory($category);
        $translation->setLocale($locale);
        $translation->setTranslation($title);

        $category->addTranslation($translation);

        static::getEntityManager()->persist($category);
        static::getEntityManager()->persist($translation);
        static::getEntityManager()->flush();

        return $category;
    }

    abstract protected static function getEntityManager(): EntityManagerInterface;
}
