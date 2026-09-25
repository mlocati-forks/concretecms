<?php

declare(strict_types=1);

namespace Concrete\Block\TopicList;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;

defined('C5_EXECUTE') or die('Access Denied.');

class Api extends DefaultBlockApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        $arguments = parent::getSaveArgumentsFromApiValue($value, $block);
        $arguments['externalTarget'] = empty($arguments['cParentID']) ? 0 : 1;

        return $arguments;
    }
}
