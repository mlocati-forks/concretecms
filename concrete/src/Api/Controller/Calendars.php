<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\CalendarEventTransformer;
use Concrete\Core\Api\Fractal\Transformer\CalendarTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Api\Traits\SupportsSiteTrait;
use Concrete\Core\Calendar\Calendar\CalendarService;
use Concrete\Core\Calendar\Event\EventList;
use Concrete\Core\Calendar\Event\EventService;
use Concrete\Core\Entity\Calendar\Calendar;
use Concrete\Core\Entity\Calendar\CalendarEvent;
use Concrete\Core\Permission\Checker;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

defined('C5_EXECUTE') or die('Access Denied.');

class Calendars extends ApiController
{
    use SupportsSiteTrait;

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/calendars",
     *     tags={"calendars"},
     *     operationId="getCalendars",
     *     summary="List the calendars of this installation",
     *     security={
     *         {"authorization": {"calendars:read"}}
     *     },
     *     @OA\Parameter(
     *         name="site",
     *         in="query",
     *         description="ID of the site whose calendars are answered, which GET /sites lists: left out, it is the site the domain of this request answers for. The calendars that belong to no site come whatever it says.",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Calendar")
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="This installation has no site of the ID the request names",
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listCalendars()
    {
        $site = $this->getRequestedSiteOrCurrent();
        $calendars = [];
        foreach ($this->app->make(CalendarService::class)->getList() as $calendar) {
            if ($this->belongsToSite($calendar->getSite(), $site) && $this->canViewCalendar($calendar)) {
                $calendars[] = $calendar;
            }
        }

        return new Collection($calendars, new CalendarTransformer(), Resources::RESOURCE_CALENDARS);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/calendars/{calendarID}",
     *     tags={"calendars"},
     *     operationId="getCalendarById",
     *     summary="Find a calendar by its ID",
     *     security={
     *         {"authorization": {"calendars:read"}}
     *     },
     *     @OA\Parameter(
     *         name="calendarID",
     *         in="path",
     *         description="ID of the calendar to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Calendar")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="This installation has no calendar of this ID",
     *     ),
     * )
     *
     * @param int|string $calendarID
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function read($calendarID)
    {
        $calendar = $this->getCalendar($calendarID);
        if ($calendar === null) {
            return $this->error(t('Calendar not found.'), 404);
        }

        return new Item($calendar, new CalendarTransformer(), Resources::RESOURCE_CALENDARS);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/calendars/{calendarID}/events",
     *     tags={"calendars"},
     *     operationId="getCalendarEvents",
     *     summary="List the events of a calendar",
     *     description="Every approved event of the calendar comes back, the oldest first.",
     *     security={
     *         {"authorization": {"calendar_events:read"}}
     *     },
     *     @OA\Parameter(
     *         name="calendarID",
     *         in="path",
     *         description="ID of the calendar holding the events",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/CalendarEvent")
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="This installation has no calendar of this ID",
     *     ),
     * )
     *
     * @param int|string $calendarID
     *
     * @return \League\Fractal\Resource\Collection|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listEvents($calendarID)
    {
        $calendar = $this->getCalendar($calendarID);
        if ($calendar === null) {
            return $this->error(t('Calendar not found.'), 404);
        }
        $list = new EventList();
        $list->filterByCalendar($calendar);
        $list->sortBy('e.eventID', 'asc');

        return new Collection($list->getResults(), new CalendarEventTransformer(), Resources::RESOURCE_CALENDAR_EVENTS);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/calendars/{calendarID}/events/{eventID}",
     *     tags={"calendars"},
     *     operationId="getCalendarEventById",
     *     summary="Find an event of a calendar by its ID",
     *     security={
     *         {"authorization": {"calendar_events:read"}}
     *     },
     *     @OA\Parameter(
     *         name="calendarID",
     *         in="path",
     *         description="ID of the calendar holding the event",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="eventID",
     *         in="path",
     *         description="ID of the event to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="includes",
     *         in="query",
     *         explode=false,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(type="string", enum={"custom_attributes"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/CalendarEvent")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="This calendar has no event of this ID",
     *     ),
     * )
     *
     * @param int|string $calendarID
     * @param int|string $eventID
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function readEvent($calendarID, $eventID)
    {
        $calendar = $this->getCalendar($calendarID);
        if ($calendar === null) {
            return $this->error(t('Calendar not found.'), 404);
        }
        $event = $this->app->make(EventService::class)->getByID((int) $eventID);
        if (!$event instanceof CalendarEvent || $event->getCalendar()->getID() !== $calendar->getID()) {
            return $this->error(t('Event not found.'), 404);
        }

        return new Item($event, new CalendarEventTransformer(), Resources::RESOURCE_CALENDAR_EVENTS);
    }

    /**
     * Get the calendar of an ID, where the request may view it.
     *
     * @param int|string $calendarID
     */
    private function getCalendar($calendarID): ?Calendar
    {
        $calendar = $this->app->make(CalendarService::class)->getByID((int) $calendarID);
        if (!$calendar instanceof Calendar || !$this->canViewCalendar($calendar)) {
            return null;
        }

        return $calendar;
    }

    private function canViewCalendar(Calendar $calendar): bool
    {
        $checker = new Checker($calendar);

        return (bool) $checker->canViewCalendar();
    }
}
