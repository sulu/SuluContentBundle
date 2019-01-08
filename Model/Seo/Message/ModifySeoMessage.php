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

namespace Sulu\Bundle\ContentBundle\Model\Seo\Message;

use Sulu\Bundle\ContentBundle\Common\Model\MissingResultException;
use Sulu\Bundle\ContentBundle\Common\Payload\PayloadTrait;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoViewInterface;

class ModifySeoMessage
{
    use PayloadTrait {
        __construct as protected initializePayload;
    }

    /**
     * @var string
     */
    private $resourceKey;

    /**
     * @var string
     */
    private $resourceId;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var SeoViewInterface|null
     */
    private $seo;

    public function __construct(string $resourceKey, string $resourceId, string $locale, array $payload)
    {
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->locale = $locale;

        $this->initializePayload($payload);
    }

    public function getResourceKey(): string
    {
        return $this->resourceKey;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getSeo(): SeoViewInterface
    {
        if (!$this->seo) {
            throw new MissingResultException(__METHOD__);
        }

        return $this->seo;
    }

    public function setSeo(SeoViewInterface $seo): ModifySeoMessage
    {
        $this->seo = $seo;

        return $this;
    }
}
