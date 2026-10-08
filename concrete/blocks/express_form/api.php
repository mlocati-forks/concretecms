<?php

declare(strict_types=1);

namespace Concrete\Block\ExpressForm;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;

defined('C5_EXECUTE') or die('Access Denied.');

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
        $schema['properties']['exFormID']['description'] = implode("\n", array_filter([
            (string) ($schema['properties']['exFormID']['description'] ?? ''),
            'GET /express_entities names the entities this API serves, each with its forms.',
        ]));

        return $schema;
    }
}
