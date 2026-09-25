<?php

declare(strict_types=1);

namespace Concrete\TestHelpers\Api;

use Concrete\Core\Api\OpenApi\SourceRegistry;
use OpenApi\Generator;

defined('C5_EXECUTE') or die('Access Denied.');

trait SchemaFieldsTrait
{
    /**
     * @var array<string,mixed>|null
     */
    private static $apiSchemas;

    /**
     * Check that an answer carries the fields that a schema of the specification describes, and no
     * other one.
     *
     * @param array<string,mixed> $answer
     */
    protected function assertFieldsAre(string $schema, array $answer): void
    {
        $fields = $this->getSchemaFields($schema);
        sort($fields);
        $keys = array_keys($answer);
        sort($keys);
        static::assertSame($fields, $keys);
    }

    /**
     * Check what an answer carries, field by field, however its fields are ordered: a model built
     * out of public properties hands them over in an order that depends on the PHP version.
     *
     * @param mixed[] $expected
     * @param mixed[] $answer
     */
    protected static function assertAnswerIs(array $expected, array $answer): void
    {
        static::assertSame(self::sortedByKey($expected), self::sortedByKey($answer));
    }

    /**
     * @param mixed $value
     *
     * @return mixed the arrays named by their keys sorted by key, the lists left in their order
     */
    private static function sortedByKey($value)
    {
        if (!is_array($value)) {
            return $value;
        }
        $sorted = [];
        foreach ($value as $key => $item) {
            $sorted[$key] = self::sortedByKey($item);
        }
        if ($sorted !== [] && array_keys($sorted) !== range(0, count($sorted) - 1)) {
            ksort($sorted);
        }

        return $sorted;
    }

    /**
     * @return string[]
     */
    protected function getSchemaFields(string $schema): array
    {
        if (self::$apiSchemas === null) {
            $sourceRegistry = new SourceRegistry();
            $sourceRegistry->addDefaultSources();
            $spec = json_decode((string) json_encode(Generator::scan($sourceRegistry->getSources())), true);
            self::$apiSchemas = $spec['components']['schemas'] ?? [];
        }
        if (!isset(self::$apiSchemas[$schema])) {
            static::fail("The OpenAPI specification describes no {$schema} schema.");
        }
        $described = self::$apiSchemas[$schema];
        $fields = array_keys($described['properties'] ?? []);
        // a composed schema holds the one it shares its fields with, then its own ones
        foreach ($described['allOf'] ?? [] as $part) {
            $fields = array_merge(
                $fields,
                isset($part['$ref'])
                    ? $this->getSchemaFields(substr($part['$ref'], strlen('#/components/schemas/')))
                    : array_keys($part['properties'] ?? [])
            );
        }

        return $fields;
    }
}
