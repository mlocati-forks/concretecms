<?php

declare(strict_types=1);

namespace Concrete\Core\Api\OpenApi;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \League\Fractal\Serializer\DataArraySerializer
 */
class WrappedJsonSchemaRefArrayContent implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $ref;

    /**
     * @var string|null
     */
    protected $metaRef;

    /**
     * @param string $ref the schema of the objects the answer carries
     * @param string|null $metaRef the schema of what the answer carries besides its objects, as the cursor of a list walked with one
     */
    public function __construct(string $ref, ?string $metaRef = null)
    {
        $this->ref = $ref;
        $this->metaRef = $metaRef;
    }

    #[\ReturnTypeWillChange()]
    public function jsonSerialize()
    {
        $properties = [
            'data' => [
                'type' => 'array',
                'items' => [
                    '$ref' => '#' . $this->ref,
                ],
            ],
        ];
        if ($this->metaRef !== null) {
            $properties['meta'] = [
                '$ref' => '#' . $this->metaRef,
            ];
        }

        return [
            'application/json' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => $properties,
                ],
            ],
        ];
    }
}
