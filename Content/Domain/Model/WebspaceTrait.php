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

trait WebspaceTrait
{
    /**
     * @var string|null
     */
    protected $mainWebspace;

    /**
     * @var string[]|null
     */
    protected $additionalWebspaces;

    public function getMainWebspace(): ?string
    {
        return $this->mainWebspace;
    }

    public function setMainWebspace(?string $mainWebspace): void
    {
        $this->mainWebspace = $mainWebspace;

        if ($mainWebspace && !\in_array($mainWebspace, $this->additionalWebspaces ?: [], true)) {
            // additional webspace always include also the main webspace to make query simpler
            $this->additionalWebspaces = \array_merge($this->additionalWebspaces ?: [], [$mainWebspace]);
        }
    }

    /**
     * @return string[]
     */
    public function getAdditionalWebspaces(): array
    {
        return $this->additionalWebspaces ?: [];
    }

    /**
     * @param string[] $additionalWebspaces
     */
    public function setAdditionalWebspaces(array $additionalWebspaces): void
    {
        if ($this->mainWebspace && !\in_array($this->mainWebspace, $additionalWebspaces, true)) {
            // additional webspace always include also the main webspace to make query simpler
            $additionalWebspaces[] = $this->mainWebspace;
        }

        $this->additionalWebspaces = $additionalWebspaces;
    }
}
