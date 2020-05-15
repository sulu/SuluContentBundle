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
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\RouteBundle\Model\RouteInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapAlternateLink;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\PortalInformation;

class ContentSitemapProvider implements SitemapProviderInterface
{
    const ROUTE_ALIAS = 'route';
    const CONTENT_RICH_ENTITY_ALIAS = ContentWorkflowInterface::CONTENT_RICH_ENTITY_CONTEXT_KEY;
    const LOCALIZED_DIMENSION_CONTENT_ALIAS = 'localizedDimensionContent';
    const LOCALIZED_DIMENSION_ALIAS = 'localizedDimension';

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
    protected $kernelEnvironment;

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
    protected $alias;

    /**
     * @param string $kernelEnvironment Inject parameter "kernel.environment" here
     * @param string $entityClassName Classname that's used in the route table
     */
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

    public function build($page, $scheme, $host): array
    {
        $limit = self::PAGE_SIZE;
        $offset = (int) (($page - 1) * $limit);

        $portalInformations = $this->webspaceManager->findPortalInformationsByHostIncludingSubdomains(
            $host, $this->kernelEnvironment
        );

        /** @var PortalInformation|null $portalInformation */
        $portalInformation = array_shift($portalInformations);

        if (!$portalInformation) {
            return [];
        }

        $webspace = $portalInformation->getWebspace();
        $defaultLocale = $webspace->getDefaultLocalization()->getLocale(Localization::DASH);

        $routes = $this->getRoutes($limit, $offset);
        $groupedRoutes = $this->groupRoutesByEntityId($routes);

        $result = [];

        foreach ($groupedRoutes as $entityId => $entityRoutes) {
            $mainRoute = null;

            if (\array_key_exists($defaultLocale, $entityRoutes)) {
                $mainRoute = $entityRoutes[$defaultLocale];
                unset($entityRoutes[$defaultLocale]);
            } else {
                $mainRoute = array_shift($entityRoutes);
            }

            $sitemapUrl = $this->generateSitemapUrl(
                $mainRoute,
                $entityRoutes,
                $webspace->getKey(),
                $host,
                $scheme
            );

            if (null === $sitemapUrl) {
                continue;
            }

            $result[] = $sitemapUrl;
        }

        return $result;
    }

    public function createSitemap($scheme, $host): Sitemap
    {
        return new Sitemap(
            $this->getAlias(),
            $this->getMaxPage($scheme, $host)
        );
    }

    public function getMaxPage($scheme, $host): int
    {
        $queryBuilder = $this->createRoutesQueryBuilder();
        try {
            $amount = $queryBuilder
                ->select('COUNT(' . self::ROUTE_ALIAS . ')')
                ->getQuery()
                ->getSingleScalarResult();

            return (int) ceil($amount / self::PAGE_SIZE);
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * @param RouteInterface[] $routes
     *
     * @return mixed[]
     */
    protected function groupRoutesByEntityId(array $routes): array
    {
        $result = [];

        foreach ($routes as $route) {
            $entityId = $route->getEntityId();

            if (!\array_key_exists($entityId, $result)) {
                $result[$entityId] = [];
            }

            $result[$entityId][$route->getLocale()] = $route;
        }

        return $result;
    }

    /**
     * @return mixed[]
     */
    protected function getRoutes(int $limit, int $offset): array
    {
        $queryBuilder = $this->createRoutesQueryBuilder();

        return $queryBuilder
            ->select(self::ROUTE_ALIAS)
            ->distinct()
            ->orderBy(self::ROUTE_ALIAS . '.entityId', 'asc')
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getResult();
    }

    protected function createRoutesQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        return $queryBuilder
            ->from($this->entityClassName, self::CONTENT_RICH_ENTITY_ALIAS)
            ->innerJoin(self::CONTENT_RICH_ENTITY_ALIAS . '.dimensionContents', self::LOCALIZED_DIMENSION_CONTENT_ALIAS)
            ->innerJoin(self::LOCALIZED_DIMENSION_CONTENT_ALIAS . '.dimension', self::LOCALIZED_DIMENSION_ALIAS)
            ->innerJoin($this->routeClassName, self::ROUTE_ALIAS, Join::WITH, self::ROUTE_ALIAS . '.entityId = ' . self::CONTENT_RICH_ENTITY_ALIAS . '.' . $this->getEntityIdField())
            ->where(self::LOCALIZED_DIMENSION_ALIAS . '.stage = :stage')
            ->andWhere(self::ROUTE_ALIAS . '.entityClass = :entityClass')
            ->andWhere(self::ROUTE_ALIAS . '.history = :history')
            ->andWhere(self::ROUTE_ALIAS . '.locale = ' . self::LOCALIZED_DIMENSION_ALIAS . '.locale')
            ->setParameters([
                'stage' => DimensionInterface::STAGE_LIVE,
                'entityClass' => $this->entityClassName,
                'history' => false,
            ]);
    }

    /**
     * @param RouteInterface[] $alternateRoutes
     */
    protected function generateSitemapUrl(
        RouteInterface $route,
        array $alternateRoutes,
        string $webspaceKey,
        string $host,
        string $scheme
    ): ?SitemapUrl {
        $url = $this->generateUrl($route, $webspaceKey, $host, $scheme);

        if (!$url) {
            return null;
        }

        $sitemapUrl = new SitemapUrl(
            $url,
            $route->getLocale(),
            $route->getLocale()
        );

        foreach ($alternateRoutes as $alternateRoute) {
            $alternateUrl = $this->generateUrl($alternateRoute, $webspaceKey, $host, $scheme);

            if (!$alternateUrl) {
                continue;
            }

            $alternateLink = new SitemapAlternateLink($alternateUrl, $alternateRoute->getLocale());

            $sitemapUrl->addAlternateLink($alternateLink);
        }

        return $sitemapUrl;
    }

    protected function generateUrl(
        RouteInterface $route,
        string $webspaceKey,
        string $host,
        string $scheme
    ): ?string {
        $url = $this->webspaceManager->findUrlByResourceLocator(
            $route->getPath(),
            $this->kernelEnvironment,
            $route->getLocale(),
            $webspaceKey,
            $host,
            $scheme
        );

        return $url ?: null;
    }

    protected function getEntityIdField(): string
    {
        return 'id';
    }

    public function getAlias()
    {
        return $this->alias;
    }
}
