<?php

declare(strict_types=1);

namespace Concrete\Tests\Block;

use Concrete\Core\Block\ReferenceColumns;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

class ReferenceColumnsTest extends TestCase
{
    public function testColumns(): void
    {
        $columns = $this->createColumns();

        static::assertSame(['fID', 'fOnstateID'], $columns->getColumns(ReferenceColumns::FILE));
        static::assertSame([], $columns->getColumns(ReferenceColumns::PAGE_FEED));
        // a kind of reference that no column holds isn't listed
        static::assertSame([ReferenceColumns::FILE, ReferenceColumns::PAGE], $columns->getReferences());
    }

    public function testColumnsAreLookedUpCaseInsensitively(): void
    {
        $columns = $this->createColumns();

        static::assertSame(ReferenceColumns::FILE, $columns->getReference('fID'));
        static::assertSame(ReferenceColumns::FILE, $columns->getReference('fid'));
        static::assertSame(ReferenceColumns::PAGE, $columns->getReference('internalLinkCID'));
    }

    public function testAColumnHoldingNoReference(): void
    {
        static::assertSame('', $this->createColumns()->getReference('maxWidth'));
    }

    public function testAColumnDeclaredTwiceKeepsTheKindDeclaredFirst(): void
    {
        $columns = new ReferenceColumns([
            ReferenceColumns::PAGE => ['cID'],
            ReferenceColumns::FILE => ['cID'],
        ]);

        static::assertSame(ReferenceColumns::PAGE, $columns->getReference('cID'));
    }

    public function testABlockTypeHoldingNoReference(): void
    {
        $columns = new ReferenceColumns([]);

        static::assertSame([], $columns->getReferences());
        static::assertSame([], $columns->getColumns(ReferenceColumns::PAGE));
        static::assertSame('', $columns->getReference('cID'));
    }

    private function createColumns(): ReferenceColumns
    {
        return new ReferenceColumns([
            ReferenceColumns::FILE => ['fID', 'fOnstateID'],
            ReferenceColumns::PAGE => ['internalLinkCID'],
            ReferenceColumns::CONTENT => [],
        ]);
    }
}
