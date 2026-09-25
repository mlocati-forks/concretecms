<?php

declare(strict_types=1);

namespace Concrete\Tests\Api;

use Concrete\Core\Api\OpenApi\SourceRegistry;
use Concrete\Tests\TestCase;
use OpenApi\Generator;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The API clients, and the OAuth scopes of the installation, are built out of the generated OpenAPI
 * specification: a path that isn't there simply doesn't exist for them.
 */
class OpenApiSpecTest extends TestCase
{
    /**
     * @var \OpenApi\Annotations\OpenApi|null
     */
    private static $spec;

    public function testTheBlockTypePathsAreInTheSpec(): void
    {
        $paths = [];
        foreach ($this->getSpec()->paths as $path) {
            $paths[] = $path->path;
        }

        static::assertContains('/ccm/api/1.0/block_types', $paths);
        static::assertContains('/ccm/api/1.0/block_types/{blockTypeHandle}', $paths);
    }

    public function testTheOpenApiPathIsInTheSpec(): void
    {
        $paths = [];
        foreach ($this->getSpec()->paths as $path) {
            $paths[] = $path->path;
        }

        static::assertContains('/ccm/api/1.0/system/openapi', $paths);
    }

    /**
     * @return array<int,string[]>
     */
    public function provideNewScopes(): array
    {
        return [
            ['block_types:read'],
            ['pages:areas:sort_blocks'],
            ['system:openapi:read'],
        ];
    }

    /**
     * @dataProvider provideNewScopes
     */
    public function testTheScopeIsInTheSpec(string $expectedScope): void
    {
        $scopes = [];
        foreach ($this->getSpec()->components->securitySchemes as $scheme) {
            foreach ($scheme->flows[0]->scopes as $scope => $description) {
                $scopes[] = $scope;
            }
        }

        static::assertContains($expectedScope, $scopes);
    }

    /**
     * Every scope used by an operation must be declared in the security scheme it refers to, since
     * the ones that aren't never reach the OAuth2Scope table.
     *
     * @see \Concrete\Core\Api\Command\SynchronizeScopesCommandHandler
     */
    public function testEveryUsedScopeIsDeclared(): void
    {
        $declared = [];
        foreach ($this->getSpec()->components->securitySchemes as $scheme) {
            foreach ($scheme->flows[0]->scopes as $scope => $description) {
                $declared[$scheme->securityScheme][] = $scope;
            }
        }
        $undeclared = [];
        foreach ($this->getSpec()->paths as $path) {
            foreach (['get', 'post', 'put', 'delete'] as $method) {
                // swagger-php fills the operations a path doesn't have with a placeholder string
                $operation = $path->{$method};
                if (!is_object($operation) || !is_array($operation->security)) {
                    continue;
                }
                foreach ($operation->security as $security) {
                    foreach ($security as $schemeName => $scopes) {
                        foreach ((array) $scopes as $scope) {
                            if (!in_array($scope, $declared[$schemeName] ?? [], true)) {
                                $undeclared[] = "{$path->path}: {$schemeName}/{$scope}";
                            }
                        }
                    }
                }
            }
        }

        static::assertSame([], $undeclared);
    }

    public function testEveryReferencedSchemaExists(): void
    {
        $defined = [];
        foreach ($this->getSpec()->components->schemas as $schema) {
            $defined[] = $schema->schema;
        }
        $referenced = [];
        foreach ($this->findRefs(json_decode(json_encode($this->getSpec()), true)) as $ref) {
            if (strpos($ref, '#/components/schemas/') === 0) {
                $referenced[] = substr($ref, strlen('#/components/schemas/'));
            }
        }

        static::assertSame([], array_values(array_unique(array_diff($referenced, $defined))));
    }

    /**
     * @return \OpenApi\Annotations\OpenApi
     */
    private function getSpec()
    {
        if (self::$spec === null) {
            $sourceRegistry = new SourceRegistry();
            $sourceRegistry->addDefaultSources();
            self::$spec = Generator::scan($sourceRegistry->getSources());
        }

        return self::$spec;
    }

    /**
     * @param mixed $data
     *
     * @return string[]
     */
    private function findRefs($data): array
    {
        $result = [];
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($key === '$ref' && is_string($value)) {
                    $result[] = $value;
                } else {
                    $result = array_merge($result, $this->findRefs($value));
                }
            }
        }

        return $result;
    }
}
