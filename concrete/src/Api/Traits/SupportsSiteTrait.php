<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Traits;

use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Error\UserMessageHttpException;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Site\Service;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Read the site a request works with, for the things an installation of several sites keeps apart.
 *
 * @property \Concrete\Core\Application\Application $app
 * @property \Concrete\Core\Http\Request $request
 */
trait SupportsSiteTrait
{
    /**
     * Get the site that the site parameter of a request names, for an answer that a site only narrows.
     *
     * @throws \Concrete\Core\Error\UserMessageHttpException where it names a site this installation has not
     *     got, or one the request may not read
     *
     * @return \Concrete\Core\Entity\Site\Site|null NULL where the request names no site, which leaves the
     *     answer as wide as it comes
     */
    protected function getRequestedSite(): ?Site
    {
        $siteID = $this->request->query->getInt('site');
        if ($siteID === 0) {
            return null;
        }
        $site = $siteID > 0 ? $this->app->make(Service::class)->getByID($siteID) : null;
        if (!$site instanceof Site) {
            throw new UserMessageHttpException(t('Invalid site query parameter found. No site of this installation has this ID.'), 400);
        }
        if (!(new Checker($site))->canViewSiteInSelector()) {
            throw new UserMessageHttpException(t('You do not have access to read properties about this site.'), 403);
        }

        return $site;
    }

    /**
     * Get the site a request works with, for an answer that is about one site at a time: the one it
     * names, or the one the domain it came to answers for.
     *
     * @throws \Concrete\Core\Error\UserMessageHttpException
     */
    protected function getRequestedSiteOrCurrent(): Site
    {
        $site = $this->getRequestedSite() ?? $this->app->make(Service::class)->getSite();
        if (!$site instanceof Site) {
            throw new \RuntimeException('There is no site associated with this installation');
        }

        return $site;
    }

    /**
     * Whether something belongs to the site in question: what belongs to no site belongs to every one of them.
     */
    protected function belongsToSite(?Site $of, Site $site): bool
    {
        return $of === null || (int) $of->getSiteID() === (int) $site->getSiteID();
    }
}
