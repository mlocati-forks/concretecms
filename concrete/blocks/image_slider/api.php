<?php

declare(strict_types=1);

namespace Concrete\Block\ImageSlider;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Api\FileIdentifier;
use Concrete\Core\Block\Block;
use Concrete\Core\Editor\LinkAbstractor;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The image_slider block type keeps its slides in a table of their own: the value carries them
 * along with the row of the main table, and its save() method reads them as the parallel lists that
 * its form sends, one for every field.
 */
class Api extends DefaultBlockApiHandler
{
    /**
     * The table holding the slides.
     *
     * @var string
     */
    private const ENTRIES_TABLE = 'btImageSliderEntries';

    /**
     * The value of the linkType field of the form: the slide links to a page.
     *
     * @var int
     */
    private const LINK_TYPE_PAGE = 1;

    /**
     * The value of the linkType field of the form: the slide links to an URL.
     *
     * @var int
     */
    private const LINK_TYPE_URL = 2;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $schema = parent::getApiValueSchema();
        $schema['properties']['slides'] = $this->describeTableRows(self::ENTRIES_TABLE, 'The slides of the slideshow.');

        return $schema;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValue()
     */
    public function getApiValue(Block $block): array
    {
        $value = parent::getApiValue($block);
        $value['slides'] = $this->getTableRows($block, self::ENTRIES_TABLE, 'sortOrder');

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        $arguments = parent::getSaveArgumentsFromApiValue($value, $block);
        $current = $block === null ? [] : $this->getApiValue($block);
        $arguments += ['fID' => [], 'title' => [], 'description' => [], 'sortOrder' => [], 'linkURL' => [], 'internalLinkCID' => [], 'linkType' => []];
        $fileIdentifier = app(FileIdentifier::class);
        $sortOrder = 0;
        foreach ((array) ($value['slides'] ?? $current['slides'] ?? []) as $slide) {
            if (!is_array($slide)) {
                continue;
            }
            $internalLinkCID = (int) ($slide['internalLinkCID'] ?? 0);
            $linkURL = (string) ($slide['linkURL'] ?? '');
            $arguments['fID'][] = empty($slide['fID']) ? 0 : $fileIdentifier->fromApi($slide['fID']);
            $arguments['title'][] = (string) ($slide['title'] ?? '');
            // the save() method abstracts the rich text the way the editor of the form sends it
            $arguments['description'][] = LinkAbstractor::translateFromEditMode((string) ($slide['description'] ?? ''));
            // the slides are numbered after their place in the list, which is the order they come in
            $arguments['sortOrder'][] = $sortOrder;
            $arguments['linkURL'][] = $linkURL;
            $arguments['internalLinkCID'][] = $internalLinkCID;
            if ($internalLinkCID !== 0) {
                $arguments['linkType'][] = self::LINK_TYPE_PAGE;
            } else {
                $arguments['linkType'][] = $linkURL === '' ? 0 : self::LINK_TYPE_URL;
            }
            $sortOrder++;
        }

        return $arguments;
    }
}
