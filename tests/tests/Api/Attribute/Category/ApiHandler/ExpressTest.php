<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute\Category\ApiHandler;

use Concrete\Core\Api\Attribute\Category;
use Concrete\Core\Api\Attribute\Category\ApiHandler;
use Concrete\Core\Api\Express\EntityAccess;
use Concrete\Core\Attribute\Category\ExpressCategory;
use Concrete\Core\Entity\Attribute\Key\ExpressKey;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The keys of the Express category belong to an entity each, so this handler serves one set of keys
 * per entity and looks for the keys of a set by its entity.
 *
 * @see \Concrete\Core\Api\Attribute\Category\ApiHandler\Express
 */
class ExpressTest extends TestCase
{
    public function testThereIsASetOfKeysPerEntityAClientMayWorkWith(): void
    {
        $handler = $this->createHandler([$this->createEntity('7b2'), $this->createEntity('9f4')]);

        $sets = $handler->getApiCategories();

        static::assertSame(['express@7b2', 'express@9f4'], array_map(static function (Category $set): string {
            return $set->getHandle();
        }, $sets));
    }

    /**
     * The API asks the handler back for what it can't know by itself, so each set keeps it.
     */
    public function testEachSetOfKeysRemembersTheHandler(): void
    {
        $handler = $this->createHandler([$this->createEntity('7b2')]);

        static::assertSame($handler, $handler->getApiCategories()[0]->getApiHandler());
    }

    public function testNoEntityToWorkWithMeansNoSetOfKeys(): void
    {
        static::assertSame([], $this->createHandler([])->getApiCategories());
    }

    public function testASetOfKeysNamesTheEntityOfItsKeys(): void
    {
        $handler = $this->createHandler([$this->createEntity('7b2')]);

        $model = $handler->createApiCategoryModel($handler->getApiCategories()[0]);

        static::assertSame('7b2', $model->express_entity_id);
    }

    /**
     * The keys of a set are the ones of its entity, the ones the core keeps for itself left out.
     */
    public function testTheKeysOfASetAreTheOnesOfItsEntity(): void
    {
        $entity = $this->createEntity('7b2');
        $keys = [$this->createMock(ExpressKey::class)];
        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects(static::once())
            ->method('findBy')
            ->with(['entity' => $entity, 'akIsInternal' => false], ['akHandle' => 'ASC'])
            ->willReturn($keys)
        ;
        $handler = $this->createHandler([$entity], $repository);

        static::assertSame($keys, $handler->getApiKeys($handler->getApiCategories()[0]));
    }

    /**
     * @param \Concrete\Core\Entity\Express\Entity[] $entities the entities a client may work with
     * @param \Doctrine\ORM\EntityRepository|null $repository where the keys of an entity are looked for
     */
    private function createHandler(array $entities, $repository = null): ApiHandler\Express
    {
        $entityAccess = $this->createMock(EntityAccess::class);
        $entityAccess->method('getEntities')->willReturn($entities);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository ?? $this->createMock(EntityRepository::class));

        return new ApiHandler\Express($this->createMock(ExpressCategory::class), $entityAccess, $entityManager);
    }

    /**
     * @return \Concrete\Core\Entity\Express\Entity&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createEntity(string $id): Entity
    {
        $entity = $this->createMock(Entity::class);
        $entity->method('getId')->willReturn($id);
        $entity->method('getName')->willReturn('Entity ' . $id);

        return $entity;
    }
}
