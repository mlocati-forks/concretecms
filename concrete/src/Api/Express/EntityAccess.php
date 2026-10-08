<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Express;

use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Entity\Express\Entry;
use Concrete\Core\Permission\Checker;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * An entity is served where it says so and it is published, which is what the routes of its entries
 * are built with.
 */
class EntityAccess
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Concrete\Core\Entity\Express\Entity[] the entities a client may work with
     */
    public function getEntities(): array
    {
        $served = $this->entityManager->getRepository(Entity::class)->findBy(
            ['include_in_rest_api' => true, 'is_published' => true],
            ['name' => 'ASC']
        );

        return array_values(array_filter($served, function (Entity $entity): bool {
            return $this->canViewEntries($entity);
        }));
    }

    public function getEntity(string $entityID): ?Entity
    {
        if ($entityID === '') {
            return null;
        }
        $entity = $this->entityManager->getRepository(Entity::class)->findOneBy(['id' => $entityID]);

        return $entity instanceof Entity ? $entity : null;
    }

    public function isServed(Entity $entity): bool
    {
        return $entity->getIncludeInRestApi() && $entity->isPublished();
    }

    public function canViewEntries(Entity $entity): bool
    {
        return (bool) (new Checker($entity))->canViewExpressEntries();
    }

    public function canViewEntry(Entry $entry): bool
    {
        return (bool) (new Checker($entry))->canViewExpressEntry();
    }
}
