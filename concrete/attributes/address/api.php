<?php

declare(strict_types=1);

namespace Concrete\Attribute\Address;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\Model\AttributeKey as AttributeKeyModel;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\AddressSettings;

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
        return 'AttributeKeyAddress';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): string
    {
        return 'AttributeValueAddress';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::describeApiKey()
     */
    public function describeApiKey(Key $key): AttributeKeyModel
    {
        $settings = $key->getAttributeKeySettings();
        $model = new AttributeKeyModel\Address();
        $this->fillApiKey($model, $key);
        $model->default_country = $settings instanceof AddressSettings ? (string) $settings->getDefaultCountry() : '';
        $model->countries = $this->getCountries($settings);

        return $model;
    }

    /**
     * Get the countries the key is meant for, which are the ones its form offers.
     *
     * @param \Concrete\Core\Entity\Attribute\Key\Settings\Settings|null $settings
     *
     * @return string[] empty where the key is meant for any country of the site
     */
    protected function getCountries($settings): array
    {
        if (!$settings instanceof AddressSettings || !$settings->hasCustomCountries()) {
            return [];
        }
        $countries = [];
        foreach ((array) $settings->getCustomCountries() as $country) {
            $countries[] = (string) $country;
        }

        return $countries;
    }
}
