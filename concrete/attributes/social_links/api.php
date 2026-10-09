<?php

declare(strict_types=1);

namespace Concrete\Attribute\SocialLinks;

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
        return 'AttributeValueSocialLinks';
    }

    /**
     * {@inheritdoc}
     *
     * A value carries the links themselves, which a read hands over wrapped in a data property.
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::createApiValue()
     */
    public function createApiValue($value)
    {
        return parent::createApiValue(self::unwrapApiData($value));
    }
}
