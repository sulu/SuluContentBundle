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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Sitemap;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class ContentSitemapProvider implements SitemapProviderInterface
{
    use ContentSitemapProviderTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var WebspaceManagerInterface
     */
    protected $webspaceManager;

    /**
     * @var string
     */
    private $kernelEnvironment;

    /**
     * @var string
     */
    protected $entityClassName;

    /**
     * @var string
     */
    protected $routeClassName;

    /**
     * @var string
     */
    private $alias;

    public function __construct(
        EntityManagerInterface $entityManager,
        WebspaceManagerInterface $webspaceManager,
        string $kernelEnvironment,
        string $entityClassName,
        string $routeClassName,
        string $alias
    ) {
        $this->entityManager = $entityManager;
        $this->webspaceManager = $webspaceManager;
        $this->kernelEnvironment = $kernelEnvironment;
        $this->entityClassName = $entityClassName;
        $this->routeClassName = $routeClassName;
        $this->alias = $alias;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function getWebspaceManager(): WebspaceManagerInterface
    {
        return $this->webspaceManager;
    }

    protected function getKernelEnvironment(): string
    {
        return $this->kernelEnvironment;
    }

    protected function getEntityClass(): string
    {
        return $this->entityClassName;
    }

    protected function getRouteClass(): string
    {
        return $this->routeClassName;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }
}
