<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Block\Block;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Entity\Express\Entry;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Express\ObjectManager;
use Concrete\Core\Tree\Node\NodeType;
use Concrete\Core\Tree\TreeType;
use Concrete\Core\Tree\Type\ExpressEntryResults;
use Concrete\Core\User\User;
use Concrete\TestHelpers\Block\BlockApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Block\ExpressEntryDetail\Api
 */
class ExpressEntryValueTest extends BlockApiTestCase
{
    /**
     * The entity whose entries the API serves.
     *
     * @var string
     */
    private static $servedEntityID = '';

    /**
     * The entity the API doesn't serve.
     *
     * @var string
     */
    private static $hiddenEntityID = '';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        NodeType::add('category');
        NodeType::add('express_entry_category');
        NodeType::add('express_entry_results');
        TreeType::add('express_entry_results');
        ExpressEntryResults::add();
        Category::add('express');
        self::$servedEntityID = self::addEntity('student', 'students', 'Student', true);
        self::$hiddenEntityID = self::addEntity('teacher', 'teachers', 'Teacher', false);
    }

    public function setUp(): void
    {
        parent::setUp();
        // with no permission key registered, only the superuser is allowed anything
        $superUser = $this->createMock(User::class);
        $superUser->method('isSuperUser')->willReturn(true);
        app()->instance(User::class, $superUser);
    }

    public function tearDown(): void
    {
        app()->forgetInstance(User::class);
        parent::tearDown();
    }

    public function testTheEntryTravelsByItsPublicIdentifier(): void
    {
        $entry = $this->addEntry(self::$servedEntityID);

        $value = $this->getApiValue($this->addDetailBlock(self::$servedEntityID, (int) $entry->getID()));

        static::assertSame($entry->getPublicIdentifier(), $value['exSpecificEntryID']);
    }

    /**
     * The entries added before the public identifiers existed have none.
     */
    public function testAnEntryWithoutAPublicIdentifierTravelsByItsId(): void
    {
        $entry = $this->addEntry(self::$servedEntityID);
        $entryID = (int) $entry->getID();
        app(Connection::class)->update('ExpressEntityEntries', ['publicIdentifier' => null], ['exEntryID' => $entryID]);
        app(EntityManagerInterface::class)->clear();

        $value = $this->getApiValue($this->addDetailBlock(self::$servedEntityID, $entryID));

        static::assertSame($entryID, $value['exSpecificEntryID']);
    }

    public function testNoEntryStaysNoEntry(): void
    {
        $value = $this->getApiValue($this->addDetailBlock(self::$servedEntityID, 0));

        static::assertSame(0, $value['exSpecificEntryID']);
    }

    public function testTheEntryComesBackFromItsPublicIdentifier(): void
    {
        $entry = $this->addEntry(self::$servedEntityID);
        $block = $this->addDetailBlock(self::$servedEntityID, 0);

        $this->updateBlock($block, ['exSpecificEntryID' => $entry->getPublicIdentifier()]);

        static::assertSame((int) $entry->getID(), $this->getStoredEntryID($block));
    }

    /**
     * The IDs stay inside the site.
     */
    public function testAnEntryWithAPublicIdentifierDoesntComeBackFromItsId(): void
    {
        $entry = $this->addEntry(self::$servedEntityID);
        $block = $this->addDetailBlock(self::$servedEntityID, 0);

        $this->expectException(UserMessageException::class);
        $this->expectExceptionMessage('No entry named ' . $entry->getID() . ' was found.');

        $this->updateBlock($block, ['exSpecificEntryID' => (int) $entry->getID()]);
    }

    public function testAnEntryOfAnotherEntityIsRefused(): void
    {
        $entry = $this->addEntry(self::$hiddenEntityID);
        $block = $this->addDetailBlock(self::$servedEntityID, 0);

        $this->expectException(UserMessageException::class);
        $this->expectExceptionMessage('belongs to another entity');

        $this->updateBlock($block, ['exSpecificEntryID' => $entry->getPublicIdentifier()]);
    }

    public function testAnEntityTheApiDoesntServeIsRefused(): void
    {
        $block = $this->addDetailBlock(self::$servedEntityID, 0);

        $this->expectException(UserMessageException::class);
        $this->expectExceptionMessage('serves no Express entity');

        $this->updateBlock($block, ['exEntityID' => self::$hiddenEntityID]);
    }

    public function testTheEntityCanBeTakenAway(): void
    {
        $block = $this->addDetailBlock(self::$servedEntityID, 0);

        $this->updateBlock($block, ['exEntityID' => '']);

        static::assertSame('', $this->getApiValue($this->getBlock($block->getBlockCollectionObject()))['exEntityID']);
    }

    public function testNoEntryIsTakenWhereTheBlockDisplaysNoEntity(): void
    {
        $entry = $this->addEntry(self::$servedEntityID);
        $block = $this->addDetailBlock('', 0);

        $this->expectException(UserMessageException::class);
        $this->expectExceptionMessage('no Express entity');

        $this->updateBlock($block, ['exSpecificEntryID' => $entry->getPublicIdentifier()]);
    }

    public function testANewBlockTakesThePublicIdentifierToo(): void
    {
        $entry = $this->addEntry(self::$servedEntityID);

        $block = $this->addBlockFromApiValue('express_entry_detail', [
            'exEntityID' => self::$servedEntityID,
            'entryMode' => 'S',
            'exSpecificEntryID' => $entry->getPublicIdentifier(),
            'exFormID' => '',
        ]);

        static::assertSame((int) $entry->getID(), $this->getStoredEntryID($block));
    }

    public function testTheEntityOfABlockTheApiDoesntServeStaysWhereItIs(): void
    {
        $block = $this->addDetailBlock(self::$hiddenEntityID, 0);

        $this->updateBlock($block, ['exFormID' => '']);

        static::assertSame(self::$hiddenEntityID, $this->getApiValue($this->getBlock($block->getBlockCollectionObject()))['exEntityID']);
    }

    public function testTheSchemaSaysThatAnEntryMayBeAString(): void
    {
        $described = $this->describeEntryField();

        static::assertSame(['string', 'integer', 'null'], $described['type']);
    }

    public function testTheSchemaTellsHowAnEntryIsNamed(): void
    {
        $described = $this->describeEntryField();

        static::assertStringContainsString('UUID', $described['description']);
        // the comment of the column is still there
        static::assertStringContainsString('entryMode', $described['description']);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getTables()
     */
    protected function getTables()
    {
        return array_merge(parent::getTables(), [
            'PermissionAccessEntities',
            'PermissionAccessEntityGroups',
            'TreeGroupNodes',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getEntityClassNames()
     */
    protected function getEntityClassNames(): array
    {
        return array_merge(parent::getEntityClassNames(), [
            'Concrete\Core\Entity\Attribute\Category',
            'Concrete\Core\Entity\Express\Association',
            'Concrete\Core\Entity\Express\Entity',
            'Concrete\Core\Entity\Express\Entry',
            'Concrete\Core\Entity\Express\Form',
            'Concrete\Core\Entity\Attribute\Key\ExpressKey',
        ]);
    }

    /**
     * @param bool $servedByTheApi whether the API may work with its entries
     *
     * @return string the ID of the entity
     */
    private static function addEntity(string $handle, string $pluralHandle, string $name, bool $servedByTheApi): string
    {
        $entity = app(ObjectManager::class)->buildObject($handle, $pluralHandle, $name)->save();
        $entity->setIncludeInRestApi($servedByTheApi);
        $entityManager = app(EntityManagerInterface::class);
        $entityManager->persist($entity);
        $entityManager->flush();

        return (string) $entity->getId();
    }

    private function addEntry(string $entityID): Entry
    {
        $entity = app(EntityManagerInterface::class)->find(Entity::class, $entityID);

        return app(ObjectManager::class)->buildEntry($entity)->save();
    }

    private function addDetailBlock(string $entityID, int $entryID): Block
    {
        return $this->addBlock('express_entry_detail', [
            'exEntityID' => $entityID,
            'entryMode' => 'S',
            'exSpecificEntryID' => $entryID,
            'exFormID' => '',
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    private function describeEntryField(): array
    {
        $block = $this->addDetailBlock(self::$servedEntityID, 0);

        return $this->getHandler($block)->getApiValueSchema()['properties']['exSpecificEntryID'];
    }

    private function getStoredEntryID(Block $block): int
    {
        return (int) app(Connection::class)->fetchOne(
            'SELECT exSpecificEntryID FROM btExpressEntryDetail WHERE bID = ?',
            [$block->getBlockID()]
        );
    }
}
