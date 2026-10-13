<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\File;

use Concrete\Core\Api\Fractal\Transformer\FileSetTransformer;
use Concrete\Core\File\Set\Set;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A set gathers files of the file manager, and a client reads which ones there are to name one of
 * them: the document_library block files what is uploaded to it in a set, and the files endpoint
 * hands over the files of one.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\FileSetTransformer
 */
class FileSetTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testASetSaysWhatItIsCalledAndWhoItBelongsTo(): void
    {
        $transformed = $this->transform(12, 'Portfolio', Set::TYPE_PUBLIC);

        static::assertAnswerIs(['id' => 12, 'name' => 'Portfolio', 'public' => true, 'starred' => false], $transformed);
    }

    /**
     * @return array<int,array> whether a set of each type belongs to the site, and whether it holds
     *                          the files its user starred
     */
    public static function provideTheTypesASetIsOf(): array
    {
        return [
            [Set::TYPE_PUBLIC, true, false],
            [Set::TYPE_PRIVATE, false, false],
            // the files a user starred are kept in a set of their own, which is theirs like any other
            [Set::TYPE_STARRED, false, true],
            // a saved search is no set of files, and the file manager keeps it in the same table
            [Set::TYPE_SAVED_SEARCH, false, false],
        ];
    }

    /**
     * @dataProvider provideTheTypesASetIsOf
     */
    public function testASetSaysWhoItBelongsToAndWhetherItHoldsTheStarredFiles(int $type, bool $public, bool $starred): void
    {
        $transformed = $this->transform(12, 'Portfolio', $type);

        static::assertSame($public, $transformed['public']);
        static::assertSame($starred, $transformed['starred']);
    }

    public function testTheFieldsAreTheOnesTheSpecificationDescribes(): void
    {
        $this->assertFieldsAre('FileSet', $this->transform(12, 'Portfolio', Set::TYPE_PUBLIC));
    }

    /**
     * @return array<string,mixed> what a client reads of the set
     */
    private function transform(int $id, string $name, int $type): array
    {
        $set = $this->createMock(Set::class);
        $set->method('getFileSetID')->willReturn($id);
        $set->method('getFileSetName')->willReturn($name);
        $set->method('getFileSetType')->willReturn($type);

        return (new FileSetTransformer())->transform($set);
    }
}
