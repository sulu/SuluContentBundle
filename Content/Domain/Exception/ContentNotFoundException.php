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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Exception;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;

class ContentNotFoundException extends \Exception
{
    /**
     * @param array<string, string|int|float|bool|null> $dimensionAttributes
     */
    public function __construct(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes)
    {
        /** @var string|int $id */
        $id = $contentRichEntity->getId();

        parent::__construct(\sprintf(
            'Could not load content with id "%s" and attributes: %s',
            $id,
            \json_encode($dimensionAttributes)
        ));
    }
}
