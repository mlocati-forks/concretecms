<?php

declare(strict_types=1);

namespace Concrete\Core\Api\OpenApi;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \League\Fractal\Serializer\DataArraySerializer
 */
class WrappedJsonSchemaRefContent implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $ref;

    /**
     * @param string $ref the schema of the object the answer is about
     */
    public function __construct(string $ref)
    {
        $this->ref = $ref;
    }

    #[\ReturnTypeWillChange()]
    public function jsonSerialize()
    {
        return [
            'application/json' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'data' => [
                            '$ref' => '#' . $this->ref,
                        ],
                    ],
                ],
            ],
        ];
    }
}
