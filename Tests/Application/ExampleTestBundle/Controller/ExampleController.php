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

namespace Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionMergerInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Repository\DimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExampleController extends AbstractRestController implements ClassResourceInterface
{
    /**
     * @var FieldDescriptorFactoryInterface
     */
    private $fieldDescriptorFactory;

    /**
     * @var DoctrineListBuilderFactoryInterface
     */
    private $listBuilderFactory;

    /**
     * @var RestHelperInterface
     */
    private $restHelper;

    /**
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

    /**
     * @var ContentDimensionMergerInterface
     */
    private $contentDimensionMerger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        TokenStorageInterface $tokenStorage,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        DoctrineListBuilderFactoryInterface $listBuilderFactory,
        RestHelperInterface $restHelper,
        DimensionRepositoryInterface $dimensionRepository,
        ContentDimensionMergerInterface $contentDimensionMerger,
        EntityManagerInterface $entityManager
    ) {
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->listBuilderFactory = $listBuilderFactory;
        $this->restHelper = $restHelper;
        $this->dimensionRepository = $dimensionRepository;
        $this->contentDimensionMerger = $contentDimensionMerger;
        $this->entityManager = $entityManager;

        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(): Response
    {
        /** @var DoctrineFieldDescriptorInterface[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors(Example::RESOURCE_KEY);
        $listBuilder = $this->listBuilderFactory->create(Example::class);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $listRepresentation = new PaginatedRepresentation(
            $listBuilder->execute(),
            Example::RESOURCE_KEY,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            $listBuilder->count()
        );

        return $this->handleView($this->view($listRepresentation));
    }

    public function getAction(Request $request, int $id): Response
    {
        /** @var Example|null $example */
        $example = $this->entityManager->getRepository(Example::class)->findOneBy(['id' => $id]);

        if (!$example) {
            throw new NotFoundHttpException();
        }

        return $this->handleView($this->view($this->createViewData($example, $this->getAttributes($request))));
    }

    public function postAction(Request $request): Response
    {
        $example = new Example();
        $data = $this->getData($request);

        // TODO set example data

        $this->entityManager->persist($example);

        // TODO set dimension data

        $this->entityManager->flush();

        return $this->handleView($this->view($this->createViewData($example, $this->getAttributes($request)), 201));
    }

    public function putAction(Request $request, int $id): Response
    {
        /** @var Example|null $example */
        $example = $this->entityManager->getRepository(Example::class)->findOneBy(['id' => $id]);

        if (!$example) {
            throw new NotFoundHttpException();
        }

        // TODO set example data
        // TODO set dimension data

        $this->entityManager->flush();

        return $this->handleView($this->view($this->createViewData($example, $this->getAttributes($request))));
    }

    public function deleteAction(int $id): Response
    {
        /** @var Example $example */
        $example = $this->entityManager->getReference(Example::class, $id);
        $this->entityManager->remove($example);
        $this->entityManager->flush();

        return new Response('', 204);
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    protected function createViewData(Example $example, array $attributes): array
    {
        $dimensionIds = $this->dimensionRepository->findIdsByAttributes($attributes);

        $dimensions = [];
        // TODO here we should call the repository and load only the dimension we need
        foreach ($example->getDimensions() as $dimension) {
            $dimensionId = $dimension->getDimensionId();
            if (\in_array($dimensionId, $dimensionIds, true)) {
                $dimensions[array_search($dimensionId, $dimensionIds, true)] = $dimension;
            }
        }

        $data = $this->contentDimensionMerger->merge($dimensions);

        // FIXME some hack implementation it should be possible to set this templateKey and form path
        $data['id'] = $example->getId();
        $data = array_merge($data, $data['template']['templateData']);
        $data['template'] = $data['template']['templateKey'];

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAttributes(Request $request): array
    {
        return $request->query->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(Request $request): array
    {
        $data = $request->request->all();

        return $data;
    }
}
