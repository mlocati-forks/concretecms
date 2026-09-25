<?php

declare(strict_types=1);

namespace Concrete\Block\RssDisplayer;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The value of the rss_displayer block type is the row of its table, but its save() method builds
 * the format of the dates out of the two fields its form uses: a list of ready-made formats, and a
 * custom one.
 *
 * @property \Concrete\Block\RssDisplayer\Controller $controller
 */
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
        $dateFormat = (string) ($arguments['dateFormat'] ?? '');
        if (array_key_exists($dateFormat, $this->controller->getDefaultDateTimeFormats())) {
            $arguments['standardDateFormat'] = $dateFormat;
        } else {
            $arguments['standardDateFormat'] = ':custom:';
            $arguments['customDateFormat'] = $dateFormat;
        }

        return $arguments;
    }
}
