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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContactFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AuthorInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

class AuthorDataMapper implements DataMapperInterface
{
    /**
     * @var ContactFactoryInterface
     */
    private $contactFactory;

    public function __construct(ContactFactoryInterface $contactFactory)
    {
        $this->contactFactory = $contactFactory;
    }

    public function map(
        array $data,
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent
    ): void {
        if (!$localizedDimensionContent instanceof AuthorInterface) {
            return;
        }

        $this->setAuthorData($localizedDimensionContent, $data);
    }

    /**
     * @param mixed[] $data
     */
    private function setAuthorData(AuthorInterface $dimensionContent, array $data): void
    {
        if (isset($data['author'])) {
            $dimensionContent->setAuthor($this->contactFactory->create($data['author']));
        }

        if (isset($data['authored'])) {
            $dimensionContent->setAuthored(new \DateTimeImmutable($data['authored']));
        }
    }
}
