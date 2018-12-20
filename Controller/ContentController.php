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

use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Content\Message\ModifyContentMessage;
use Sulu\Bundle\ContentBundle\Model\Content\Query\FindContentQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class ContentController implements ClassResourceInterface
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

    /**
     * @var string
     */
    protected $defaultType;

    public function __construct(
        MessageBusInterface $messageBus,
        ViewHandlerInterface $viewHandler,
        string $defaultType = 'default'
    ) {
        $this->messageBus = $messageBus;
        $this->defaultType = $defaultType;

        $this->setViewHandler($viewHandler);
    }

    public function cgetAction(): Response
    {
        throw new NotFoundHttpException();
    }

    public function getAction(Request $request, string $resourceId): Response
    {
        try {
            $message = new FindContentQuery($this->getResourceKey(), $resourceId, $request->query->get('locale'));
            $this->messageBus->dispatch($message);
            $content = $message->getContent();
        } catch (ContentNotFoundException $exception) {
            $content = [
                'template' => $this->defaultType,
            ];
        }

        return $this->handleView($this->view($content));
    }

    public function putAction(Request $request, string $resourceId): Response
    {
        $data = $request->request->all();
        unset($data['template']);
        $payload = [
            'type' => $request->get('template'),
            'data' => $data,
        ];

        $locale = $request->query->get('locale');
        $message = new ModifyContentMessage($this->getResourceKey(), $resourceId, $locale, $payload);
        $this->messageBus->dispatch($message);
        $content = $message->getContent();

        $action = $request->query->get('action');
        if ($action) {
            $this->handleAction($resourceId, $locale, $action);
        }

        return $this->handleView($this->view($content));
    }

    protected function handleAction(string $resourceId, string $locale, string $action): void
    {
        if ('publish' === $action) {
            $this->handlePublish($resourceId, $locale);
        }
    }

    abstract protected function handlePublish(string $resourceId, string $locale): void;

    abstract protected function getResourceKey(): string;
}
