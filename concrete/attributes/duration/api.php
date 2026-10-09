<?php

declare(strict_types=1);

namespace Concrete\Attribute\Duration;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\Model\AttributeKey as AttributeKeyModel;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\DurationSettings;
use Concrete\Core\Entity\Attribute\Value\AbstractValue;
use Concrete\Core\Entity\Attribute\Value\Value\DurationValue;

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
        return 'AttributeKeyDuration';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): string
    {
        return 'AttributeValueDuration';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::describeApiKey()
     */
    public function describeApiKey(Key $key): AttributeKeyModel
    {
        $model = new AttributeKeyModel\Duration();
        $this->fillApiKey($model, $key);
        $model->unit = $this->getUnit($key);

        return $model;
    }

    /**
     * {@inheritdoc}
     *
     * A duration is the number the key keeps, counted in the unit the key names, which is also the
     * number a client writes: the controller answers with that number turned into seconds instead,
     * so writing back what was read stored a duration as many units long as it was seconds.
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiValue()
     */
    public function getApiValue()
    {
        $value = $this->controller->getAttributeValue();
        $duration = $value instanceof AbstractValue ? $value->getValueObject() : null;

        return [
            'value' => $duration instanceof DurationValue ? (int) $duration->getValue() : 0,
            'unit' => $this->getUnit($this->controller->getAttributeKey()),
        ];
    }

    /**
     * Get the unit a value of the key counts in.
     *
     * @param \Concrete\Core\Entity\Attribute\Key\Key|null $key
     */
    protected function getUnit($key): string
    {
        $settings = $key instanceof Key ? $key->getAttributeKeySettings() : null;
        $unit = $settings instanceof DurationSettings ? (string) $settings->getUnitType() : '';

        // the controller counts in seconds the duration of a key that names no unit
        return $unit === '' ? 'seconds' : $unit;
    }
}
