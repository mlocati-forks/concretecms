<?php

namespace Concrete\Core\Express\Command;

use Concrete\Core\Api\Attribute\AttributeValueMap;
use Concrete\Core\Api\Express\Association\AssociationMap;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Foundation\Command\Command;

class AddExpressEntryCommand extends Command
{

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var AttributeValueMap
     */
    protected $attributeMap;

    /**
     * @var AssociationMap
     */
    protected $associationMap;

    /**
     * @var \Concrete\Core\Entity\Site\Site|null
     */
    protected $site;

    /**
     * AddExpressEntryCommand constructor.
     * @param Entity $entity
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * @param Entity $entity
     */
    public function setEntity(Entity $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * @return AttributeValueMap
     */
    public function getAttributeMap(): ?AttributeValueMap
    {
        return $this->attributeMap;
    }

    /**
     * @param AttributeValueMap $attributeMap
     */
    public function setAttributeMap(AttributeValueMap $attributeMap): void
    {
        $this->attributeMap = $attributeMap;
    }

    /**
     * @return AssociationMap
     */
    public function getAssociationMap(): ?AssociationMap
    {
        return $this->associationMap;
    }

    /**
     * @param AssociationMap $associationMap
     */
    public function setAssociationMap(AssociationMap $associationMap): void
    {
        $this->associationMap = $associationMap;
    }

    /**
     * Get the site whose entries the new one joins, for an entity that keeps them apart by site.
     */
    public function getSite(): ?Site
    {
        return $this->site;
    }

    /**
     * @param \Concrete\Core\Entity\Site\Site|null $site NULL to leave the entry where the entries of
     *     every site are
     */
    public function setSite(?Site $site): void
    {
        $this->site = $site;
    }


}
