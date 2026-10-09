<?php

declare(strict_types=1);

namespace Concrete\Attribute\Express;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\OpenApi\SpecProperty;
use Concrete\Core\Entity\Attribute\Key\Key;

defined('C5_EXECUTE') or die('Access Denied.');

class Api extends AttributeApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiSpecProperty()
     */
    public function getApiSpecProperty(Key $key): SpecProperty
    {
        // the controller reads the entry by its public identifier, the way the express endpoints name one
        return new SpecProperty(
            (string) $key->getAttributeKeyHandle(),
            (string) $key->getAttributeKeyDisplayName(),
            'string',
            'uuid'
        );
    }
}
