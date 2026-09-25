<?php

declare(strict_types=1);

namespace Concrete\Block\ShareThisPage;

use Concrete\Core\Api\Block\BlockApiHandler;
use Concrete\Core\Block\Block;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Sharing\ShareThisPage\Service;
use Concrete\Core\Sharing\ShareThisPage\ServiceList;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The share_this_page block type keeps a record for every service it offers, and not a record for
 * every block: the value is the list of the services, in the order they are displayed.
 */
class Api extends BlockApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $handles = [];
        foreach (ServiceList::get() as $service) {
            $handles[] = (string) $service->getHandle();
        }

        return [
            'type' => 'object',
            'properties' => [
                'services' => [
                    'type' => 'array',
                    'description' => 'The services the visitors can share the page with, in the order they are displayed.',
                    'items' => [
                        'type' => 'string',
                        'enum' => $handles,
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getApiValue()
     */
    public function getApiValue(Block $block): array
    {
        $services = app(Connection::class)->fetchFirstColumn(
            'SELECT service FROM btShareThisPage WHERE bID = ? ORDER BY displayOrder ASC',
            [$block->getBlockID()]
        );

        return ['services' => array_map('strval', $services)];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        $current = $block === null ? [] : $this->getApiValue($block);
        $services = $value['services'] ?? $current['services'] ?? [];
        $handles = [];
        foreach (is_array($services) ? $services : [] as $service) {
            // a service may be provided by a package that this site doesn't have
            $service = Service::getByHandle((string) $service);
            if (is_object($service)) {
                $handles[] = (string) $service->getHandle();
            }
        }

        return ['service' => $handles];
    }
}
