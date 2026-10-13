<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Block;

use Concrete\Core\Api\ReferenceCatalog;
use Concrete\Core\Block\Block;
use Concrete\Core\Block\BlockController;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A block type whose form asks for something else than its own columns comes with a
 * Concrete\Block\<Handle>\Api class extending this one.
 *
 * @see \Concrete\Core\Block\BlockController::getApiHandler()
 */
abstract class BlockApiHandler
{
    /**
     * @var \Concrete\Core\Block\BlockController
     */
    protected $controller;

    public function __construct(BlockController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @param string $reference one of the \Concrete\Core\Reference\ReferenceKind::... constants
     * @return string an empty string when the field holds no reference
     */
    protected function describeReference(string $reference): string
    {
        return app(ReferenceCatalog::class)->describe($reference);
    }

    /**
     * Get the description of this block type written for the clients of the API, which a block type
     * whose own description says nothing to them answers with.
     *
     * @return string an empty string when the description of the block type does the job
     */
    public function getCustomApiDescription(): string
    {
        return '';
    }

    /**
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

    /**
     * Runs once the API has added or updated the block, with the block saved.
     */
    public function afterApiWrite(Block $block): void
    {
    }
}
