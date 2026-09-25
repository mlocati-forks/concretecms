<?php

declare(strict_types=1);

namespace Concrete\Block\DesktopSiteActivity;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The desktop_site_activity block type keeps the kinds of activity it displays in a single column,
 * as a JSON array: the API hands them over as a list, which is what its save() method wants too.
 */
class Api extends DefaultBlockApiHandler
{
    /**
     * The kinds of activity that can be displayed, as the form of the block type lists them.
     *
     * @var string[]
     */
    private const TYPES = [
        'form_submissions',
        'survey_results',
        'signups',
        'conversation_messages',
        'workflow',
    ];

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $schema = parent::getApiValueSchema();
        $schema['properties']['types'] = [
            'type' => 'array',
            'description' => 'The kinds of activity displayed.',
            'items' => [
                'type' => 'string',
                'enum' => self::TYPES,
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
        $value['types'] = $this->getTypes($value['types'] ?? null);

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

        // the save() method turns the list into the JSON array kept by the column
        return ['types' => $this->getTypes($value['types'] ?? $current['types'] ?? null)];
    }

    /**
     * Get the kinds of activity out of what the column holds, or out of what a client sends.
     *
     * @return string[]
     */
    private function getTypes($value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_intersect(array_map('strval', $value), self::TYPES));
    }
}
