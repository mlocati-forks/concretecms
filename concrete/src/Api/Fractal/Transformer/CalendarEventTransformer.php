<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Resources;
use Concrete\Core\Api\TokenScopes;
use Concrete\Core\Entity\Calendar\Calendar;
use Concrete\Core\Entity\Calendar\CalendarEvent;
use Concrete\Core\Permission\Checker;
use League\Fractal\TransformerAbstract;

class CalendarEventTransformer extends TransformerAbstract
{

    protected $availableIncludes = [
        'custom_attributes',
    ];

    protected $defaultIncludes = [
        'version',
    ];

    /**
     * @param Calendar $calendar
     * @return array
     */
    public function transform(CalendarEvent $event)
    {
        if (!$this->isVisible($event)) {
            return ['id' => $event->getID()];
        }

        $data['id'] = $event->getID();
        $data['name'] = $event->getName();
        return $data;
    }

    public function includeCustomAttributes(CalendarEvent $event)
    {
        $values = $event->getObjectAttributeCategory()->getAttributeValues($event->getSelectedVersion());
        return $this->collection($values, new AttributeValueTransformer(), Resources::RESOURCE_CUSTOM_ATTRIBUTES);
    }

    public function includeVersion(CalendarEvent $event)
    {
        $version = $event->getSelectedVersion();
        return $this->item($version, new CalendarEventVersionTransformer(), Resources::RESOURCE_CALENDAR_EVENT_VERSIONS);
    }

    /**
     * Whether the request may see the whole of an event: the scopes of its token settle
     * it first, and then the permissions, the ones the endpoint serving these things asks about.
     */
    protected function isVisible(CalendarEvent $event): bool
    {
        if (!app(TokenScopes::class)->has('calendar_events:read')) {
            return false;
        }
        $calendar = $event->getCalendar();

        // the endpoint serving the events of a calendar asks about the calendar itself
        return !$calendar instanceof Calendar || (bool) (new Checker($calendar))->canViewCalendar();
    }
}
