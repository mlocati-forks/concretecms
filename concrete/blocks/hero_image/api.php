<?php

declare(strict_types=1);

namespace Concrete\Block\HeroImage;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The value of the hero_image block type is the row of its table, but its save() method reads where
 * the button points to from the destination picker of its form, which keeps the page, the file and
 * the external URL in fields of its own.
 */
class Api extends DefaultBlockApiHandler
{
    /**
     * The key the destination picker of the button is built with.
     *
     * @var string
     */
    private const LINK_PICKER = 'imageLink';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        $arguments = parent::getSaveArgumentsFromApiValue($value, $block);
        $key = self::LINK_PICKER;
        if ((int) ($arguments['buttonInternalLinkCID'] ?? 0) !== 0) {
            $arguments["{$key}__which"] = 'page';
            $arguments["{$key}_page"] = $arguments['buttonInternalLinkCID'];
        } elseif ((int) ($arguments['buttonFileLinkID'] ?? 0) !== 0) {
            $arguments["{$key}__which"] = 'file';
            $arguments["{$key}_file"] = $arguments['buttonFileLinkID'];
        } elseif ((string) ($arguments['buttonExternalLink'] ?? '') !== '') {
            $arguments["{$key}__which"] = 'external_url';
            $arguments["{$key}_external_url"] = $arguments['buttonExternalLink'];
        } else {
            $arguments["{$key}__which"] = 'none';
        }

        return $arguments;
    }
}
