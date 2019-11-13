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

namespace Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContentDimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;

class ExampleDimension extends AbstractContentDimension implements ExcerptInterface, SeoInterface, TemplateInterface
{
    use ExcerptTrait;
    use SeoTrait;
    use TemplateTrait {
        getTemplateData as parentGetTemplateData;
        setTemplateData as parentSetTemplateData;
    }

    /**
     * @var Example
     */
    protected $example;

    /**
     * @var string|null
     */
    protected $title;

    public function __construct(Example $example, DimensionInterface $dimension)
    {
        $this->example = $example;
        $this->dimension = $dimension;
    }

    public function getExample(): Example
    {
        return $this->example;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getTemplateData(): array
    {
        $data = $this->parentGetTemplateData();
        $data['title'] = $this->getTitle();

        return $data;
    }

    public function setTemplateData(array $templateData): void
    {
        $this->setTitle($templateData['title']);
        unset($templateData['title']);
        $this->parentSetTemplateData($templateData);
    }

    public function createViewInstance(): ContentViewInterface
    {
        $contentView = new ExampleView($this->getExample(), $this->dimension);
        $contentView->setTitle($this->getTitle());

        return $contentView;
    }

    public function getTemplateType(): string
    {
        return Example::TYPE_KEY;
    }
}
