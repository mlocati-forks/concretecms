<?php

declare(strict_types=1);

namespace Concrete\Attribute\CalendarEvent;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\OpenApi\SpecProperty;
use Concrete\Core\Calendar\Event\EventService;
use Concrete\Core\Entity\Attribute\Key\Key;

defined('C5_EXECUTE') or die('Access Denied.');

class Api extends AttributeApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): string
    {
        return 'AttributeValueCalendarEvent';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiSpecProperty()
     */
    public function getApiSpecProperty(Key $key): SpecProperty
    {
        return new SpecProperty(
            (string) $key->getAttributeKeyHandle(),
            (string) $key->getAttributeKeyDisplayName(),
            'integer'
        );
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::createApiValue()
     */
    public function createApiValue($value)
    {
        // a client names an event by ID, which is what the value keeps, but the controller wants
        // the event itself
        $id = self::extractApiIdentifier($value, 'id');
        if ($id === false) {
            // a request that names nothing of the kind leaves the attribute as it was
            return null;
        }
        $id = (int) $id;

        return $this->controller->createAttributeValue($id === 0 ? null : app(EventService::class)->getByID($id));
    }
}
