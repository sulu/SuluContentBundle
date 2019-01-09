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
use Sulu\Bundle\ContentBundle\Model\Seo\Exception\SeoNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Seo\Message\ModifySeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\Query\FindSeoQuery;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class SeoController implements ClassResourceInterface
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

    public function cgetAction(): Response
    {
        throw new NotFoundHttpException();
    }

    public function getAction(Request $request, string $resourceId): Response
    {
        try {
            $message = new FindSeoQuery($this->getResourceKey(), $resourceId, $request->query->get('locale'));
            $this->messageBus->dispatch($message);
            $seo = $message->getSeo();
        } catch (SeoNotFoundException $exception) {
            // the form in the frontend requires an object with all properties of the seo-view
            // TODO: return null when the form in the frontend does not require an object with all properties anymore
            $seo = new SeoView($this->getResourceKey(), $resourceId, $request->query->get('locale'));
        }

        return $this->handleView($this->view($seo));
    }

    public function putAction(Request $request, string $resourceId): Response
    {
        $locale = $request->query->get('locale');
        $message = new ModifySeoMessage($this->getResourceKey(), $resourceId, $locale, $request->request->all());
        $this->messageBus->dispatch($message);
        $seo = $message->getSeo();

        $action = $request->query->get('action');
        if ($action) {
            $this->handleAction($resourceId, $locale, $action);
        }

        return $this->handleView($this->view($seo));
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
