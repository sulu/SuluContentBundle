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

namespace Sulu\Bundle\ContentBundle\TestCases\Content;

use Sulu\Bundle\CategoryBundle\Entity\Category;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\TagBundle\Entity\Tag;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

/**
 * Trait to test your implementation of the ExcerptInterface.
 */
trait ExcerptTestCaseTrait
{
    abstract protected function getExcerptInstance(): ExcerptInterface;

    public function testGetSetExcerptTitle(): void
    {
        $model = $this->getExcerptInstance();
        $this->assertNull($model->getExcerptTitle());
        $model->setExcerptTitle('Excerpt Title');
        $this->assertSame('Excerpt Title', $model->getExcerptTitle());
    }

    public function testGetSetExcerptDescription(): void
    {
        $model = $this->getExcerptInstance();
        $this->assertNull($model->getExcerptDescription());
        $model->setExcerptDescription('Excerpt Description');
        $this->assertSame('Excerpt Description', $model->getExcerptDescription());
    }

    public function testGetSetExcerptMore(): void
    {
        $model = $this->getExcerptInstance();
        $this->assertNull($model->getExcerptMore());
        $model->setExcerptMore('Excerpt More');
        $this->assertSame('Excerpt More', $model->getExcerptMore());
    }

    public function testGetSetExcerptImageId(): void
    {
        $model = $this->getExcerptInstance();
        $this->assertNull($model->getExcerptImage());
        $model->setExcerptImage(1);
        $this->assertSame(1, $model->getExcerptImage());
    }

    public function testGetSetExcerptIconId(): void
    {
        $model = $this->getExcerptInstance();
        $this->assertNull($model->getExcerptIcon());
        $model->setExcerptIcon(2);
        $this->assertSame(2, $model->getExcerptIcon());
    }

    public function testGetSetExcerptTags(): void
    {
        $tag1 = $this->createTag(1);
        $tag2 = $this->createTag(2);

        $model = $this->getExcerptInstance();
        $this->assertEmpty($model->getExcerptTagIds());
        $model->setExcerptTags([$tag1, $tag2]);
        $this->assertSame([1, 2], $model->getExcerptTagIds());
    }

    public function testGetSetExcerptCategories(): void
    {
        $category1 = $this->createCategory(1);
        $category2 = $this->createCategory(2);

        $model = $this->getExcerptInstance();
        $this->assertEmpty($model->getExcerptCategoryIds());
        $model->setExcerptCategories([$category1, $category2]);
        $this->assertSame([1, 2], $model->getExcerptCategoryIds());
    }

    public function testExcerptToArray(): void
    {
        $model = $this->getExcerptInstance();
        $this->assertSame([
            'title' => null,
            'description' => null,
            'more' => null,
            'image' => null,
            'icon' => null,
            'categories' => [],
            'tags' => [],
        ], $this->excerptToArray($model));
    }

    /**
     * Overwrite this function to unset custom data.
     *
     * @return mixed[]
     */
    protected function excerptToArray(ExcerptInterface $model): array
    {
        return $model->excerptToArray();
    }

    private function createTag(int $id): TagInterface
    {
        $tag = new Tag();
        $tag->setId($id);

        return $tag;
    }

    private function createCategory(int $id): CategoryInterface
    {
        $category = new Category();
        $category->setId($id);

        return $category;
    }
}
