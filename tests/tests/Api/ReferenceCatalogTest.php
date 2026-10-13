<?php

declare(strict_types=1);

namespace Concrete\Tests\Api;

use Concrete\Core\Api\Fractal\Transformer\ReferenceKindTransformer;
use Concrete\Core\Api\ReferenceCatalog;
use Concrete\Core\Reference\ReferenceKind;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A field of a value naming something of the site says which kind of thing it names, and a client
 * reads what the kinds mean without knowing them beforehand.
 *
 * @see \Concrete\Core\Api\ReferenceCatalog
 * @see \Concrete\Core\Api\Controller\System::references()
 */
class ReferenceCatalogTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testEveryKindOfReferenceIsHandedOver(): void
    {
        static::assertSame($this->getDeclaredKinds(), $this->getCatalog()->getList());
    }

    public function testEveryKindIsDescribed(): void
    {
        $catalog = $this->getCatalog();

        foreach ($catalog->getList() as $reference) {
            static::assertNotSame('', $catalog->describe($reference), $reference);
        }
    }

    public function testEveryEndpointIsNamedWithoutThePrefixOfTheApi(): void
    {
        $catalog = $this->getCatalog();

        foreach ($catalog->getList() as $reference) {
            $endpoint = $catalog->getEndpoint($reference);
            if ($endpoint !== '') {
                static::assertMatchesRegularExpression('{^/[a-z_]+(/|$)}', $endpoint, $reference);
            }
        }
    }

    public function testAKindIsHandedOverWithTheEndpointListingItsThings(): void
    {
        $answer = $this->transform(ReferenceKind::FILE_SET);

        $this->assertFieldsAre('ReferenceKind', $answer);
        static::assertSame(ReferenceKind::FILE_SET, $answer['handle']);
        static::assertSame('/file_sets', $answer['endpoint']);
        static::assertStringContainsString('GET /file_sets', $answer['description']);
    }

    public function testAKindWhoseThingsAreReadSomewhereElseNamesNoEndpoint(): void
    {
        // the entries of an Express entity are read through a path named after the entity itself
        static::assertSame('', $this->transform(ReferenceKind::EXPRESS_ENTRY)['endpoint']);
    }

    public function testAKindThisInstallationKnowsNothingAbout(): void
    {
        $catalog = $this->getCatalog();

        static::assertSame('', $catalog->describe('something-else'));
        static::assertSame('', $catalog->getEndpoint('something-else'));
        static::assertSame('', $catalog->describe(''));
    }

    /**
     * @return array<string,mixed>
     */
    private function transform(string $reference): array
    {
        return (new ReferenceKindTransformer())->transform($reference);
    }

    /**
     * @return string[] the kinds of reference a block type may declare
     */
    private function getDeclaredKinds(): array
    {
        $kinds = [];
        foreach ((new \ReflectionClass(ReferenceKind::class))->getReflectionConstants() as $constant) {
            if ($constant->isPublic()) {
                $kinds[] = (string) $constant->getValue();
            }
        }

        return $kinds;
    }

    private function getCatalog(): ReferenceCatalog
    {
        return app(ReferenceCatalog::class);
    }
}
