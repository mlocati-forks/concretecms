<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Resources;
use Concrete\Core\Api\TokenScopes;
use Concrete\Core\Entity\Calendar\Calendar;
use Concrete\Core\Permission\Checker;
use League\Fractal\TransformerAbstract;

class CalendarTransformer extends TransformerAbstract
{

    protected $availableIncludes = [
        'site',
    ];

    /**
     * @param Calendar $calendar
     * @return array
     */
    public function transform(Calendar $calendar)
    {
        if (!$this->isVisible($calendar)) {
            return ['id' => $calendar->getID()];
        }

        $data['id'] = $calendar->getID();
        $data['name'] = $calendar->getName();
        return $data;
    }

    public function includeSite(Calendar $calendar)
    {
        $site = $calendar->getSite();
        return $this->item($site, new SiteTransformer(), Resources::RESOURCE_SITES);
    }

    /**
     * Whether the request may see the whole of a calendar: the scopes of its token settle
     * it first, and then the permissions, the ones the endpoint serving these things asks about.
     */
    protected function isVisible(Calendar $calendar): bool
    {
        return app(TokenScopes::class)->has('calendars:read')
            && (bool) (new Checker($calendar))->canViewCalendar();
    }
}
