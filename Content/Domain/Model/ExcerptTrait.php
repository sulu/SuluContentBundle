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

use Doctrine\Common\Collections\ArrayCollection;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Webmozart\Assert\Assert;

trait ExcerptTrait
{
    /**
     * @var string|null
     */
    private $excerptTitle;

    /**
     * @var string|null
     */
    private $excerptDescription;

    /**
     * @var string|null
     */
    private $excerptMore;

    /**
     * @var ArrayCollection<CategoryInterface>
     */
    private $excerptCategories;

    /**
     * @var ArrayCollection<TagInterface>
     */
    private $excerptTags;

    /**
     * @var int|null
     */
    private $excerptImageId;

    /**
     * @var int|null
     */
    private $excerptIconId;

    public function getExcerptTitle(): ?string
    {
        return $this->excerptTitle;
    }

    public function setExcerptTitle(?string $excerptTitle): void
    {
        $this->excerptTitle = $excerptTitle;
    }

    public function getExcerptDescription(): ?string
    {
        return $this->excerptDescription;
    }

    public function setExcerptDescription(?string $excerptDescription): void
    {
        $this->excerptDescription = $excerptDescription;
    }

    public function getExcerptMore(): ?string
    {
        return $this->excerptMore;
    }

    public function setExcerptMore(?string $excerptMore): void
    {
        $this->excerptMore = $excerptMore;
    }

    /**
     * @return int[]
     */
    public function getExcerptCategoryIds(): array
    {
        $this->initializeCategories();
        $categoryIds = [];
        foreach ($this->excerptCategories as $excerptCategory) {
            $categoryIds[] = $excerptCategory->getId();
        }

        return $categoryIds;
    }

    /**
     * @return CategoryInterface[]
     */
    public function getExcerptCategories(): array
    {
        $this->initializeCategories();

        return $this->excerptCategories->toArray();
    }

    /**
     * @param CategoryInterface[] $excerptCategories
     */
    public function setExcerptCategories(array $excerptCategories): void
    {
        $this->initializeCategories();
        $this->excerptCategories->clear();

        foreach ($excerptCategories as $excerptCategory) {
            $this->excerptCategories->add($excerptCategory);
        }
    }

    /**
     * @return TagInterface[]
     */
    public function getExcerptTags(): array
    {
        $this->initializeTags();

        return $this->excerptTags->toArray();
    }

    /**
     * @return string[]
     */
    public function getExcerptTagNames(): array
    {
        $this->initializeTags();
        $tagNames = [];
        foreach ($this->excerptTags as $excerptTag) {
            $tagNames[] = $excerptTag->getName();
        }

        return $tagNames;
    }

    /**
     * @param TagInterface[] $excerptTags
     */
    public function setExcerptTags(array $excerptTags): void
    {
        $this->initializeTags();
        $this->excerptTags->clear();

        foreach ($excerptTags as $excerptTag) {
            $this->excerptTags->add($excerptTag);
        }
    }

    /**
     * @return mixed[]|null
     */
    public function getExcerptImage(): ?array
    {
        if (!$this->excerptImageId) {
            return null;
        }

        return [
            'id' => $this->excerptImageId,
        ];
    }

    /**
     * @param mixed[]|null $excerptImage
     */
    public function setExcerptImage(?array $excerptImage): void
    {
        $this->excerptImageId = $excerptImage['id'] ?? null;
    }

    /**
     * @return mixed[]|null
     */
    public function getExcerptIcon(): ?array
    {
        if (!$this->excerptIconId) {
            return null;
        }

        return [
            'id' => $this->excerptIconId,
        ];
    }

    /**
     * @param mixed[]|null $excerptIcon
     */
    public function setExcerptIcon(?array $excerptIcon): void
    {
        $this->excerptIconId = $excerptIcon['id'] ?? null;
    }

    private function initializeTags(): void
    {
        if (null === $this->excerptTags) {
            $this->excerptTags = new ArrayCollection();
        }
    }

    private function initializeCategories(): void
    {
        if (null === $this->excerptCategories) {
            $this->excerptCategories = new ArrayCollection();
        }
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function setExcerptData(array $data): array
    {
        $excerptTitle = 'excerptTitle';

        if (array_key_exists($excerptTitle, $data)) {
            $value = $data[$excerptTitle];

            Assert::nullOrString($value);

            $this->setExcerptTitle($value);

            unset($data[$excerptTitle]);
        }

        $excerptDescription = 'excerptDescription';

        if (array_key_exists($excerptDescription, $data)) {
            $excerptDescription = $data[$excerptDescription];

            Assert::nullOrString($excerptDescription);

            $this->setExcerptDescription($value);

            unset($data[$excerptDescription]);
        }

        $excerptMore = 'excerptMore';

        if (array_key_exists($excerptMore, $data)) {
            $value = $data[$excerptMore];

            Assert::nullOrString($value);

            $this->setExcerptMore($value);

            unset($data[$excerptMore]);
        }

        $excerptCategories = 'excerptCategories';

        if (array_key_exists($excerptCategories, $data)) {
            $value = $data[$excerptCategories];

            Assert::isArray($value);

            $this->setExcerptCategories($value);

            unset($data[$excerptCategories]);
        }

        $excerptTags = 'excerptTags';

        if (array_key_exists($excerptTags, $data)) {
            $value = $data[$excerptTags];

            Assert::isArray($value);

            $this->setExcerptTags($value);

            unset($data[$excerptTags]);
        }

        $excerptImage = 'excerptImage';

        if (array_key_exists($excerptImage, $data)) {
            $value = $data[$excerptImage];

            Assert::nullOrIsArray($value);

            $this->setExcerptImage($value);

            unset($data[$excerptImage]);
        }

        $excerptIcon = 'excerptIcon';

        if (array_key_exists($excerptIcon, $data)) {
            $value = $data[$excerptIcon];

            Assert::nullOrIsArray($value);

            $this->setExcerptIcon($value);

            unset($data[$excerptIcon]);
        }

        return $data;
    }
}
