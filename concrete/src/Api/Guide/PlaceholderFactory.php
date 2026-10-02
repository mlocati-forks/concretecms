<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Guide;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Site\Service;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Guide\GuideGenerator
 */
class PlaceholderFactory
{
    /**
     * @var \Concrete\Core\Config\Repository\Repository
     */
    protected $config;

    /**
     * @var \Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface
     */
    protected $resolverManager;

    /**
     * @var \Concrete\Core\Site\Service
     */
    protected $siteService;

    public function __construct(Repository $config, ResolverManagerInterface $resolverManager, Service $siteService)
    {
        $this->config = $config;
        $this->resolverManager = $resolverManager;
        $this->siteService = $siteService;
    }

    /**
     * @return array<string,string> the values, keyed by the name of the placeholder (without the
     */
    public function getPlaceholders(): array
    {
        $site = $this->siteService->getSite();
        // the paths of the pages are appended to it, so it has to end with a slash
        $siteRootUrl = rtrim($this->resolveUrl('/'), '/') . '/';
        $apiBaseUrl = rtrim($this->resolveUrl('/ccm/api/1.0'), '/');

        return [
            'siteName' => $site === null ? '' : (string) $site->getSiteName(),
            'concreteVersion' => (string) $this->config->get('concrete.version'),
            'siteRootUrl' => $siteRootUrl,
            'apiBaseUrl' => $apiBaseUrl,
            'openApiSpecificationUrl' => $apiBaseUrl . '/system/openapi',
            'sites' => $this->getSiteList($siteRootUrl),
        ];
    }

    /**
     * @param string $siteRootUrl the URL where this installation answers
     */
    protected function getSiteList(string $siteRootUrl): string
    {
        $lines = [];
        foreach ($this->siteService->getList() as $site) {
            // the name of a site is whatever its administrators typed, so it gets a field of its own
            $name = '- Site name: ' . $site->getSiteName();
            $url = $this->getSiteUrl($site, $siteRootUrl);
            if ($url === '') {
                $lines[] = $name;
            } else {
                // without the break the two fields are a single line once the markdown is rendered
                $lines[] = $name . '<br/>';
                $lines[] = '  Site root URL: ' . $url;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Get the URL where a site is served: the default site answers where this installation does,
     * while the others answer where their canonical URL says, which may well not be set.
     *
     * @param string $siteRootUrl the URL where this installation answers
     *
     * @return string empty string if there's no telling where the site answers
     */
    protected function getSiteUrl(Site $site, string $siteRootUrl): string
    {
        if ($site->isDefault()) {
            return $siteRootUrl;
        }
        $url = (string) $site->getSiteCanonicalURL();

        return $url === '' ? '' : rtrim($url, '/') . '/';
    }

    protected function resolveUrl(string $path): string
    {
        return (string) $this->resolverManager->resolve([$path]);
    }
}
