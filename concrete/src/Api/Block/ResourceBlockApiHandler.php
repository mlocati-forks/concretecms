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
 * handing over what its resource builds and writing back what the client sends, untouched.
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
        return $value;
    }
}
