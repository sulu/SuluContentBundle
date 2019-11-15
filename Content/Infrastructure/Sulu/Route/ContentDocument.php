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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Route;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Component\Content\Document\Behavior\ExtensionBehavior;

/**
 * @codeCoverageIgnore
 */
class ContentDocument implements ExtensionBehavior
{
    /**
     * @var TemplateInterface
     */
    private $content;

    /**
     * @var string
     */
    private $locale;

    public function __construct(TemplateInterface $content, string $locale)
    {
        $this->content = $content;
        $this->locale = $locale;
    }

    /**
     * @return mixed[]
     */
    public function getExtensionsData(): array
    {
        $seo = [];
        if ($this->content instanceof SeoInterface) {
            $seo = [
                'title' => $this->content->getSeoTitle(),
                'description' => $this->content->getSeoDescription(),
                'keywords' => $this->content->getSeoKeywords(),
                'canonicalUrl' => $this->content->getSeoCanonicalUrl(),
                'noIndex' => $this->content->getSeoNoIndex(),
                'noFollow' => $this->content->getSeoNoFollow(),
                'hideInSitemap' => $this->content->getSeoHideInSitemap(),
            ];
        }

        $excerpt = [];
        if ($this->content instanceof ExcerptInterface) {
            $excerpt = [
                'title' => $this->content->getExcerptTitle(),
                'description' => $this->content->getExcerptDescription(),
                'more' => $this->content->getExcerptMore(),
                'categories' => $this->content->getExcerptCategoryIds(),
                'tags' => $this->content->getExcerptTags(),
            ];
        }

        return [
            'seo' => $seo,
            'excerpt' => $excerpt,
        ];
    }

    public function setExtensionsData($extensionData): void
    {
        $this->readOnlyException(__METHOD__);
    }

    public function setExtension($name, $data): void
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale($locale): void
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getOriginalLocale(): string
    {
        return $this->locale;
    }

    public function setOriginalLocale($locale): void
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getStructureType(): ?string
    {
        return $this->content->getTemplateKey();
    }

    public function setStructureType($structureType): void
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getStructure()
    {
        return null;
    }

    protected function readOnlyException(string $method): void
    {
        throw new \BadMethodCallException(
            sprintf(
                'Compatibility layer ContentDocument instances are readonly. Tried to call "%s"',
                $method
            )
        );
    }
}
