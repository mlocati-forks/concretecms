<?php

namespace Concrete\Core\Api;

use League\Fractal\Resource\ResourceInterface;

/**
 * What the attribute types, and the block types that predate the block API handlers, build their value with.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\AttributeValueTransformer
 * @see \Concrete\Core\Api\Block\BlockApiHandler
 */
interface ApiResourceValueInterface
{
    public function getApiValueResource(): ?ResourceInterface;
}
