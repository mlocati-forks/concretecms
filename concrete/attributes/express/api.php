<?php

declare(strict_types=1);

namespace Concrete\Attribute\Express;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\Model\AttributeKey as AttributeKeyModel;
use Concrete\Core\Api\OpenApi\SpecProperty;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\ExpressSettings;
use Concrete\Core\Entity\Express\Entity;

defined('C5_EXECUTE') or die('Access Denied.');

class Api extends AttributeApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiKeySchema()
     */
    public function getApiKeySchema(): string
    {
        return 'AttributeKeyExpress';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::describeApiKey()
     */
    public function describeApiKey(Key $key): AttributeKeyModel
    {
        $settings = $key->getAttributeKeySettings();
        $entity = $settings instanceof ExpressSettings ? $settings->getEntity() : null;
        $model = new AttributeKeyModel\Express();
        $this->fillApiKey($model, $key);
        // a value names an entry of that entity alone
        $model->express_entity_id = $entity instanceof Entity ? (string) $entity->getId() : '';

        return $model;
    }

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

    /**
     * {@inheritdoc}
     *
     * A value names the entry by its public identifier, which is what a read of it hands over among
     * the fields of the entry, in a list of its own: the type keeps one entry, the way the form of
     * the site writes it.
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::createApiValue()
     */
    public function createApiValue($value)
    {
        $value = self::unwrapApiData($value);
        if (is_array($value) && !isset($value['id'])) {
            $value = reset($value);
        }

        $identifier = self::extractApiIdentifier($value, 'id');

        // a request that names nothing of the kind leaves the attribute as it was
        return $identifier === false ? null : parent::createApiValue($identifier);
    }
}
