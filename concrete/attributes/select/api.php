<?php

declare(strict_types=1);

namespace Concrete\Attribute\Select;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\Model\AttributeKey as AttributeKeyModel;
use Concrete\Core\Api\OpenApi\SpecProperty;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\SelectSettings;
use Concrete\Core\Entity\Attribute\Value\Value\SelectValueOption;
use Concrete\Core\Entity\Attribute\Value\Value\SelectValueOptionList;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @property \Concrete\Attribute\Select\Controller $controller
 */
class Api extends AttributeApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiKeySchema()
     */
    public function getApiKeySchema(): string
    {
        return 'AttributeKeySelect';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): string
    {
        return 'AttributeValueSelect';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::describeApiKey()
     */
    public function describeApiKey(Key $key): AttributeKeyModel
    {
        $settings = $key->getAttributeKeySettings();
        $model = new AttributeKeyModel\Select();
        $this->fillApiKey($model, $key);
        $model->options = $this->getOptions($key);
        $model->allow_multiple_values = $settings instanceof SelectSettings ? (bool) $settings->getAllowMultipleValues() : false;
        $model->allow_other_values = $settings instanceof SelectSettings ? (bool) $settings->getAllowOtherValues() : false;

        return $model;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiSpecProperty()
     */
    public function getApiSpecProperty(Key $key): SpecProperty
    {
        return new SpecProperty(
            (string) $key->getAttributeKeyHandle(),
            (string) $key->getAttributeKeyDisplayName(),
            'array',
            null,
            // an option of the key is named by its ID, and a value of its own by the text of it
            ['oneOf' => [['type' => 'integer'], ['type' => 'string']]]
        );
    }

    /**
     * {@inheritdoc}
     *
     * A value names the options it picks by ID, and one the key hasn't got by the text of it: a key
     * that allows a value of its own keeps it among its options, the way the form of the site does,
     * while a key that doesn't ignores it.
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::createApiValue()
     */
    public function createApiValue($json)
    {
        $options = [];
        foreach ((array) $json as $item) {
            $option = $this->findOption($item);
            if ($option !== null) {
                $options[] = $option;
            }
        }

        // the controller keeps as many of them as the key takes
        return $this->controller->createAttributeValue($options);
    }

    /**
     * Get the option that a value of the request names, which the key gains where it is one of its
     * own and the key allows it.
     *
     * @param mixed $item the ID of an option of the key, or the text of a value
     */
    protected function findOption($item): ?SelectValueOption
    {
        $key = $this->controller->getAttributeKey();
        if (!$key instanceof Key) {
            return null;
        }
        if (!is_string($item)) {
            return $this->findOptionByID($key, (int) $item);
        }
        $value = trim($item);
        if ($value === '') {
            return null;
        }
        // the site compares the text of a value the way its database does, so the controller asks it
        $option = $this->controller->getOptionByValue($value, $key);

        return $option instanceof SelectValueOption ? $option : $this->addOption($key, $value);
    }

    /**
     * Get an option of the key by ID.
     */
    protected function findOptionByID(Key $key, int $id): ?SelectValueOption
    {
        if ($id === 0) {
            return null;
        }
        foreach ($this->getOptionList($key) as $option) {
            if ((int) $option->getSelectAttributeOptionID() === $id) {
                return $option;
            }
        }

        return null;
    }

    /**
     * Add a value of its own to the options of the key, where the key allows one.
     *
     * @return \Concrete\Core\Entity\Attribute\Value\Value\SelectValueOption|null NULL where the key
     *                                                                            takes its options as they are
     */
    protected function addOption(Key $key, string $value): ?SelectValueOption
    {
        $settings = $key->getAttributeKeySettings();
        if (!$settings instanceof SelectSettings || !$settings->getAllowOtherValues()) {
            return null;
        }
        $list = $settings->getOptionList();
        if (!$list instanceof SelectValueOptionList) {
            return null;
        }
        $option = new SelectValueOption();
        $option->setOptionList($list);
        $option->setIsEndUserAdded(true);
        $option->setDisplayOrder(count($this->getOptionList($key)));
        $option->setSelectAttributeOptionValue($value);

        return $option;
    }

    /**
     * Get the options a value of the key is picked out of.
     *
     * @return array<int,array<string,mixed>>
     */
    protected function getOptions(Key $key): array
    {
        $options = [];
        foreach ($this->getOptionList($key) as $option) {
            $model = new AttributeKeyModel\Select\Option();
            $model->id = (int) $option->getSelectAttributeOptionID();
            $model->value = (string) $option->getSelectAttributeOptionValue();
            $model->display_value = (string) $option->getSelectAttributeOptionDisplayValue('string');
            $options[] = $model->jsonSerialize();
        }

        return $options;
    }

    /**
     * @return \Concrete\Core\Entity\Attribute\Value\Value\SelectValueOption[]
     */
    protected function getOptionList(Key $key): array
    {
        $settings = $key->getAttributeKeySettings();
        $list = $settings instanceof SelectSettings ? $settings->getOptionList() : null;
        if (!$list instanceof SelectValueOptionList) {
            return [];
        }
        $options = [];
        foreach ($list->getOptions() as $option) {
            if ($option instanceof SelectValueOption) {
                $options[] = $option;
            }
        }

        return $options;
    }
}
