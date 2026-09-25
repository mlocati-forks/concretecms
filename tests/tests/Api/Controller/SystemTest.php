<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Controller;

use Concrete\Core\Api\Controller\System;
use Concrete\Core\Api\OpenApi\SourceRegistry;
use Concrete\Core\Api\OpenApi\SpecGenerator;
use Concrete\Core\Http\Request;
use Concrete\Tests\TestCase;
use Mockery as M;
use OpenApi\Generator;
use Symfony\Component\Yaml\Yaml;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests the specification that the API serves of itself.
 *
 * @see \Concrete\Core\Api\Controller\System::openapi()
 */
class SystemTest extends TestCase
{
    public function testTheSpecificationIsServedAsJson(): void
    {
        $response = $this->getSpecification('');

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        $specification = json_decode($response->getContent(), true);
        static::assertIsArray($specification);
        static::assertArrayHasKey('/ccm/api/1.0/system/openapi', $specification['paths']);
    }

    public function testTheSpecificationIsServedAsYaml(): void
    {
        $response = $this->getSpecification('yaml');

        static::assertSame('application/yaml', $response->headers->get('Content-Type'));
        $specification = Yaml::parse($response->getContent());
        static::assertIsArray($specification);
        static::assertArrayHasKey('/ccm/api/1.0/system/openapi', $specification['paths']);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getSpecification(string $format)
    {
        $request = Request::createFromGlobals();
        if ($format !== '') {
            $request->query->set('format', $format);
        }
        $sourceRegistry = new SourceRegistry();
        $sourceRegistry->addDefaultSources();
        // the real one adds to the specification the Express objects of the site, which live in the database
        $specGenerator = M::mock(SpecGenerator::class);
        $specGenerator->shouldReceive('getSpec')->andReturn(Generator::scan($sourceRegistry->getSources()));

        return (new System($request, $specGenerator))->openapi();
    }
}
