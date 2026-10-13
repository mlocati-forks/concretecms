<?php

declare(strict_types=1);

namespace Concrete\Tests\Reference;

use Concrete\Core\Reference\ReferenceKind;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Reference\ReferenceKind
 */
class ReferenceKindTest extends TestCase
{
    public function testEveryKindHasAValueOfItsOwn(): void
    {
        $repeated = array_keys(array_filter(array_count_values($this->getKinds()), static function (int $times): bool {
            return $times > 1;
        }));

        static::assertSame([], $repeated);
    }

    public function testEveryKindIsNamedInLowerCaseWithHyphens(): void
    {
        foreach ($this->getKinds() as $kind) {
            static::assertMatchesRegularExpression('{^[a-z]+(-[a-z]+)*$}', $kind);
        }
    }

    public function testTheKindsOfReferenceACifFileTranslates(): void
    {
        static::assertTrue(ReferenceKind::isTranslatedInCif(ReferenceKind::PAGE));
        static::assertTrue(ReferenceKind::isTranslatedInCif(ReferenceKind::CONTENT));
        // the kinds that only tell the clients of the API what a field names
        static::assertFalse(ReferenceKind::isTranslatedInCif(ReferenceKind::FILE_SET));
        static::assertFalse(ReferenceKind::isTranslatedInCif(''));
    }

    /**
     * @return string[] the value of every kind of reference
     */
    private function getKinds(): array
    {
        $kinds = [];
        foreach ((new \ReflectionClass(ReferenceKind::class))->getReflectionConstants() as $constant) {
            if ($constant->isPublic()) {
                $kinds[] = (string) $constant->getValue();
            }
        }

        return $kinds;
    }
}
