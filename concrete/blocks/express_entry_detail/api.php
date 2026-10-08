<?php

declare(strict_types=1);

namespace Concrete\Block\ExpressEntryDetail;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Api\Express\EntityAccess;
use Concrete\Core\Block\Block;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Entity\Express\Entry;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Express\ObjectManager;

defined('C5_EXECUTE') or die('Access Denied.');

class Api extends DefaultBlockApiHandler
{
    /**
     * @var string
     */
    private const ENTRY_FORMAT = 'Entries are named by UUID, or by ID when the UUID is missing.';

    /**
     * Where a client reads what an entity offers, described to the clients of the API.
     *
     * @var string
     */
    private const ENTITY_FORMAT = 'GET /express_entities names the entities this API serves, each with its forms.';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $schema = parent::getApiValueSchema();
        $described = $schema['properties']['exSpecificEntryID'];
        $described['type'] = ['string', 'integer', 'null'];
        $described['description'] = implode("\n", array_filter([(string) ($described['description'] ?? ''), self::ENTRY_FORMAT]));
        $schema['properties']['exSpecificEntryID'] = $described;
        foreach (['exEntityID', 'exFormID'] as $name) {
            $schema['properties'][$name]['description'] = implode("\n", array_filter([
                (string) ($schema['properties'][$name]['description'] ?? ''),
                self::ENTITY_FORMAT,
            ]));
        }

        return $schema;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValue()
     */
    public function getApiValue(Block $block): array
    {
        $value = parent::getApiValue($block);
        $value['exSpecificEntryID'] = $this->getEntryIdentifierForApi((int) ($value['exSpecificEntryID'] ?? 0));

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getSaveArgumentsFromApiValue()
     *
     * @throws \Concrete\Core\Error\UserMessageException when the client names an entity or an entry it can't reach
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        $arguments = parent::getSaveArgumentsFromApiValue($value, $block);
        $stored = $block === null ? [] : $this->getMainTableRow($block);
        $entityID = (string) ($arguments['exEntityID'] ?? '');
        // an entity already there stays, even one the API doesn't serve
        if ($entityID !== (string) ($stored['exEntityID'] ?? '')) {
            $this->getAccessibleEntity($entityID);
        }
        $storedEntryID = (int) ($stored['exSpecificEntryID'] ?? 0);
        $identifier = array_key_exists('exSpecificEntryID', $value) ? $value['exSpecificEntryID'] : $this->getEntryIdentifierForApi($storedEntryID);
        $arguments['exSpecificEntryID'] = $this->getEntryIDFromApi($identifier, $storedEntryID, $entityID);

        return $arguments;
    }

    /**
     * @return int|string 0 where the block points at no entry, the ID where the entry has no public identifier
     */
    private function getEntryIdentifierForApi(int $entryID)
    {
        if ($entryID <= 0) {
            return 0;
        }
        $entry = app(ObjectManager::class)->getEntry($entryID);
        $identifier = $entry instanceof Entry ? (string) $entry->getPublicIdentifier() : '';

        return $identifier === '' ? $entryID : $identifier;
    }

    /**
     * @param int $storedEntryID the entry the block points at, kept as it is where the identifier names it
     * @param string $entityID the entity whose entries the block displays
     *
     * @throws \Concrete\Core\Error\UserMessageException
     */
    private function getEntryIDFromApi($identifier, int $storedEntryID, string $entityID): int
    {
        if ($identifier === null || $identifier === '' || (is_numeric($identifier) && (int) $identifier === 0)) {
            return 0;
        }
        if ((string) $identifier === (string) $this->getEntryIdentifierForApi($storedEntryID)) {
            return $storedEntryID;
        }
        $entity = $this->getAccessibleEntity($entityID);
        if ($entity === null) {
            throw new UserMessageException(t('The block displays the entries of no Express entity.'));
        }
        $entry = app(ObjectManager::class)->getEntry($identifier);
        $publicIdentifier = $entry instanceof Entry ? (string) $entry->getPublicIdentifier() : '';
        // the IDs stay inside the site: an entry that has a public identifier answers to that one only
        if (!$entry instanceof Entry || ($publicIdentifier !== '' && $publicIdentifier !== (string) $identifier)) {
            throw new UserMessageException(t('No entry named %s was found.', $identifier));
        }
        $entryEntity = $entry->getEntity();
        if (!$entryEntity instanceof Entity || (string) $entryEntity->getId() !== (string) $entity->getId()) {
            throw new UserMessageException(t('The entry named %s belongs to another entity.', $identifier));
        }
        if (!app(EntityAccess::class)->canViewEntry($entry)) {
            throw new UserMessageException(t('You do not have access to the entry named %s.', $identifier));
        }

        return (int) $entry->getID();
    }

    /**
     * @throws \Concrete\Core\Error\UserMessageException where the API doesn't serve the entity, or its entries can't be read
     * @return \Concrete\Core\Entity\Express\Entity|null NULL where the ID names no entity at all
     */
    private function getAccessibleEntity(string $entityID): ?Entity
    {
        if ($entityID === '') {
            return null;
        }
        $access = app(EntityAccess::class);
        $entity = $access->getEntity($entityID);
        if ($entity === null || !$access->isServed($entity)) {
            throw new UserMessageException(t('This API serves no Express entity named %s.', $entityID));
        }
        if (!$access->canViewEntries($entity)) {
            throw new UserMessageException(t('You do not have access to the entries of %s.', $entity->getName()));
        }

        return $entity;
    }
}
