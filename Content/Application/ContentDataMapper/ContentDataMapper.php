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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper;

use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper\DataMapperInterface;

class ContentDataMapper implements ContentDataMapperInterface
{
    /**
     * @var iterable<DataMapperInterface>
     */
    private $dataMappers;

    /**
     * @param iterable<DataMapperInterface> $dataMappers
     */
    public function __construct(iterable $dataMappers)
    {
        $this->dataMappers = $dataMappers;
    }

    public function map(
        array $data,
        object $unlocalizedObject,
        ?object $localizedObject = null
    ): void {
        foreach ($this->dataMappers as $mapper) {
            $mapper->map($data, $unlocalizedObject, $localizedObject);
        }
    }
}
