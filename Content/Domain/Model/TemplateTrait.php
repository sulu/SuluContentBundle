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

/**
 * Basic implementation of the TemplateInterface.
 */
trait TemplateTrait
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $templateData = [];

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function getTemplateData(): array
    {
        return $this->templateData;
    }

    public function setTemplateData(array $templateData): void
    {
        $this->templateData = $templateData;
    }

    /**
     * @return mixed[]
     */
    public function templateToArray(): array
    {
        return [
            'template' => $this->getTemplate(),
            'data' => $this->getTemplateData(),
        ];
    }
}
