<?php

declare(strict_types=1);

namespace Concrete\Tests\Block;

use Concrete\Core\Block\ReferenceColumns;
use Concrete\Core\Reference\ReferenceKind;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

class ReferenceColumnsTest extends TestCase
{
    public function testColumns(): void
    {
        $columns = $this->createColumns();

        static::assertSame(['fID', 'fOnstateID'], $columns->getColumns(ReferenceKind::FILE));
        static::assertSame([], $columns->getColumns(ReferenceKind::PAGE_FEED));
        // a kind of reference that no column holds isn't listed
        static::assertSame([ReferenceKind::FILE, ReferenceKind::PAGE], $columns->getReferences());
    }

    public function testColumnsAreLookedUpCaseInsensitively(): void
    {
        $columns = $this->createColumns();

        static::assertSame(ReferenceKind::FILE, $columns->getReference('fID'));
        static::assertSame(ReferenceKind::FILE, $columns->getReference('fid'));
        static::assertSame(ReferenceKind::PAGE, $columns->getReference('internalLinkCID'));
    }

    public function testAColumnHoldingNoReference(): void
    {
        static::assertSame('', $this->createColumns()->getReference('maxWidth'));
    }

    public function testAColumnDeclaredTwiceKeepsTheKindDeclaredFirst(): void
    {
        $columns = new ReferenceColumns([
            ReferenceKind::PAGE => ['cID'],
            ReferenceKind::FILE => ['cID'],
        ]);

        static::assertSame(ReferenceKind::PAGE, $columns->getReference('cID'));
    }

    public function testMoreColumnsAreDeclaredOnTopOfTheOnesAlreadyThere(): void
    {
        $columns = $this->createColumns()->with([
            ReferenceKind::FILE => ['onstateID'],
            ReferenceKind::FILE_SET => ['addFilesToSetID'],
        ]);

        static::assertSame(['fID', 'fOnstateID', 'onstateID'], $columns->getColumns(ReferenceKind::FILE));
        static::assertSame(['addFilesToSetID'], $columns->getColumns(ReferenceKind::FILE_SET));
        static::assertSame(ReferenceKind::PAGE, $columns->getReference('internalLinkCID'));
    }

    public function testDeclaringMoreColumnsLeavesTheOnesTheyCameFromAlone(): void
    {
        $columns = $this->createColumns();

        $columns->with([ReferenceKind::FILE_SET => ['addFilesToSetID']]);

        static::assertSame('', $columns->getReference('addFilesToSetID'));
    }

    public function testAColumnDeclaredAgainKeepsTheKindDeclaredBefore(): void
    {
        $columns = $this->createColumns()->with([ReferenceKind::FILE_SET => ['fID']]);

        static::assertSame(ReferenceKind::FILE, $columns->getReference('fID'));
    }

    public function testABlockTypeHoldingNoReference(): void
    {
        $columns = new ReferenceColumns([]);

        static::assertSame([], $columns->getReferences());
        static::assertSame([], $columns->getColumns(ReferenceKind::PAGE));
        static::assertSame('', $columns->getReference('cID'));
    }

    private function createColumns(): ReferenceColumns
    {
        return new ReferenceColumns([
            ReferenceKind::FILE => ['fID', 'fOnstateID'],
            ReferenceKind::PAGE => ['internalLinkCID'],
            ReferenceKind::CONTENT => [],
        ]);
    }
}
