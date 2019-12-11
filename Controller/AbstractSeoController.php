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
use Sulu\Bundle\ContentBundle\Model\Seo\Exception\SeoNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Seo\Message\ModifySeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\Query\FindSeoQuery;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class AbstractSeoController implements ClassResourceInterface
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
            $message = new FindSeoQuery($this->getSeoResourceKey(), $id, $request->query->get('locale'));
            $this->messageBus->dispatch($message);
            $seo = $message->getSeo();
        } catch (HandlerFailedException $exception) {
            if (!$exception->getPrevious() instanceof SeoNotFoundException) {
                throw $exception;
            }

            // need to return an empty seo-view object because the sulu frontend does not expect any errors here
            // TODO: review this code when subresource handling is implemented in the sulu frontend
            $seo = new SeoView($this->getSeoResourceKey(), $id, $request->query->get('locale'));
        }

        return $this->handleView($this->view($seo)->setContext($this->createSerializationContext()));
    }

    public function putAction(Request $request, string $id): Response
    {
        $locale = $request->query->get('locale');
        $message = new ModifySeoMessage($this->getSeoResourceKey(), $id, $locale, $request->request->all());
        $this->messageBus->dispatch($message);
        $seo = $message->getSeo();

        $action = $request->query->get('action');
        if ($action) {
            $this->handleAction($id, $locale, $action);
        }

        return $this->handleView($this->view($seo)->setContext($this->createSerializationContext()));
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
        return (new Context())->setGroups(['fullSeo']);
    }

    abstract protected function handlePublish(string $resourceId, string $locale): void;

    abstract protected function handleDelete(string $resourceId, string $locale): void;

    abstract protected function getSeoResourceKey(): string;
}
