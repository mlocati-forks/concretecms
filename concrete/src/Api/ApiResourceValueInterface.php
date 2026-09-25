<?php

namespace Concrete\Core\Api;

use League\Fractal\Resource\ResourceInterface;

/**
 * Implemented by the attribute types, and by the block types that were building the value the API
 * hands to its clients before the block API handlers existed.
 *
 * A block type doesn't need it: it says what its value looks like with an Api class beside its
 * controller.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\AttributeValueTransformer
 * @see \Concrete\Core\Api\Block\BlockApiHandler
 */
interface ApiResourceValueInterface
{
    /**
     * Get the resource building the value that the API hands to its clients (NULL when there is none).
     */
    public function getApiValueResource(): ?ResourceInterface;
}
