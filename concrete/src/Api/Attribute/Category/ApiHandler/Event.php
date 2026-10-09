<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Attribute\Category\ApiHandler;

use Concrete\Core\Api\Attribute\Category\ApiHandler;

defined('C5_EXECUTE') or die('Access Denied.');

class Event extends ApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\Category\ApiHandler::getCategoryDescription()
     */
    public function getCategoryDescription(): string
    {
        return t('Attributes of calendar events');
    }
}
