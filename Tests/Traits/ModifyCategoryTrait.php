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
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryTranslation;
use Sulu\Bundle\CategoryBundle\Entity\CategoryTranslationInterface;

trait ModifyCategoryTrait
{
    protected static function modifyCategory(int $id, string $title, string $locale = 'en'): CategoryInterface
    {
        $category = static::getCategoryRepository()->findCategoryById($id);

        if (!$category) {
            throw new \RuntimeException(sprintf('Category with id "%d" was not found!', $id));
        }

        /** @var CategoryTranslationInterface|null $translation */
        $translation = $category->findTranslationByLocale($locale);

        if (!$translation) {
            $translation = new CategoryTranslation();
            $translation->setCategory($category);
            $translation->setLocale($locale);

            $category->addTranslation($translation);

            static::getEntityManager()->persist($translation);
        }

        $translation->setTranslation($title);

        static::getEntityManager()->flush();

        return $category;
    }

    abstract protected static function getEntityManager(): EntityManagerInterface;

    abstract protected static function getCategoryRepository(): CategoryRepositoryInterface;
}
