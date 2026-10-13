<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

$router->get('/calendars', '\Concrete\Core\Api\Controller\Calendars::listCalendars')
    ->setScopes('calendars:read')
;

$router->get('/calendars/{calendarID}', '\Concrete\Core\Api\Controller\Calendars::read')
    ->setRequirement('calendarID', '[0-9]+')
    ->setScopes('calendars:read')
;

$router->get('/calendars/{calendarID}/events', '\Concrete\Core\Api\Controller\Calendars::listEvents')
    ->setRequirement('calendarID', '[0-9]+')
    ->setScopes('calendar_events:read')
;

$router->get('/calendars/{calendarID}/events/{eventID}', '\Concrete\Core\Api\Controller\Calendars::readEvent')
    ->setRequirement('calendarID', '[0-9]+')
    ->setRequirement('eventID', '[0-9]+')
    ->setScopes('calendar_events:read')
;
