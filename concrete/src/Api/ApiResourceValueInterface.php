<?php

namespace Concrete\Core\Api;

use League\Fractal\Resource\ResourceInterface;

/**
 * What the attribute types, and the block types that predate the block API handlers, build their value with.
 *
 * @deprecated a block type hands its value over through its own API handler, an attribute type
 *             through its one
 *
 * @see \Concrete\Core\Api\Block\BlockApiHandler
 * @see \Concrete\Core\Api\Attribute\AttributeApiHandler
 */
interface ApiResourceValueInterface
{
    public function getApiValueResource(): ?ResourceInterface;
}
