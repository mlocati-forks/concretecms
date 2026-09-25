<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Block;

use Concrete\Core\Block\Block;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Block\ReferenceColumns;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * What the API knows about the value of a block type: how to describe it, how to hand it to the
 * clients, and how to turn what they send back into what the save() method of the controller wants.
 *
 * Every block type gets the default handler, which works out everything from the db.xml file of the
 * block type; a block type whose form asks for something else than its own columns comes with a
 * Concrete\Block\<Handle>\Api class extending this one.
 *
 * @see \Concrete\Core\Block\BlockController::getApiHandler()
 */
abstract class BlockApiHandler
{
    /**
     * How a field naming a file says which one, described to the clients of the API.
     *
     * @var string
     */
    private const FILE_FORMAT = 'Files are named by UUID, or by ID when the UUID is missing.';

    /**
     * @var \Concrete\Core\Block\BlockController
     */
    protected $controller;

    public function __construct(BlockController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Get what a client must write in a field holding a reference, which is where the value of a
     * block stops being plain data.
     *
     * @param string $reference one of the ReferenceColumns::... constants
     *
     * @return string an empty string when the field holds no reference
     */
    protected function describeReference(string $reference): string
    {
        switch ($reference) {
            case ReferenceColumns::FILE:
                return self::FILE_FORMAT;
            case ReferenceColumns::PAGE:
                return 'A page is named by its ID.';
            case ReferenceColumns::PAGE_TYPE:
                return 'A page type is named by its ID.';
            case ReferenceColumns::PAGE_FEED:
                return 'An RSS feed is named by its ID.';
            case ReferenceColumns::FILE_FOLDER:
                return 'A folder of files is named by its ID.';
            case ReferenceColumns::CONTENT:
                return implode("\n", [
                    'HTML, where you can use:',
                    '- {CCM:BASE_URL} for the URL of the site',
                    '- {CCM:CID_<page ID>} for the URL of a page',
                    '- {CCM:FID_<file>} for the URL of a file',
                    '- {CCM:FID_DL_<file>} for the URL of a file, forcing its download',
                    '- <concrete-picture fID="<file>" /> for displaying an image',
                    self::FILE_FORMAT,
                ]);
        }

        return '';
    }

    /**
     * Get the JSON Schema of the value of the blocks of this block type.
     *
     * @return array<string,mixed>
     */
    abstract public function getApiValueSchema(): array;

    /**
     * Get the value of a block, as the API hands it to its clients.
     *
     * A value is always a JSON object: what a block keeps in more than one row, or in more than one
     * table, lives in a key of its own, holding a list or an object (see the Api class of the faq
     * block for one of them).
     *
     * @return array<string,mixed>
     */
    abstract public function getApiValue(Block $block): array;

    /**
     * Turn a value received by the API into the arguments that the save() method of the controller
     * wants, keeping the settings that the value doesn't mention.
     *
     * The API saves in import mode, so the arguments are the ones a block type reads when the data
     * doesn't come from its own form.
     *
     * @param array<string,mixed> $value
     * @param \Concrete\Core\Block\Block|null $block the block being updated (NULL when a block is being added)
     *
     * @return array<string,mixed>
     */
    abstract public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array;
}
