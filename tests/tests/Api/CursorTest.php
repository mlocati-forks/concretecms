<?php

declare(strict_types=1);

namespace Concrete\Tests\Api;

use Concrete\Core\Api\Cursor;
use Concrete\Core\Api\Cursor\IntegerCursor;
use Concrete\Core\Api\Cursor\StringCursor;
use Concrete\Core\File\Search\ColumnSet\Column\FileIDColumn;
use Concrete\Core\Http\Request;
use Concrete\Core\Search\ItemList\Pager\PagerProviderInterface;
use Concrete\Tests\TestCase;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Cursor
 */
class CursorTest extends TestCase
{
    public function testAListIsWalkedFromTheBeginningWhereTheRequestAsksForNothing(): void
    {
        static::assertNull((new IntegerCursor($this->getIntegerKey()))->getCurrent($this->createRequest(null)));
        static::assertNull((new StringCursor($this->getStringKey()))->getCurrent($this->createRequest(null)));
    }

    public function testTheKeyTheRequestSendsComesBackAsTheTypeTheListIsWalkedBy(): void
    {
        static::assertSame(12, (new IntegerCursor($this->getIntegerKey()))->getCurrent($this->createRequest('12')));
        static::assertSame('12', (new StringCursor($this->getStringKey()))->getCurrent($this->createRequest('12')));
    }

    /**
     * @return array<int,array<int,string>>
     */
    public static function provideKeysThatNameNoObject(): array
    {
        return [
            ['0'],
            ['-1'],
            ['abc'],
            [''],
        ];
    }

    /**
     * @dataProvider provideKeysThatNameNoObject
     */
    public function testAnIdThatNamesNoObjectAsksForTheBeginningOfTheList(string $after): void
    {
        static::assertNull((new IntegerCursor($this->getIntegerKey()))->getCurrent($this->createRequest($after)));
    }

    public function testTheListLeavesOutWhatComesBeforeTheObjectTheRequestNames(): void
    {
        $column = $this->createMock(FileIDColumn::class);
        $list = $this->createMock(PagerProviderInterface::class);
        $object = $this->createObject(84);
        $column->expects(static::once())->method('filterListAtOffset')->with($list, $object);

        (new IntegerCursor($this->getIntegerKey()))->startAfter($this->createRequest('84'), $list, $column, static function (int $key) use ($object) {
            return $key === 84 ? $object : null;
        });
    }

    public function testTheListStartsWhereItIsWhereTheRequestNamesNoObject(): void
    {
        $column = $this->createMock(FileIDColumn::class);
        $list = $this->createMock(PagerProviderInterface::class);
        $column->expects(static::never())->method('filterListAtOffset');

        (new IntegerCursor($this->getIntegerKey()))->startAfter($this->createRequest(null), $list, $column, static function (int $key): void {
            static::fail('The list was asked for the object of a cursor it never got.');
        });
    }

    public function testTheListStartsWhereItIsWhereTheKeyNamesNothingItHolds(): void
    {
        $column = $this->createMock(FileIDColumn::class);
        $list = $this->createMock(PagerProviderInterface::class);
        $column->expects(static::never())->method('filterListAtOffset');

        (new IntegerCursor($this->getIntegerKey()))->startAfter($this->createRequest('84'), $list, $column, static function (int $key) {
        });
    }

    public function testTheAnswerSaysWhereTheListWasWalkedAndWhereToWalkItOn(): void
    {
        $cursor = $this->describe(new IntegerCursor($this->getIntegerKey()), '84', [$this->createObject(85), $this->createObject(86)]);

        static::assertSame(84, $cursor->getCurrent());
        // this API does not walk a list backwards
        static::assertNull($cursor->getPrev());
        static::assertSame(86, $cursor->getNext());
        static::assertSame(2, $cursor->getCount());
    }

    public function testAnEmptyAnswerHasNothingToWalkOnTo(): void
    {
        $cursor = $this->describe(new IntegerCursor($this->getIntegerKey()), '84', []);

        static::assertSame(84, $cursor->getCurrent());
        static::assertNull($cursor->getNext());
        static::assertSame(0, $cursor->getCount());
    }

    public function testTheAnswerCarriesTheKeyAsTheClosureHandsItOver(): void
    {
        $asInteger = $this->describe(new IntegerCursor($this->getIntegerKey()), null, [$this->createObject(85)]);
        $asUuid = $this->describe(new StringCursor(static function ($object): string {
            return 'file-' . $object->getID();
        }), null, [$this->createObject(85)]);

        static::assertSame(85, $asInteger->getNext());
        static::assertSame('file-85', $asUuid->getNext());
    }

    /**
     * @param object[] $results the objects of the page the answer carries
     */
    private function describe(Cursor $cursor, ?string $after, array $results): \League\Fractal\Pagination\Cursor
    {
        $resource = new Collection($results, static function ($object): array {
            return [];
        });
        $cursor->describe($this->createRequest($after), $results, $resource);

        return $this->getCursorOf($resource);
    }

    private function getCursorOf(ResourceAbstract $resource): \League\Fractal\Pagination\Cursor
    {
        $cursor = $resource->getCursor();
        static::assertInstanceOf(\League\Fractal\Pagination\Cursor::class, $cursor);

        return $cursor;
    }

    /**
     * @return \Closure the way an object of a list answers with its key, as an ID
     */
    private function getIntegerKey(): \Closure
    {
        return static function ($object) {
            return (int) $object->getID();
        };
    }

    /**
     * @return \Closure the way an object of a list answers with its key, as a UUID would come
     */
    private function getStringKey(): \Closure
    {
        return static function ($object) {
            return (string) $object->getID();
        };
    }

    private function createRequest(?string $after): Request
    {
        return Request::create('/ccm/api/1.0/whatever' . ($after === null ? '' : '?after=' . urlencode($after)));
    }

    /**
     * @return object an object of a list, answering with its key through getID()
     */
    private function createObject(int $id)
    {
        return new class ($id) {
            /**
             * @var int
             */
            private $id;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getID(): int
            {
                return $this->id;
            }
        };
    }
}
