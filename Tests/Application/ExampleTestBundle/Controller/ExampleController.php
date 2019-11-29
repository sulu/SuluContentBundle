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
use Sulu\Bundle\ContentBundle\Content\Application\ContentLoader\ContentLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilder;
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
     * @var ContentLoaderInterface
     */
    private $contentLoader;

    /**
     * @var ContentPersisterInterface
     */
    private $contentPersister;

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
        ContentLoaderInterface $contentLoader,
        ContentPersisterInterface $contentPersister,
        EntityManagerInterface $entityManager
    ) {
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->listBuilderFactory = $listBuilderFactory;
        $this->restHelper = $restHelper;
        $this->contentLoader = $contentLoader;
        $this->contentPersister = $contentPersister;
        $this->entityManager = $entityManager;

        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        /** @var DoctrineFieldDescriptorInterface[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors(Example::RESOURCE_KEY);
        /** @var DoctrineListBuilder $listBuilder */
        $listBuilder = $this->listBuilderFactory->create(Example::class);
        $listBuilder->setParameter('locale', $request->query->get('locale'));
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

        $dimensionAttributes = $this->getAttributes($request);
        $contentView = $this->contentLoader->load($example, $dimensionAttributes);

        return $this->handleView($this->view($contentView));
    }

    public function postAction(Request $request): Response
    {
        $example = new Example();

        $data = $this->getData($request);
        $dimensionAttributes = $this->getAttributes($request);

        $contentView = $this->contentPersister->persist($example, $data, $dimensionAttributes);

        $this->entityManager->persist($example);
        $this->entityManager->flush();

        $contentView['id'] = $example->getId(); // TODO autoincrement id need to be set manually

        return $this->handleView($this->view($contentView, 201));
    }

    public function putAction(Request $request, int $id): Response
    {
        /** @var Example|null $example */
        $example = $this->entityManager->getRepository(Example::class)->findOneBy(['id' => $id]);

        if (!$example) {
            throw new NotFoundHttpException();
        }

        $data = $this->getData($request);
        $dimensionAttributes = $this->getAttributes($request);

        $contentView = $this->contentPersister->persist($example, $data, $dimensionAttributes);

        $this->entityManager->flush();

        return $this->handleView($this->view($contentView));
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
