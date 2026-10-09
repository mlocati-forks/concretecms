<?php

declare(strict_types=1);

namespace Concrete\Attribute\Site;

use Concrete\Core\Api\Attribute\AttributeApiHandler;

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
        return 'AttributeValueSite';
    }

    /**
     * {@inheritdoc}
     *
     * A value names the site by ID, which is what a read of it hands over among the fields of the site.
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::createApiValue()
     */
    public function createApiValue($value)
    {
        $id = self::extractApiIdentifier($value, 'id');

        // a request that names nothing of the kind leaves the attribute as it was
        return $id === false ? null : parent::createApiValue($id);
    }
}
