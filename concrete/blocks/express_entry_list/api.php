<?php

declare(strict_types=1);

namespace Concrete\Block\ExpressEntryList;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Express\Search\ColumnSet\ColumnSet;
use Concrete\Core\Express\Search\SearchProvider;
use Concrete\Core\Foundation\Serializer\SafeClassUnserializerTrait;
use Concrete\Core\Search\Field\FieldInterface;
use Concrete\Core\Search\Field\ManagerFactory;
use Concrete\Core\Utility\Service\Xml;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

class Api extends DefaultBlockApiHandler
{
    use SafeClassUnserializerTrait;

    /**
     * The columns holding a JSON list of identifiers.
     *
     * @var string[]
     */
    private const LIST_COLUMNS = [
        'linkedProperties',
        'searchProperties',
        'searchAssociations',
    ];

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $schema = parent::getApiValueSchema();
        foreach (self::LIST_COLUMNS as $name) {
            $schema['properties'][$name] = [
                'type' => 'array',
                'description' => $schema['properties'][$name]['description'] ?? '',
                'items' => ['type' => 'string'],
            ];
        }
        $schema['properties']['columns'] = [
            'type' => 'array',
            'description' => 'The columns of the table, from left to right.',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'key' => [
                        'type' => 'string',
                        'description' => 'The identifier of the column, which is one of the columns the entity offers.',
                    ],
                    'sortDirection' => [
                        'type' => 'string',
                        'enum' => ['asc', 'desc'],
                        'description' => 'How the entries are sorted when the visitors sort them by this column.',
                    ],
                ],
            ],
        ];
        $schema['properties']['defaultSortColumn'] = [
            'type' => 'string',
            'description' => 'The identifier of the column the entries are sorted by (empty: the entries come in the order the database gives them).',
        ];
        $schema['properties']['defaultSortDirection'] = [
            'type' => 'string',
            'enum' => ['asc', 'desc', ''],
            'description' => 'How the entries are sorted by defaultSortColumn.',
        ];
        $schema['properties']['filterFields'] = [
            'type' => 'array',
            'description' => 'The filters the listed entries must match.',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'key' => [
                        'type' => 'string',
                        'description' => 'The identifier of the filter, which is one of the filters the entity offers.',
                    ],
                    'data' => [
                        'type' => 'object',
                        'description' => 'What the filter looks for, in the keys the filter itself names.',
                    ],
                ],
            ],
        ];

        return $schema;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValue()
     */
    public function getApiValue(Block $block): array
    {
        $value = parent::getApiValue($block);
        foreach (self::LIST_COLUMNS as $name) {
            $decoded = is_string($value[$name] ?? null) ? json_decode($value[$name], true) : null;
            $value[$name] = is_array($decoded) ? array_values(array_map('strval', $decoded)) : [];
        }
        $columnSet = $this->unserializeColumnSet((string) ($value['columns'] ?? ''));
        $value['columns'] = [];
        $value['defaultSortColumn'] = '';
        $value['defaultSortDirection'] = '';
        if ($columnSet !== null) {
            foreach ($columnSet->getColumns() as $column) {
                $value['columns'][] = [
                    'key' => (string) $column->getColumnKey(),
                    'sortDirection' => (string) $column->getColumnSortDirection(),
                ];
            }
            $defaultSortColumn = $columnSet->getDefaultSortColumn();
            if ($defaultSortColumn !== null) {
                $value['defaultSortColumn'] = (string) $defaultSortColumn->getColumnKey();
                $value['defaultSortDirection'] = (string) $defaultSortColumn->getColumnSortDirection();
            }
        }
        $filterFields = $this->unserializeFilterFields((string) ($value['filterFields'] ?? ''));
        $value['filterFields'] = [];
        foreach ($filterFields as $filterField) {
            $value['filterFields'][] = [
                'key' => (string) $filterField->getKey(),
                'data' => $this->getFilterFieldData($filterField),
            ];
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        $current = $block === null ? [] : $this->getApiValue($block);
        $arguments = parent::getSaveArgumentsFromApiValue($value, $block);
        foreach (self::LIST_COLUMNS as $name) {
            $arguments[$name] = (array) ($value[$name] ?? $current[$name] ?? []);
        }
        $entity = $this->getEntity((string) ($arguments['exEntityID'] ?? ''));
        $arguments['columns'] = serialize($this->buildColumnSet($entity, $value + $current));
        $arguments['filterFields'] = serialize($this->buildFilterFields($entity, $value['filterFields'] ?? $current['filterFields'] ?? []));

        return $arguments;
    }

    private function getEntity(string $entityID): ?Entity
    {
        if ($entityID === '') {
            return null;
        }
        $entity = app(EntityManagerInterface::class)->find(Entity::class, $entityID);

        return $entity instanceof Entity ? $entity : null;
    }

    /**
     * @param array<string,mixed> $value
     * @throws \Concrete\Core\Error\UserMessageException
     */
    private function buildColumnSet(?Entity $entity, array $value): ColumnSet
    {
        $set = app(ColumnSet::class);
        if ($entity === null) {
            return $set;
        }
        $provider = app(SearchProvider::class, ['entity' => $entity, 'category' => $entity->getAttributeKeyCategory()]);
        $available = $provider->getAvailableColumnSet();
        $defaultSortColumnKey = (string) ($value['defaultSortColumn'] ?? '');
        if ($defaultSortColumnKey !== '') {
            $defaultSortColumn = $available->getColumnByKey($defaultSortColumnKey);
            if ($defaultSortColumn === null) {
                throw new UserMessageException(t('The entity offers no column named %s.', $defaultSortColumnKey));
            }
            $set->setDefaultSortColumn($defaultSortColumn, (string) ($value['defaultSortDirection'] ?? ''));
        }
        foreach (is_array($value['columns'] ?? null) ? $value['columns'] : [] as $item) {
            $key = (string) ($item['key'] ?? '');
            $column = $available->getColumnByKey($key);
            if ($column === null) {
                throw new UserMessageException(t('The entity offers no column named %s.', $key));
            }
            $column->setColumnSortDirection((string) ($item['sortDirection'] ?? ''));
            $set->addColumn($column);
        }

        return $set;
    }

    /**
     * @param array<int,mixed> $items
     * @throws \Concrete\Core\Error\UserMessageException
     * @return \Concrete\Core\Search\Field\FieldInterface[]
     */
    private function buildFilterFields(?Entity $entity, $items): array
    {
        if ($entity === null || !is_array($items)) {
            return [];
        }
        $manager = ManagerFactory::get('express');
        $manager->setExpressCategory($entity->getAttributeKeyCategory());
        $fields = [];
        foreach ($items as $item) {
            $key = (string) ($item['key'] ?? '');
            $field = $manager->getFieldByKey($key);
            if (!$field instanceof FieldInterface) {
                throw new UserMessageException(t('The entity offers no filter named %s.', $key));
            }
            $field->loadDataFromImport($this->describeFilterField($key, $item['data'] ?? []));
            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * @return array<string,mixed>
     */
    private function getFilterFieldData(FieldInterface $field): array
    {
        $element = new \SimpleXMLElement('<filterFields/>');
        $field->export($element);
        $data = json_decode((string) ($element->field[0]->data ?? ''), true);

        return is_array($data) ? $data : [];
    }

    /**
     * Describe a filter the way it is exported, which is how it reads back what it looks for.
     */
    private function describeFilterField(string $key, $data): \SimpleXMLElement
    {
        $element = new \SimpleXMLElement('<field/>');
        $element->addAttribute('key', $key);
        app(Xml::class)->createChildElement($element, 'data', json_encode(is_array($data) ? $data : []));

        return $element;
    }

    /**
     * @return \Concrete\Core\Express\Search\ColumnSet\ColumnSet|null NULL when the block holds no columns
     */
    private function unserializeColumnSet(string $value): ?ColumnSet
    {
        $unserialized = self::safeUnserializeObject($value, ColumnSet::class);

        return $unserialized instanceof ColumnSet ? $unserialized : null;
    }

    /**
     * @return \Concrete\Core\Search\Field\FieldInterface[]
     */
    private function unserializeFilterFields(string $value): array
    {
        $unserialized = self::safeUnserializeObjectArray($value, FieldInterface::class) ?? [];

        return array_values(array_filter($unserialized, static function ($field): bool {
            return $field instanceof FieldInterface;
        }));
    }
}
