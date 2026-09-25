<?php

declare(strict_types=1);

namespace Concrete\Block\Gallery;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Api\FileIdentifier;
use Concrete\Core\Block\Block;
use Concrete\Core\Block\ReferenceColumns;
use Concrete\Core\Database\Connection\Connection;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The gallery block type keeps its images, and how every one of them is displayed, in two tables of
 * their own: the value carries them along with the row of the main table.
 */
class Api extends DefaultBlockApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $schema = parent::getApiValueSchema();
        $schema['properties']['images'] = [
            'type' => 'array',
            'description' => 'The images of the gallery, in the order they are displayed.',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'file' => [
                        'type' => ['string', 'integer'],
                        'description' => 'The file holding the image.' . "\n" . $this->describeReference(ReferenceColumns::FILE),
                        'x-concrete-reference' => ReferenceColumns::FILE,
                    ],
                    'displayChoices' => [
                        'type' => 'object',
                        'description' => 'How the image is displayed: caption, hover_caption and size, plus the ones added by the block types built upon this one.',
                        'additionalProperties' => ['type' => 'string'],
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
        $value['images'] = [];
        $connection = app(Connection::class);
        $fileIdentifier = app(FileIdentifier::class);
        $rows = $connection->fetchAllAssociative('SELECT eID, fID FROM btGalleryEntries WHERE bID = ? ORDER BY idx', [$block->getBlockID()]);
        foreach ($rows as $row) {
            $displayChoices = $connection->fetchAllKeyValue('SELECT dcKey, value FROM btGalleryEntryDisplayChoices WHERE entryID = ?', [$row['eID']]);
            $value['images'][] = [
                'file' => $fileIdentifier->forApi($row['fID']),
                // the save() method keeps a choice only when it says something
                'displayChoices' => array_filter(array_map('strval', $displayChoices), 'strlen'),
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
        $arguments = parent::getSaveArgumentsFromApiValue($value, $block);
        $current = $block === null ? [] : $this->getApiValue($block);
        $images = $value['images'] ?? $current['images'] ?? [];
        $fileIdentifier = app(FileIdentifier::class);
        $entries = [];
        foreach (is_array($images) ? $images : [] as $image) {
            if (!is_array($image) || !isset($image['file'])) {
                continue;
            }
            $displayChoices = [];
            foreach ((array) ($image['displayChoices'] ?? []) as $key => $choice) {
                $displayChoices[(string) $key] = ['value' => (string) $choice];
            }
            $entries[] = [
                'id' => $fileIdentifier->fromApi($image['file']),
                'displayChoices' => $displayChoices,
            ];
        }
        // the save() method reads the images out of the JSON document that the form of the block type builds
        $arguments['field_json'] = json_encode($entries);

        return $arguments;
    }
}
