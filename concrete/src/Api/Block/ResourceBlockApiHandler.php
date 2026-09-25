<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Block;

use Concrete\Core\Block\Block;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The value of a block type that builds it itself, with the interface that the API honoured before
 * the handlers existed.
 *
 * No block type of the core does, but one out there may: this handler keeps it working as it did,
 * handing over what its resource builds and writing back what the client sends, with just the
 * booleans turned into the numbers a table holds.
 *
 * @see \Concrete\Core\Api\ApiResourceValueInterface
 *
 * @property \Concrete\Core\Block\BlockController&\Concrete\Core\Api\ApiResourceValueInterface $controller
 */
class ResourceBlockApiHandler extends BlockApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        // the block type builds its value on its own, so there is nothing we can say about it
        return [
            'type' => 'object',
            'x-concrete-undescribed' => true,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getApiValue()
     */
    public function getApiValue(Block $block): array
    {
        $resource = $this->controller->getApiValueResource();

        return $resource === null ? [] : (array) $resource->getTransformer()->transform($resource->getData());
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        // that's what the API did with the value of every block before the handlers existed
        return $this->numberBooleans($value);
    }

    /**
     * Turn the booleans of a value into the 0 and 1 a block type stores.
     *
     * A block type that builds its own value decides what to do with it, and what it usually does is
     * handing it over to the database: a boolean bound as it is becomes an empty string, which an
     * integer column refuses.
     *
     * @param array<mixed> $value
     *
     * @return array<mixed>
     */
    private function numberBooleans(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_bool($item)) {
                $value[$key] = $item ? 1 : 0;
            } elseif (is_array($item)) {
                $value[$key] = $this->numberBooleans($item);
            }
        }

        return $value;
    }
}
