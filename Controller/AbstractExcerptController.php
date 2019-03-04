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

namespace Sulu\Bundle\ContentBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Exception\ExcerptNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptView;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\ModifyExcerptMessage;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Query\FindExcerptQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class AbstractExcerptController implements ClassResourceInterface
{
    use ControllerTrait;

    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * @var string
     */
    protected $resourceKey;

    public function __construct(
        MessageBusInterface $messageBus,
        ViewHandlerInterface $viewHandler
    ) {
        $this->messageBus = $messageBus;

        $this->setViewHandler($viewHandler);
    }

    public function getAction(Request $request, string $id): Response
    {
        try {
            $message = new FindExcerptQuery($this->getExcerptResourceKey(), $id, $request->query->get('locale'));
            $this->messageBus->dispatch($message);
            $excerpt = $message->getExcerpt();
        } catch (ExcerptNotFoundException $exception) {
            // need to return an empty excerpt-view object because the sulu frontend does not expect any errors here
            // TODO: review this code when subresource handling is implemented in the sulu frontend
            $excerpt = new ExcerptView($this->getExcerptResourceKey(), $id, $request->query->get('locale'));
        }

        return $this->handleView($this->view($excerpt)->setContext($this->createSerializationContext()));
    }

    public function putAction(Request $request, string $id): Response
    {
        $locale = $request->query->get('locale');
        $message = new ModifyExcerptMessage($this->getExcerptResourceKey(), $id, $locale, $request->request->all());
        $this->messageBus->dispatch($message);
        $excerpt = $message->getExcerpt();

        $action = $request->query->get('action');
        if ($action) {
            $this->handleAction($id, $locale, $action);
        }

        return $this->handleView($this->view($excerpt)->setContext($this->createSerializationContext()));
    }

    public function deleteAction(Request $request, string $id): Response
    {
        $this->handleDelete($id, $request->query->get('locale'));

        return $this->handleView($this->view());
    }

    protected function handleAction(string $resourceId, string $locale, string $action): void
    {
        if ('publish' === $action) {
            $this->handlePublish($resourceId, $locale);
        }
    }

    protected function createSerializationContext(): Context
    {
        return (new Context())->setGroups(['fullExcerpt']);
    }

    abstract protected function handlePublish(string $resourceId, string $locale): void;

    abstract protected function handleDelete(string $resourceId, string $locale): void;

    abstract protected function getExcerptResourceKey(): string;
}
