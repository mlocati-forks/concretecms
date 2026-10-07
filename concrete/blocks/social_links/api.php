<?php

declare(strict_types=1);

namespace Concrete\Block\SocialLinks;

use Concrete\Core\Api\Block\BlockApiHandler;
use Concrete\Core\Block\Block;
use Concrete\Core\Database\Connection\Connection;

defined('C5_EXECUTE') or die('Access Denied.');

class Api extends BlockApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getCustomApiDescription()
     */
    public function getCustomApiDescription(): string
    {
        return 'Displays the social networks of the site, named by the ID that the social_links of a site answer give.';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'links' => [
                    'type' => 'array',
                    'description' => 'The social links of the site displayed by the block, in the order they are displayed.',
                    'items' => [
                        'type' => 'integer',
                        'description' => 'The ID of one of the social links of the site, as the social_links of a site answer give them.',
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
        $links = app(Connection::class)->fetchFirstColumn(
            'SELECT slID FROM btSocialLinks WHERE bID = ? ORDER BY displayOrder ASC',
            [$block->getBlockID()]
        );

        return ['links' => array_map('intval', $links)];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        $current = $block === null ? [] : $this->getApiValue($block);
        $links = [];
        foreach ((array) ($value['links'] ?? $current['links'] ?? []) as $link) {
            $link = (int) $link;
            if ($link > 0) {
                $links[] = $link;
            }
        }

        return ['slID' => $links];
    }
}
