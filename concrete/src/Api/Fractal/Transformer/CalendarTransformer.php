<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\TokenScopes;
use Concrete\Core\Entity\Calendar\Calendar;
use Concrete\Core\Permission\Checker;
use League\Fractal\TransformerAbstract;

class CalendarTransformer extends TransformerAbstract
{

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
        $site = $calendar->getSite();
        $data['site_id'] = $site === null ? null : (int) $site->getSiteID();
        return $data;
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
