<?php

declare(strict_types=1);

namespace Concrete\TestHelpers\Api;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Traits\SupportsSiteTrait;
use Concrete\Core\Entity\Site\Site;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A controller that hands over what the trait reads of a request, the way the controllers of the API
 * use it.
 *
 * @see \Concrete\Core\Api\Traits\SupportsSiteTrait
 */
class RequestedSiteController extends ApiController
{
    use SupportsSiteTrait;

    public function getNamedSite(): ?Site
    {
        return $this->getRequestedSite();
    }

    public function getSiteToWorkWith(): Site
    {
        return $this->getRequestedSiteOrCurrent();
    }

    public function isOfSite(?Site $of, Site $site): bool
    {
        return $this->belongsToSite($of, $site);
    }
}
