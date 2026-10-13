<?php

declare(strict_types=1);

namespace Concrete\Tests\Api;

use Concrete\Core\Api\ReferenceCatalog;
use Concrete\Core\Reference\ReferenceKind;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A field of a value naming something of the site says which kind of thing it names, in a sentence
 * written once for every kind.
 *
 * @see \Concrete\Core\Api\ReferenceCatalog
 */
class ReferenceCatalogTest extends TestCase
{
    public function testEveryKindOfReferenceIsDescribed(): void
    {
        $catalog = $this->getCatalog();

        foreach ($this->getDeclaredKinds() as $reference) {
            static::assertNotSame('', $catalog->describe($reference), $reference);
        }
    }

    public function testAKindThisInstallationKnowsNothingAbout(): void
    {
        $catalog = $this->getCatalog();

        static::assertSame('', $catalog->describe('something-else'));
        static::assertSame('', $catalog->describe(''));
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
