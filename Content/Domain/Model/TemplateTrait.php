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
    private $templateKey;

    /**
     * @var array
     */
    private $templateData = [];

    public function getTemplateKey(): string
    {
        return $this->templateKey;
    }

    public function setTemplateKey(string $templateKey): void
    {
        $this->templateKey = $templateKey;
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
            'templateKey' => $this->getTemplateKey(),
            'templateData' => $this->getTemplateData(),
        ];
    }
}
