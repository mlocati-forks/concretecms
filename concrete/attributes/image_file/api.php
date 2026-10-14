<?php

declare(strict_types=1);

namespace Concrete\Attribute\ImageFile;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\FileIdentifier;

defined('C5_EXECUTE') or die('Access Denied.');

class Api extends AttributeApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): string
    {
        return 'AttributeValueImageFile';
    }

    /**
     * {@inheritdoc}
     *
     * A value names the file by UUID, the way everything else of this API does, and a read hands the
     * file over whole: the uuid of what came back is what a write takes.
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::createApiValue()
     */
    public function createApiValue($value)
    {
        $identifier = self::extractApiIdentifier($value, 'uuid');
        if ($identifier === false || $identifier === null || $identifier === '') {
            // a file added before UUIDs existed has none, and travels by the ID a read hands over beside it
            $identifier = self::extractApiIdentifier($value, 'id');
        }
        if ($identifier === false) {
            // a request that names nothing of the kind leaves the attribute as it was
            return null;
        }
        $fileID = app(FileIdentifier::class)->fromApi($identifier);

        return parent::createApiValue($fileID === 0 ? null : $fileID);
    }
}
