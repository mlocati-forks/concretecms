<?php

declare(strict_types=1);

namespace Concrete\Attribute\UserSelector;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Reference\ReferenceKind;

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
        return 'AttributeValueUserSelector';
    }

    /**
     * {@inheritdoc}
     *
     * A value names the user by ID, which is what a read of it hands over among the fields of the user.
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::createApiValue()
     */
    public function createApiValue($value)
    {
        $id = self::extractApiIdentifier($value, 'id');

        // a request that names nothing of the kind leaves the attribute as it was
        return $id === false ? null : parent::createApiValue($id);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiValueReference()
     */
    public function getApiValueReference(Key $key): string
    {
        return ReferenceKind::USER;
    }
}
