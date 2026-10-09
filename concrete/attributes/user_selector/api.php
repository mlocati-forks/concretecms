<?php

declare(strict_types=1);

namespace Concrete\Attribute\UserSelector;

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
        return 'AttributeValueUserSelector';
    }
}
