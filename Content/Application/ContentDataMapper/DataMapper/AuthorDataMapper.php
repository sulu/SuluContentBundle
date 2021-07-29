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

use Sulu\Bundle\ContentBundle\Content\Domain\Factory\UserFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AuthorInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;

class AuthorDataMapper implements DataMapperInterface
{
    /**
     * @var UserFactoryInterface
     */
    private $userFactory;

    public function __construct(UserFactoryInterface $userFactory)
    {
        $this->userFactory = $userFactory;
    }

    public function map(
        array $data,
        DimensionContentCollectionInterface $dimensionContentCollection
    ): void {
        $dimensionAttributes = $dimensionContentCollection->getDimensionAttributes();
        $unlocalizedDimensionAttributes = array_merge($dimensionAttributes, ['locale' => null]);
        $unlocalizedObject = $dimensionContentCollection->getDimensionContent($unlocalizedDimensionAttributes);

        if (!$unlocalizedObject instanceof AuthorInterface) {
            return;
        }

        $localizedObject = $dimensionContentCollection->getDimensionContent($dimensionAttributes);

        if ($localizedObject) {
            if (!$localizedObject instanceof AuthorInterface) {
                throw new \RuntimeException(sprintf('Expected "$localizedObject" from type "%s" but "%s" given.', AuthorInterface::class, \get_class($localizedObject)));
            }

            $this->setAuthorData($localizedObject, $data);

            return;
        }

        $this->setAuthorData($unlocalizedObject, $data);
    }

    /**
     * @param mixed[] $data
     */
    private function setAuthorData(AuthorInterface $dimensionContent, array $data): void
    {
        if (isset($data['author'])) {
            $dimensionContent->setAuthor($this->userFactory->create($data['author']));
        }

        if (isset($data['authored'])) {
            $dimensionContent->setAuthored(new \DateTimeImmutable($data['authored']));
        }
    }
}
